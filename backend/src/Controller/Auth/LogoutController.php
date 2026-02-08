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
        try {
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
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return new JsonResponse([
                'error' => 'An error occurred while logging out.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }
}
