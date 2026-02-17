<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\GameRepository;
use App\Repository\OrderRepository;
use App\Service\EmailHtmlRenderer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class CheckoutController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderRepository $orderRepository,
        private readonly GameRepository $gameRepository,
        private readonly Security $security,
        private readonly MailerInterface $mailer,
        private readonly EmailHtmlRenderer $emailHtml,
        private readonly LoggerInterface $logger,
        private readonly string $stripeSecretKey,
        private readonly string $stripeWebhookSecret,
        private readonly string $frontendUrl,
        private readonly string $mailerFrom
    ) {}

    #[Route('/api/checkout/session', name: 'api_checkout_session', methods: ['POST'])]
    public function createSession(Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->json(['error' => 'Non authentifié.'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data) || !isset($data['items']) || !is_array($data['items']) || $data['items'] === []) {
            return $this->json(['error' => 'items requis et doit être un tableau non vide.'], Response::HTTP_BAD_REQUEST);
        }

        $order = new Order($user);
        $order->setStatus('pending');
        $order->setCurrency('eur');

        $lineItems = [];
        foreach ($data['items'] as $item) {
            if (!is_array($item)) {
                return $this->json(['error' => 'item invalide.'], Response::HTTP_BAD_REQUEST);
            }
            $gameId = $item['game_id'] ?? null;
            $quantity = $item['quantity'] ?? null;
            if (!is_int($gameId) && !ctype_digit((string) $gameId)) {
                return $this->json(['error' => 'game_id invalide.'], Response::HTTP_BAD_REQUEST);
            }
            if (!is_int($quantity) && !ctype_digit((string) $quantity)) {
                return $this->json(['error' => 'quantity invalide.'], Response::HTTP_BAD_REQUEST);
            }
            $quantity = (int) $quantity;
            if ($quantity <= 0) {
                return $this->json(['error' => 'quantity doit être > 0.'], Response::HTTP_BAD_REQUEST);
            }

            $game = $this->gameRepository->find((int) $gameId);
            if ($game === null) {
                return $this->json(['error' => 'Jeu introuvable.'], Response::HTTP_BAD_REQUEST);
            }

            $unitPriceCents = $game->getPriceCents() ?? 0;
            if ($unitPriceCents <= 0) {
                return $this->json(['error' => 'Prix du jeu invalide.'], Response::HTTP_BAD_REQUEST);
            }

            $orderItem = new OrderItem($order, $game, $quantity, $unitPriceCents);
            $order->addItem($orderItem);
            $order->setTotalCents($order->getTotalCents() + $quantity * $unitPriceCents);

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $game->getName(),
                    ],
                    'unit_amount' => $unitPriceCents,
                ],
                'quantity' => $quantity,
            ];
        }

        if ($order->getTotalCents() <= 0) {
            return $this->json(['error' => 'Total invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        try {
            \Stripe\Stripe::setApiKey($this->stripeSecretKey);

            $successUrl = rtrim($this->frontendUrl, '/') . '/cart/success?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = rtrim($this->frontendUrl, '/') . '/cart?payment=cancelled';

            $session = \Stripe\Checkout\Session::create([
                'mode' => 'payment',
                'line_items' => $lineItems,
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'order_id' => (string) $order->getId(),
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'error' => 'Une erreur est survenue lors de la création de la session de paiement.',
            ], Response::HTTP_BAD_GATEWAY);
        }

        $order->setStripeSessionId($session->id);
        $this->entityManager->flush();

        return $this->json(['url' => $session->url]);
    }

    #[Route('/api/stripe/webhook', name: 'api_stripe_webhook', methods: ['POST'])]
    public function webhook(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature', '');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $this->stripeWebhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            return new Response('Invalid payload', Response::HTTP_BAD_REQUEST);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return new Response('Invalid signature', Response::HTTP_BAD_REQUEST);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $orderId = $session->metadata->order_id ?? null;
            if ($orderId !== null) {
                $order = $this->orderRepository->find((int) $orderId);
                if ($order instanceof Order && $order->getStatus() === 'pending') {
                    $order->setStatus('paid');
                    $order->setUpdatedAt(new \DateTimeImmutable());
                    $this->entityManager->flush();

                    $user = $order->getUser();
                    if ($user !== null && $user->getEmail() !== '') {
                        $lines = [];
                        $linesHtml = '';
                        foreach ($order->getItems() as $item) {
                            $game = $item->getGame();
                            $name = htmlspecialchars($game?->getName() ?? 'Jeu', \ENT_QUOTES, 'UTF-8');
                            $qty = $item->getQuantity();
                            $price = number_format($item->getUnitPriceCents() / 100, 2, ',', ' ') . ' €';
                            $lines[] = sprintf('  - %s x %d : %s', $game?->getName() ?? 'Jeu', $qty, $price);
                            $linesHtml .= sprintf(
                                '<tr><td style="padding:8px 12px; border-bottom:1px solid #eee;">%s</td><td style="padding:8px 12px; border-bottom:1px solid #eee; text-align:center;">%d</td><td style="padding:8px 12px; border-bottom:1px solid #eee; text-align:right;">%s</td></tr>',
                                $name,
                                $qty,
                                htmlspecialchars($price, \ENT_QUOTES, 'UTF-8')
                            );
                        }
                        $total = number_format($order->getTotalCents() / 100, 2, ',', ' ') . ' €';
                        $bodyHtml = '<p>Votre commande <strong>n°' . $order->getId() . '</strong> a bien été enregistrée et payée.</p>'
                            . '<table role="presentation" width="100%" cellspacing="0" style="margin:16px 0; border-collapse:collapse; font-size:14px;">'
                            . '<tr style="background:#f3f4f6;"><th style="padding:10px 12px; text-align:left;">Article</th><th style="padding:10px 12px; text-align:center;">Qté</th><th style="padding:10px 12px; text-align:right;">Prix</th></tr>'
                            . $linesHtml
                            . '<tr><td colspan="2" style="padding:12px; text-align:right; font-weight:bold;">Total</td><td style="padding:12px; text-align:right; font-weight:bold;">' . htmlspecialchars($total, \ENT_QUOTES, 'UTF-8') . '</td></tr>'
                            . '</table>'
                            . '<p>Merci pour votre achat, l\'équipe LudoPlanet.</p>';

                        $html = $this->emailHtml->render([
                            'title' => 'Confirmation de commande n°' . $order->getId(),
                            'body' => $bodyHtml,
                            'ctaUrl' => rtrim($this->frontendUrl, '/') . '/me',
                            'ctaLabel' => 'Voir mon compte et mes commandes',
                        ]);

                        $bodyText = "Votre commande n°" . $order->getId() . " a bien été enregistrée.\n\n"
                            . "Détail :\n" . implode("\n", $lines) . "\n\n"
                            . "Total : " . $total . "\n\n"
                            . "Merci pour votre achat, l'équipe LudoPlanet.";

                        try {
                            $this->mailer->send(
                                (new Email())
                                    ->from($this->mailerFrom)
                                    ->to($user->getEmail())
                                    ->subject('Confirmation de commande n°' . $order->getId() . ' — LudoPlanet')
                                    ->html($html)
                                    ->text($bodyText)
                            );
                        } catch (\Throwable $e) {
                            $this->logger->error('Envoi email confirmation commande échoué', [
                                'order_id' => $order->getId(),
                                'email' => $user->getEmail(),
                                'exception' => $e->getMessage(),
                            ]);
                        }
                    }
                }
            }
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/checkout/order-by-session', name: 'api_checkout_order_by_session', methods: ['GET'])]
    public function orderBySession(Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->json(['error' => 'Non authentifié.'], Response::HTTP_UNAUTHORIZED);
        }

        $sessionId = $request->query->get('session_id');
        if ($sessionId === null || $sessionId === '') {
            return $this->json(['error' => 'session_id requis.'], Response::HTTP_BAD_REQUEST);
        }

        $order = $this->orderRepository->findOneByStripeSessionId($sessionId);
        if ($order === null || $order->getUser()?->getId() !== $user->getId()) {
            return $this->json(['error' => 'Commande non trouvée.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['order' => $this->orderToArray($order)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function orderToArray(Order $order): array
    {
        return [
            'id' => $order->getId(),
            'user_id' => $order->getUser()?->getId(),
            'status' => $order->getStatus(),
            'total_cents' => $order->getTotalCents(),
            'currency' => $order->getCurrency(),
            'shipping' => [
                'full_name' => $order->getShippingFullName(),
                'address_line1' => $order->getShippingAddressLine1(),
                'address_line2' => $order->getShippingAddressLine2(),
                'postal_code' => $order->getShippingPostalCode(),
                'city' => $order->getShippingCity(),
                'country' => $order->getShippingCountry(),
                'phone' => $order->getPhone(),
            ],
            'items' => array_map(static function (OrderItem $item): array {
                $game = $item->getGame();
                $imageUrl = null;
                if ($game !== null) {
                    foreach ($game->getImages() as $image) {
                        $imageUrl = $image->getImageUrl();
                        if ($image->isPrimary()) {
                            break;
                        }
                    }
                }
                return [
                    'id' => $item->getId(),
                    'game_id' => $game?->getBggId(),
                    'game_name' => $game?->getName(),
                    'game_image_url' => $imageUrl,
                    'quantity' => $item->getQuantity(),
                    'unit_price_cents' => $item->getUnitPriceCents(),
                ];
            }, $order->getItems()->toArray()),
            'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $order->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
