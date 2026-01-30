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
    private const ALLOWED_ORIGINS = ['http://localhost:5173', 'http://127.0.0.1:5173', 'http://localhost:4173', 'http://127.0.0.1:4173'];

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 100],
            KernelEvents::RESPONSE => ['onKernelResponse', 100],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || $event->getRequest()->getMethod() !== Request::METHOD_OPTIONS) {
            return;
        }

        $origin = $event->getRequest()->headers->get('Origin', '');
        if (!in_array($origin, self::ALLOWED_ORIGINS, true)) {
            return;
        }

        $response = new Response('', Response::HTTP_NO_CONTENT);
        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Max-Age', '86400');
        $event->setResponse($response);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $origin = $request->headers->get('Origin', '');

        if (!in_array($origin, self::ALLOWED_ORIGINS, true)) {
            return;
        }

        $event->getResponse()->headers->set('Access-Control-Allow-Origin', $origin);
        $event->getResponse()->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $event->getResponse()->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $event->getResponse()->headers->set('Access-Control-Max-Age', '86400');
    }
}
