<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class CorsEventSubscriber implements EventSubscriberInterface
{
    private const DEFAULT_ORIGINS = [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://localhost:4173',
        'http://127.0.0.1:4173',
        'http://72.60.189.212:3500',
    ];

    /** @var list<string> */
    private readonly array $allowedOrigins;

    public function __construct(string $corsAllowedOrigins = '')
    {
        $extra = $corsAllowedOrigins !== ''
            ? array_filter(array_map('trim', explode(',', $corsAllowedOrigins)))
            : [];
        $all = array_merge(self::DEFAULT_ORIGINS, $extra);
        $this->allowedOrigins = array_values(array_unique(array_map([self::class, 'normalizeOrigin'], $all)));
    }

    private static function normalizeOrigin(string $origin): string
    {
        return rtrim($origin, '/');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST  => ['onKernelRequest', 100],
            // Priorité basse pour ajouter CORS en dernier (éviter qu'un autre listener écrase les en-têtes)
            KernelEvents::RESPONSE => ['onKernelResponse', -512],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || $event->getRequest()->getMethod() !== Request::METHOD_OPTIONS) {
            return;
        }

        $origin = self::normalizeOrigin($event->getRequest()->headers->get('Origin', ''));
        if ($origin === '' || !in_array($origin, $this->allowedOrigins, true)) {
            return;
        }

        $response = new Response('', Response::HTTP_NO_CONTENT);
        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');
        $event->setResponse($response);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $origin = self::normalizeOrigin($request->headers->get('Origin', ''));

        if ($origin === '' || !in_array($origin, $this->allowedOrigins, true)) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');
    }
}
