<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Mechanic;

final class AdminMechanicControllerTest extends AdminWebTestCase
{
    public function testIndexRequiresAdmin(): void
    {
        $this->client->request('GET', '/api/admin/mechanics');

        self::assertResponseStatusCodeSame(401);
    }

    public function testIndexReturnsMechanics(): void
    {
        $this->requestAdmin('GET', '/api/admin/mechanics');

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('mechanics', $data);
        self::assertIsArray($data['mechanics']);
    }

    public function testCreateSuccess(): void
    {
        $this->requestAdmin('POST', '/api/admin/mechanics', [
            'content' => json_encode(['name' => 'Dés']),
        ]);

        self::assertResponseStatusCodeSame(201);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('id', $data);
        self::assertSame('Dés', $data['name']);
    }

    public function testCreateFailsWhenNameMissing(): void
    {
        $this->requestAdmin('POST', '/api/admin/mechanics', [
            'content' => json_encode([]),
        ]);

        self::assertResponseStatusCodeSame(400);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Le nom est obligatoire', $data['error'] ?? null);
    }

    public function testCreateFailsWhenNameEmpty(): void
    {
        $this->requestAdmin('POST', '/api/admin/mechanics', [
            'content' => json_encode(['name' => '   ']),
        ]);

        self::assertResponseStatusCodeSame(400);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Le nom est obligatoire', $data['error'] ?? null);
    }

    public function testCreateFailsWhenMechanicAlreadyExists(): void
    {
        $mechanic = new Mechanic('Cartes');
        $this->entityManager->persist($mechanic);
        $this->entityManager->flush();

        $this->requestAdmin('POST', '/api/admin/mechanics', [
            'content' => json_encode(['name' => 'Cartes']),
        ]);

        self::assertResponseStatusCodeSame(409);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Cette mécanique existe déjà', $data['error'] ?? null);
    }

    public function testDeleteSuccess(): void
    {
        $mechanic = new Mechanic('À supprimer');
        $this->entityManager->persist($mechanic);
        $this->entityManager->flush();
        $id = $mechanic->getId();
        self::assertNotNull($id);

        $this->requestAdmin('DELETE', '/api/admin/mechanics/' . $id);

        self::assertResponseStatusCodeSame(204);
    }

    public function testDeleteReturns404WhenMechanicNotFound(): void
    {
        $this->requestAdmin('DELETE', '/api/admin/mechanics/99999');

        self::assertResponseStatusCodeSame(404);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Mécanique non trouvée', $data['error'] ?? null);
    }
}
