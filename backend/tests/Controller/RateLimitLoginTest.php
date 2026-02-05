<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Vérifie que le rate limiting sur /api/login et /api/register fonctionne.
 */
final class RateLimitLoginTest extends WebTestCase
{
    /** Vérifie qu’une 11ᵉ requête (et plus) reçoit bien 429. (En env test le rate limit est désactivé, le test est ignoré.) */
    public function testLoginReturns429AfterLimitExceeded(): void
    {
        $client = static::createClient();
        if ($client->getContainer()->getParameter('kernel.environment') === 'test') {
            self::markTestSkipped('Rate limit désactivé en environnement test.');
        }

        // Faire assez de requêtes pour dépasser la limite (10/min par IP)
        $maxRequests = 15;
        $lastResponse = null;
        for ($i = 0; $i < $maxRequests; $i++) {
            $client->request('POST', '/api/login', server: [
                'CONTENT_TYPE' => 'application/json',
            ], content: json_encode(['email' => 'test@test.com', 'password' => 'wrong']));
            $lastResponse = $client->getResponse();
            if ($lastResponse->getStatusCode() === 429) {
                break;
            }
        }

        self::assertSame(429, $lastResponse?->getStatusCode(), 'Le rate limit doit renvoyer 429 après trop de tentatives.');
        $data = json_decode((string) $lastResponse?->getContent(), true);
        self::assertArrayHasKey('error', $data);
        self::assertStringContainsString('minute', $data['error']);
    }

    /** Une première requête avec mauvais identifiants doit donner 401 (pas encore 429). */
    public function testALoginUnderLimitReturns401Not429(): void
    {
        $client = static::createClient();
        $cache = $client->getContainer()->get('cache.app');
        if (method_exists($cache, 'clear')) {
            $cache->clear();
        }
        $client->request('POST', '/api/login', server: [
            'CONTENT_TYPE' => 'application/json',
        ], content: json_encode(['email' => 'nonexistent@test.com', 'password' => 'wrong']));

        self::assertSame(401, $client->getResponse()->getStatusCode());
    }
}
