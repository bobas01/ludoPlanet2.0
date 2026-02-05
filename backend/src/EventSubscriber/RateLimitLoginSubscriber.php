<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Limite le nombre de requêtes POST sur /api/login et /api/register par IP (anti-bruteforce).
 * Désactivé en environnement test pour ne pas bloquer les suites de tests.
 */
final class RateLimitLoginSubscriber implements EventSubscriberInterface
{
    private const LIMIT = 10;
    private const WINDOW_SECONDS = 60;
    private const PATHS = ['/api/login', '/api/register'];

    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly KernelInterface $kernel
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 256],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($this->kernel->getEnvironment() === 'test') {
            return;
        }

        $request = $event->getRequest();
        if (!$event->isMainRequest() || $request->getMethod() !== Request::METHOD_POST) {
            return;
        }

        $path = $request->getPathInfo();
        if (!in_array($path, self::PATHS, true)) {
            return;
        }

        $ip = $request->getClientIp() ?? 'unknown';
        $pathKey = str_contains($path, 'register') ? 'register' : 'login';
        $key = 'rate_limit_' . $pathKey . '_' . md5($ip);

        $item = $this->cache->getItem($key);
        $count = $item->isHit() ? (int) $item->get() : 0;
        $count++;
        $item->set((string) $count);
        $item->expiresAfter(self::WINDOW_SECONDS);
        $this->cache->save($item);

        if ($count > self::LIMIT) {
            $event->setResponse(new Response(
                json_encode(['error' => 'Trop de tentatives. Réessaie dans une minute.']),
                Response::HTTP_TOO_MANY_REQUESTS,
                ['Content-Type' => 'application/json']
            ));
        }
    }
}
