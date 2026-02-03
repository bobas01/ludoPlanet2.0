<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\GameRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController
{
    private const ALLOWED_STATUSES = ['pending', 'paid', 'pickup', 'cancelled'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderRepository $orderRepository,
        private readonly UserRepository $userRepository,
        private readonly GameRepository $gameRepository
    ) {}

    #[Route('/api/orders', name: 'api_orders_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);
        $sort = strtolower((string) $request->query->get('sort', 'desc'));
        $status = $request->query->get('status');

        if ($page < 1) {
            return new JsonResponse(['error' => 'page invalide.'], Response::HTTP_BAD_REQUEST);
        }
        if ($limit < 1) {
            return new JsonResponse(['error' => 'limit invalide.'], Response::HTTP_BAD_REQUEST);
        }
        if (!in_array($sort, ['asc', 'desc'], true)) {
            return new JsonResponse(['error' => 'sort invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $criteria = [];
        if ($status !== null && $status !== '') {
            if (!in_array((string) $status, self::ALLOWED_STATUSES, true)) {
                return new JsonResponse(['error' => 'status invalide.'], Response::HTTP_BAD_REQUEST);
            }
            $criteria['status'] = (string) $status;
        }

        $orders = $this->orderRepository->findBy(
            $criteria,
            ['createdAt' => strtoupper($sort)],
            $limit,
            ($page - 1) * $limit
        );

        return new JsonResponse([
            'orders' => array_map(fn(Order $order) => $this->orderToArray($order), $orders),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'sort' => $sort,
                'status' => $status,
            ],
        ]);
    }

    #[Route('/api/orders/{id}', name: 'api_orders_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $order = $this->orderRepository->find($id);
        if ($order === null) {
            return new JsonResponse(['error' => 'Commande non trouvée.'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['order' => $this->orderToArray($order)]);
    }

    #[Route('/api/my-orders', name: 'api_my_orders_index', methods: ['GET'])]
    public function myOrders(Request $request, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user instanceof \App\Entity\User) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);
        $sort = strtolower((string) $request->query->get('sort', 'desc'));
        $status = $request->query->get('status');

        if ($page < 1) {
            return new JsonResponse(['error' => 'page invalide.'], Response::HTTP_BAD_REQUEST);
        }
        if ($limit < 1) {
            return new JsonResponse(['error' => 'limit invalide.'], Response::HTTP_BAD_REQUEST);
        }
        if (!in_array($sort, ['asc', 'desc'], true)) {
            return new JsonResponse(['error' => 'sort invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $criteria = ['user' => $user];
        if ($status !== null && $status !== '') {
            if (!in_array((string) $status, self::ALLOWED_STATUSES, true)) {
                return new JsonResponse(['error' => 'status invalide.'], Response::HTTP_BAD_REQUEST);
            }
            $criteria['status'] = (string) $status;
        }

        $orders = $this->orderRepository->findBy(
            $criteria,
            ['createdAt' => strtoupper($sort)],
            $limit,
            ($page - 1) * $limit
        );

        return new JsonResponse([
            'orders' => array_map(fn(Order $order) => $this->orderToArray($order), $orders),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'sort' => $sort,
                'status' => $status,
            ],
        ]);
    }

    #[Route('/api/my-orders/{id}', name: 'api_my_orders_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function myOrderShow(int $id, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user instanceof \App\Entity\User) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $order = $this->orderRepository->find($id);
        if ($order === null || $order->getUser()?->getId() !== $user->getId()) {
            return new JsonResponse(['error' => 'Commande non trouvée.'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['order' => $this->orderToArray($order)]);
    }

    #[Route('/api/orders', name: 'api_orders_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Données JSON invalides.'], Response::HTTP_BAD_REQUEST);
        }

        $userId = $data['user_id'] ?? null;
        if (!is_int($userId) && !ctype_digit((string) $userId)) {
            return new JsonResponse(['error' => 'user_id requis.'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->find((int) $userId);
        if ($user === null) {
            return new JsonResponse(['error' => 'Utilisateur introuvable.'], Response::HTTP_BAD_REQUEST);
        }

        $order = new Order($user);

        $error = $this->applyOrderData($order, $data, true);
        if ($error !== null) {
            return new JsonResponse($error, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return new JsonResponse(['order' => $this->orderToArray($order)], Response::HTTP_CREATED);
    }

    #[Route('/api/orders/{id}', name: 'api_orders_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(int $id, Request $request): Response
    {
        $order = $this->orderRepository->find($id);
        if ($order === null) {
            return new JsonResponse(['error' => 'Commande non trouvée.'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Données JSON invalides.'], Response::HTTP_BAD_REQUEST);
        }

        $error = $this->applyOrderData($order, $data, false);
        if ($error !== null) {
            return new JsonResponse($error, Response::HTTP_BAD_REQUEST);
        }

        $order->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return new JsonResponse(['order' => $this->orderToArray($order)]);
    }

    #[Route('/api/orders/{id}', name: 'api_orders_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $order = $this->orderRepository->find($id);
        if ($order === null) {
            return new JsonResponse(['error' => 'Commande non trouvée.'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($order);
        $this->entityManager->flush();

        return new JsonResponse(['ok' => true]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function applyOrderData(Order $order, array $data, bool $requireItems): ?array
    {
        if (array_key_exists('user_id', $data)) {
            $userId = $data['user_id'];
            if (!is_int($userId) && !ctype_digit((string) $userId)) {
                return ['error' => 'user_id invalide.'];
            }
            $user = $this->userRepository->find((int) $userId);
            if ($user === null) {
                return ['error' => 'Utilisateur introuvable.'];
            }
            $order->setUser($user);
        }

        if (isset($data['status'])) {
            $status = (string) $data['status'];
            if (!in_array($status, self::ALLOWED_STATUSES, true)) {
                return ['error' => 'Status invalide.'];
            }
            $order->setStatus($status);
        }

        if (isset($data['currency'])) {
            $currency = strtoupper((string) $data['currency']);
            if (strlen($currency) !== 3) {
                return ['error' => 'Currency invalide.'];
            }
            $order->setCurrency($currency);
        }

        if (isset($data['shipping']) && is_array($data['shipping'])) {
            $shipping = $data['shipping'];
            $order->setShippingFullName(isset($shipping['full_name']) ? (string) $shipping['full_name'] : null);
            $order->setShippingAddressLine1(isset($shipping['address_line1']) ? (string) $shipping['address_line1'] : null);
            $order->setShippingAddressLine2(isset($shipping['address_line2']) ? (string) $shipping['address_line2'] : null);
            $order->setShippingPostalCode(isset($shipping['postal_code']) ? (string) $shipping['postal_code'] : null);
            $order->setShippingCity(isset($shipping['city']) ? (string) $shipping['city'] : null);
            $order->setShippingCountry(isset($shipping['country']) ? (string) $shipping['country'] : null);
            $order->setPhone(isset($shipping['phone']) ? (string) $shipping['phone'] : null);
        }

        if (!array_key_exists('items', $data)) {
            if ($requireItems) {
                return ['error' => 'items requis.'];
            }
            return null;
        }

        if (!is_array($data['items']) || $data['items'] === []) {
            return ['error' => 'items invalide.'];
        }

        foreach ($order->getItems() as $existingItem) {
            $order->removeItem($existingItem);
        }

        $totalCents = 0;
        foreach ($data['items'] as $item) {
            if (!is_array($item)) {
                return ['error' => 'item invalide.'];
            }
            $gameId = $item['game_id'] ?? null;
            $quantity = $item['quantity'] ?? null;
            $unitPriceCents = $item['unit_price_cents'] ?? null;

            if (!is_int($gameId) && !ctype_digit((string) $gameId)) {
                return ['error' => 'game_id invalide.'];
            }
            if (!is_int($quantity) && !ctype_digit((string) $quantity)) {
                return ['error' => 'quantity invalide.'];
            }
            if (!is_int($unitPriceCents) && !ctype_digit((string) $unitPriceCents)) {
                return ['error' => 'unit_price_cents invalide.'];
            }

            $quantity = (int) $quantity;
            $unitPriceCents = (int) $unitPriceCents;
            if ($quantity <= 0) {
                return ['error' => 'quantity doit être > 0.'];
            }
            if ($unitPriceCents < 0) {
                return ['error' => 'unit_price_cents invalide.'];
            }

            $game = $this->gameRepository->find((int) $gameId);
            if ($game === null) {
                return ['error' => 'Jeu introuvable.'];
            }

            $orderItem = new OrderItem($order, $game, $quantity, $unitPriceCents);
            $order->addItem($orderItem);
            $totalCents += $quantity * $unitPriceCents;
        }

        $order->setTotalCents($totalCents);
        return null;
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
