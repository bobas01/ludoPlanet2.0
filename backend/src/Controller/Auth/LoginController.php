<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LoginController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function __invoke(): Response
    {
        return new JsonResponse([
            'error' => 'Login endpoint is handled by the security firewall.',
        ], Response::HTTP_BAD_REQUEST);
    }
}
