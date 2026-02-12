<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Game;
use App\Entity\GameImage;

final class GameControllerTest extends AdminWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/games');

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('games', $data);
        self::assertIsArray($data['games']);
    }

    public function testShowReturnsGameWhenExists(): void
    {
        $game = new Game(123, 'Catan', 'catan-123');
        $game->addImage(new GameImage('/img/catan.png', true));
        $this->entityManager->persist($game);
        $this->entityManager->flush();

        $this->client->request('GET', '/games/123');

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame(123, $data['game']['bggId']);
        self::assertSame('Catan', $data['game']['name']);
        self::assertArrayHasKey('images', $data['game']);
    }

    public function testShowReturns404WhenGameNotFound(): void
    {
        $this->client->request('GET', '/games/99999');

        self::assertResponseStatusCodeSame(404);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Jeu non trouvé', $data['error'] ?? null);
    }
}
