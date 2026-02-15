<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Service\EmailHtmlRenderer;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class ForgotPasswordController
{
    private const TOKEN_VALIDITY = '1 hour';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly EmailHtmlRenderer $emailHtml,
        #[Autowire('%mailer_from%')]
        private readonly string $mailerFrom,
        #[Autowire('%frontend_url%')]
        private readonly string $frontendUrl
    ) {}

    #[Route('/api/forgot-password', name: 'api_forgot_password', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Données JSON invalides.'], Response::HTTP_BAD_REQUEST);
        }

        $email = isset($data['email']) ? trim((string) $data['email']) : '';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Email invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);
        if ($user === null) {
            // Ne pas révéler si l'email existe
            return new JsonResponse(['message' => 'Si cet email est connu, un lien de réinitialisation a été envoyé.'], Response::HTTP_OK);
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = new \DateTimeImmutable('+' . self::TOKEN_VALIDITY);

        $user->setResetToken($token);
        $user->setResetTokenExpiresAt($expiresAt);
        $this->entityManager->flush();

        $resetUrl = rtrim($this->frontendUrl, '/') . '/reset-password?token=' . $token;

        $bodyHtml = '<p>Bonjour,</p>'
            . '<p>Vous avez demandé la réinitialisation de votre mot de passe.</p>'
            . '<p>Cliquez sur le bouton ci-dessous pour en choisir un nouveau (lien valide 1 heure).</p>'
            . '<p>Si vous n\'êtes pas à l\'origine de cette demande, ignorez ce message.</p>'
            . '<p>L\'équipe LudoPlanet</p>';

        $html = $this->emailHtml->render([
            'title' => 'Réinitialisation de votre mot de passe',
            'body' => $bodyHtml,
            'ctaUrl' => $resetUrl,
            'ctaLabel' => 'Réinitialiser mon mot de passe',
        ]);

        $text = "Bonjour,\n\nVous avez demandé la réinitialisation de votre mot de passe.\n\n"
            . "Cliquez sur le lien suivant (valide 1 heure) :\n" . $resetUrl . "\n\n"
            . "Si vous n'êtes pas à l'origine de cette demande, ignorez ce message.\n\n"
            . "L'équipe LudoPlanet";

        $message = (new Email())
            ->from($this->mailerFrom)
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe — LudoPlanet')
            ->html($html)
            ->text($text);
        $this->mailer->send($message);

        return new JsonResponse(['message' => 'Si cet email est connu, un lien de réinitialisation a été envoyé.'], Response::HTTP_OK);
    }
}
