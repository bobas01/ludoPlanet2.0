<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Domain;

final class AdminDomainControllerTest extends AdminWebTestCase
{
    public function testIndexRequiresAdmin(): void
    {
        $this->client->request('GET', '/api/admin/domains');

        self::assertResponseStatusCodeSame(401);
    }

    public function testIndexReturnsDomains(): void
    {
        $this->requestAdmin('GET', '/api/admin/domains');

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('domains', $data);
        self::assertIsArray($data['domains']);
    }

    public function testCreateSuccess(): void
    {
        $this->requestAdmin('POST', '/api/admin/domains', [
            'content' => json_encode(['name' => 'Stratégie']),
        ]);

        self::assertResponseStatusCodeSame(201);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('id', $data);
        self::assertSame('Stratégie', $data['name']);
    }

    public function testCreateFailsWhenNameMissing(): void
    {
        $this->requestAdmin('POST', '/api/admin/domains', [
            'content' => json_encode([]),
        ]);

        self::assertResponseStatusCodeSame(400);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Le nom est obligatoire', $data['error'] ?? null);
    }

    public function testCreateFailsWhenNameEmpty(): void
    {
        $this->requestAdmin('POST', '/api/admin/domains', [
            'content' => json_encode(['name' => '   ']),
        ]);

        self::assertResponseStatusCodeSame(400);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Le nom est obligatoire', $data['error'] ?? null);
    }

    public function testCreateFailsWhenDomainAlreadyExists(): void
    {
        $domain = new Domain('Ambiance');
        $this->entityManager->persist($domain);
        $this->entityManager->flush();

        $this->requestAdmin('POST', '/api/admin/domains', [
            'content' => json_encode(['name' => 'Ambiance']),
        ]);

        self::assertResponseStatusCodeSame(409);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Ce domaine existe déjà', $data['error'] ?? null);
    }

    public function testDeleteSuccess(): void
    {
        $domain = new Domain('À supprimer');
        $this->entityManager->persist($domain);
        $this->entityManager->flush();
        $id = $domain->getId();
        self::assertNotNull($id);

        $this->requestAdmin('DELETE', '/api/admin/domains/' . $id);

        self::assertResponseStatusCodeSame(204);
    }

    public function testDeleteReturns404WhenDomainNotFound(): void
    {
        $this->requestAdmin('DELETE', '/api/admin/domains/99999');

        self::assertResponseStatusCodeSame(404);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Domaine non trouvé', $data['error'] ?? null);
    }
}
