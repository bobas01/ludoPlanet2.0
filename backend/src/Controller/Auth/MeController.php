<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class MeController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function __invoke(Security $security): Response
    {
        $user = $security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'address' => $user->getAddress(),
                'phoneNumber' => $user->getPhoneNumber(),
                'birthDate' => $user->getBirthDate()->format('Y-m-d'),
            ],
        ]);
    }

    #[Route('/api/me', name: 'api_me_update', methods: ['PUT'])]
    public function update(Security $security, Request $request): Response
    {
        $user = $security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Données JSON invalides.'], Response::HTTP_BAD_REQUEST);
        }

        $email = isset($data['email']) ? trim((string) $data['email']) : null;
        $firstName = isset($data['firstName']) ? trim((string) $data['firstName']) : null;
        $lastName = isset($data['lastName']) ? trim((string) $data['lastName']) : null;
        $address = isset($data['address']) ? trim((string) $data['address']) : null;
        $phoneNumber = isset($data['phoneNumber']) ? trim((string) $data['phoneNumber']) : null;
        $birthDateInput = isset($data['birthDate']) ? trim((string) $data['birthDate']) : null;
        $password = isset($data['password']) ? (string) $data['password'] : null;

        if ($email !== null) {
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return new JsonResponse(['error' => 'Email invalide.'], Response::HTTP_BAD_REQUEST);
            }
            $existing = $this->userRepository->findOneBy(['email' => $email]);
            if ($existing !== null && $existing->getId() !== $user->getId()) {
                return new JsonResponse(['error' => 'Email déjà utilisé.'], Response::HTTP_CONFLICT);
            }
            $user->setEmail($email);
        }

        if ($firstName !== null) {
            if ($firstName === '') {
                return new JsonResponse(['error' => 'Prénom requis.'], Response::HTTP_BAD_REQUEST);
            }
            $user->setFirstName($firstName);
        }

        if ($lastName !== null) {
            if ($lastName === '') {
                return new JsonResponse(['error' => 'Nom requis.'], Response::HTTP_BAD_REQUEST);
            }
            $user->setLastName($lastName);
        }

        if ($address !== null) {
            if ($address === '') {
                return new JsonResponse(['error' => 'Adresse requise.'], Response::HTTP_BAD_REQUEST);
            }
            $user->setAddress($address);
        }

        if ($phoneNumber !== null) {
            if ($phoneNumber === '') {
                return new JsonResponse(['error' => 'Numéro de téléphone requis.'], Response::HTTP_BAD_REQUEST);
            }
            $user->setPhoneNumber($phoneNumber);
        }

        if ($birthDateInput !== null) {
            $birthDate = \DateTimeImmutable::createFromFormat('Y-m-d', $birthDateInput);
            if ($birthDate === false) {
                return new JsonResponse(['error' => 'Date de naissance invalide (YYYY-MM-DD).'], Response::HTTP_BAD_REQUEST);
            }
            $age = $birthDate->diff(new \DateTimeImmutable('today'))->y;
            if ($age < 18) {
                return new JsonResponse(['error' => 'Vous devez être majeur.'], Response::HTTP_BAD_REQUEST);
            }
            $user->setBirthDate($birthDate);
        }

        if ($password !== null && $password !== '') {
            $allowedSpecials = '!@#$%^&*()_+-=[]{};:\'",.<>/?\\|`~';
            if (strlen($password) < 12) {
                return new JsonResponse([
                    'error' => 'Mot de passe trop court (12 caractères minimum).',
                ], Response::HTTP_BAD_REQUEST);
            }
            if (!preg_match('/[A-Z]/', $password)) {
                return new JsonResponse([
                    'error' => 'Le mot de passe doit contenir au moins une majuscule.',
                ], Response::HTTP_BAD_REQUEST);
            }
            $specialPattern = '/[' . preg_quote($allowedSpecials, '/') . ']/';
            if (!preg_match($specialPattern, $password)) {
                return new JsonResponse([
                    'error' => 'Le mot de passe doit contenir au moins un caractère spécial autorisé.',
                    'allowedSpecials' => $allowedSpecials,
                ], Response::HTTP_BAD_REQUEST);
            }
            $passwordHash = $this->passwordHasher->hashPassword($user, $password);
            $user->setPasswordHash($passwordHash);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'address' => $user->getAddress(),
                'phoneNumber' => $user->getPhoneNumber(),
                'birthDate' => $user->getBirthDate()->format('Y-m-d'),
            ],
        ]);
    }

    #[Route('/api/me', name: 'api_me_delete', methods: ['DELETE'])]
    public function delete(Security $security): Response
    {
        $user = $security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        } catch (\Throwable) {
            return new JsonResponse([
                'error' => 'Suppression impossible. Veuillez contacter le support.',
            ], Response::HTTP_CONFLICT);
        }

        $response = new JsonResponse(['ok' => true]);
        $response->headers->setCookie(new Cookie(
            'AUTH_TOKEN',
            '',
            time() - 3600,
            '/',
            null,
            false,
            true,
            false,
            Cookie::SAMESITE_LAX
        ));

        return $response;
    }
}
