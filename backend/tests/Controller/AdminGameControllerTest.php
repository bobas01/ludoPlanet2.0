<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Game;
use App\Entity\GameImage;

final class AdminGameControllerTest extends AdminWebTestCase
{
    public function testIndexRequiresAdmin(): void
    {
        $this->client->request('GET', '/api/admin/games');

        self::assertResponseStatusCodeSame(401);
    }

    public function testIndexReturnsGames(): void
    {
        $this->requestAdmin('GET', '/api/admin/games');

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('games', $data);
        self::assertIsArray($data['games']);
    }

    public function testIndexReturnsGamesWithDomainAndMechanicIds(): void
    {
        $game = new Game(100, 'Test Game');
        $game->addImage(new GameImage('/img/test.png', true));
        $this->entityManager->persist($game);
        $this->entityManager->flush();

        $admin = $this->createUser('admin2@test.com', 'Password!1234', ['ROLE_ADMIN']);
        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $token = $this->loginAndGetToken('admin2@test.com', 'Password!1234');
        $this->client->request('GET', '/api/admin/games', server: [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertCount(1, $data['games']);
        self::assertSame(100, $data['games'][0]['bggId']);
        self::assertSame('Test Game', $data['games'][0]['name']);
        self::assertArrayHasKey('domainIds', $data['games'][0]);
        self::assertArrayHasKey('mechanicIds', $data['games'][0]);
    }

    public function testCreateRequiresAdmin(): void
    {
        $this->client->request('POST', '/api/admin/games', server: [
            'CONTENT_TYPE' => 'application/json',
        ], content: json_encode(['bggId' => 1, 'name' => 'New Game']));

        self::assertResponseStatusCodeSame(401);
    }

    public function testCreateSuccess(): void
    {
        $this->requestAdmin('POST', '/api/admin/games', [
            'content' => json_encode([
                'bggId' => 42,
                'name' => 'Catan',
                'description' => 'Un jeu de plateau',
                'priceCents' => 2999,
            ]),
        ]);

        self::assertResponseStatusCodeSame(201);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame(42, $data['bggId']);
        self::assertSame('Catan', $data['name']);
    }

    public function testCreateFailsWhenBggIdAndNameMissing(): void
    {
        $this->requestAdmin('POST', '/api/admin/games', [
            'content' => json_encode([]),
        ]);

        self::assertResponseStatusCodeSame(400);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('bggId et name sont obligatoires', $data['error'] ?? null);
    }

    public function testCreateFailsWhenNameEmpty(): void
    {
        $this->requestAdmin('POST', '/api/admin/games', [
            'content' => json_encode(['bggId' => 1, 'name' => '   ']),
        ]);

        self::assertResponseStatusCodeSame(400);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Le nom ne peut pas être vide', $data['error'] ?? null);
    }

    public function testCreateFailsWhenBggIdAlreadyExists(): void
    {
        $game = new Game(99, 'Existing');
        $game->addImage(new GameImage('/img/existing.png', true));
        $this->entityManager->persist($game);
        $this->entityManager->flush();

        $this->requestAdmin('POST', '/api/admin/games', [
            'content' => json_encode(['bggId' => 99, 'name' => 'Duplicate']),
        ]);

        self::assertResponseStatusCodeSame(409);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Un jeu avec ce bggId existe déjà', $data['error'] ?? null);
    }

    public function testUpdateSuccess(): void
    {
        $game = new Game(10, 'Old Name');
        $game->addImage(new GameImage('/img/old.png', true));
        $this->entityManager->persist($game);
        $this->entityManager->flush();

        $this->requestAdmin('PUT', '/api/admin/games/10', [
            'content' => json_encode(['name' => 'New Name', 'description' => 'Updated']),
        ]);

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame(10, $data['bggId']);
        self::assertSame('New Name', $data['name']);
    }

    public function testUpdateReturns404WhenGameNotFound(): void
    {
        $this->requestAdmin('PUT', '/api/admin/games/99999', [
            'content' => json_encode(['name' => 'Any']),
        ]);

        self::assertResponseStatusCodeSame(404);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Jeu non trouvé', $data['error'] ?? null);
    }

    public function testDeleteSuccess(): void
    {
        $game = new Game(20, 'To Delete');
        $game->addImage(new GameImage('/img/del.png', true));
        $this->entityManager->persist($game);
        $this->entityManager->flush();

        $this->requestAdmin('DELETE', '/api/admin/games/20');

        self::assertResponseStatusCodeSame(204);
    }

    public function testDeleteReturns404WhenGameNotFound(): void
    {
        $this->requestAdmin('DELETE', '/api/admin/games/88888');

        self::assertResponseStatusCodeSame(404);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Jeu non trouvé', $data['error'] ?? null);
    }
}
