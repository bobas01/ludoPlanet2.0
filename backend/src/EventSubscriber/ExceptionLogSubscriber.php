<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Log l'exception réelle quand le kernel en reçoit une (ex. 401/403 du firewall).
 * Permet de voir en prod la cause exacte au lieu du seul "Notified event kernel.exception".
 */
final class ExceptionLogSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 255],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        $this->logger->warning('Exception traitée par le kernel (security ou autre)', [
            'exception_class' => $exception::class,
            'message' => $exception->getMessage(),
            'path' => $request->getPathInfo(),
            'method' => $request->getMethod(),
        ]);
    }
}
