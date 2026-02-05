<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

/**
 * Log les tentatives de connexion échouées (IP, email si présent, message).
 */
final class LoginFailureLogSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginFailureEvent::class => ['onLoginFailure', 0],
        ];
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $request = $event->getRequest();
        $ip = $request->getClientIp() ?? 'unknown';
        $email = $this->getEmailFromRequest($request);
        $message = $event->getException()->getMessage();

        $this->logger->warning('Échec de connexion', [
            'ip' => $ip,
            'email' => $email,
            'message' => $message,
        ]);
    }

    private function getEmailFromRequest(Request $request): ?string
    {
        $content = $request->getContent();
        if (!is_string($content)) {
            return null;
        }
        $data = json_decode($content, true);
        if (!is_array($data) || !isset($data['email'])) {
            return null;
        }
        return is_string($data['email']) ? $data['email'] : null;
    }
}
