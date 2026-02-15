<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VerifyEmailController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/api/verify-email', name: 'api_verify_email', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $token = $request->query->get('token');
        if ($token === null || $token === '') {
            return new JsonResponse(['error' => 'Token manquant.'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->findOneBy(['emailVerificationToken' => $token]);
        if ($user === null) {
            return new JsonResponse(['error' => 'Lien invalide ou déjà utilisé.'], Response::HTTP_BAD_REQUEST);
        }

        $expiresAt = $user->getEmailVerificationTokenExpiresAt();
        if ($expiresAt === null || $expiresAt < new \DateTimeImmutable()) {
            $user->setEmailVerificationToken(null);
            $user->setEmailVerificationTokenExpiresAt(null);
            $this->entityManager->flush();
            return new JsonResponse(['error' => 'Lien expiré.'], Response::HTTP_BAD_REQUEST);
        }

        $user->setEmailVerifiedAt(new \DateTimeImmutable());
        $user->setEmailVerificationToken(null);
        $user->setEmailVerificationTokenExpiresAt(null);
        $user->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Adresse e-mail confirmée. Vous pouvez vous connecter.'], Response::HTTP_OK);
    }
}
