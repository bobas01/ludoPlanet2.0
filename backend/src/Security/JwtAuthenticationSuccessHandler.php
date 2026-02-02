<?php

declare(strict_types=1);

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final class JwtAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private const COOKIE_NAME = 'AUTH_TOKEN';

    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
        #[Autowire('%jwt_ttl%')]
        private readonly int $jwtTtl
    ) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $user = $token->getUser();
        $jwt = $this->jwtManager->create($user);

        $response = new JsonResponse(['token' => $jwt]);
        $response->headers->setCookie(new Cookie(
            self::COOKIE_NAME,
            $jwt,
            time() + $this->jwtTtl,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            Cookie::SAMESITE_LAX
        ));

        return $response;
    }
}
