<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\EmailHtmlRenderer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterController
{
    private const VERIFICATION_VALIDITY = '24 hours';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MailerInterface $mailer,
        private readonly EmailHtmlRenderer $emailHtml,
        #[Autowire('%mailer_from%')]
        private readonly string $mailerFrom,
        #[Autowire('%frontend_url%')]
        private readonly string $frontendUrl
    ) {}

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Données JSON invalides.'], Response::HTTP_BAD_REQUEST);
        }

        $email = isset($data['email']) ? trim((string) $data['email']) : '';
        $password = isset($data['password']) ? (string) $data['password'] : '';
        $firstName = isset($data['firstName']) ? trim((string) $data['firstName']) : '';
        $lastName = isset($data['lastName']) ? trim((string) $data['lastName']) : '';
        $address = isset($data['address']) ? trim((string) $data['address']) : '';
        $phoneNumber = isset($data['phoneNumber']) ? trim((string) $data['phoneNumber']) : '';
        $birthDateInput = isset($data['birthDate']) ? trim((string) $data['birthDate']) : '';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Email invalide.'], Response::HTTP_BAD_REQUEST);
        }

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

        if ($this->userRepository->findOneBy(['email' => $email]) !== null) {
            return new JsonResponse(['error' => 'Email déjà utilisé.'], Response::HTTP_CONFLICT);
        }

        $user = new User($email, '');
        $passwordHash = $this->passwordHasher->hashPassword($user, $password);
        $user->setPasswordHash($passwordHash);
        $user->setRoles(['ROLE_USER']);
        if ($firstName !== '') {
            $user->setFirstName($firstName);
        }
        if ($lastName !== '') {
            $user->setLastName($lastName);
        }
        if ($address !== '') {
            $user->setAddress($address);
        }
        if ($phoneNumber !== '') {
            $user->setPhoneNumber($phoneNumber);
        }
        if ($birthDateInput !== '') {
            $birthDate = \DateTimeImmutable::createFromFormat('Y-m-d', $birthDateInput);
            if ($birthDate === false) {
                return new JsonResponse(['error' => 'Date de naissance invalide (YYYY-MM-DD).'], Response::HTTP_BAD_REQUEST);
            }
            $user->setBirthDate($birthDate);
        }

        $verificationToken = bin2hex(random_bytes(32));
        $verificationExpiresAt = new \DateTimeImmutable('+' . self::VERIFICATION_VALIDITY);
        $user->setEmailVerificationToken($verificationToken);
        $user->setEmailVerificationTokenExpiresAt($verificationExpiresAt);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $verifyUrl = rtrim($this->frontendUrl, '/') . '/verify-email?token=' . $verificationToken;

        $bodyHtml = '<p>Bonjour,</p>'
            . '<p>Votre compte LudoPlanet a bien été créé.</p>'
            . '<p>Pour confirmer votre adresse e-mail, cliquez sur le bouton ci-dessous (lien valide 24 h).</p>'
            . '<p>Si vous n\'êtes pas à l\'origine de cette inscription, ignorez ce message.</p>'
            . '<p>À bientôt sur LudoPlanet !</p>';

        $html = $this->emailHtml->render([
            'title' => 'Confirmez votre inscription',
            'body' => $bodyHtml,
            'ctaUrl' => $verifyUrl,
            'ctaLabel' => 'Confirmer mon adresse e-mail',
        ]);

        $text = "Bonjour,\n\nVotre compte LudoPlanet a bien été créé.\n\n"
            . "Pour confirmer votre adresse e-mail, cliquez sur le lien suivant (valide 24 h) :\n"
            . $verifyUrl . "\n\n"
            . "Si vous n'êtes pas à l'origine de cette inscription, ignorez ce message.\n\n"
            . "À bientôt sur LudoPlanet !";

        $message = (new Email())
            ->from($this->mailerFrom)
            ->to($user->getEmail())
            ->subject('Confirmation de votre inscription — LudoPlanet')
            ->html($html)
            ->text($text);
        $this->mailer->send($message);

        return new JsonResponse([
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'address' => $user->getAddress(),
                'phoneNumber' => $user->getPhoneNumber(),
                'birthDate' => $user->getBirthDate()?->format('Y-m-d'),
            ],
        ], Response::HTTP_CREATED);
    }
}
