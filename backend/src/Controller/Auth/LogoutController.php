<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LogoutController
{
    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function __invoke(): Response
    {
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
