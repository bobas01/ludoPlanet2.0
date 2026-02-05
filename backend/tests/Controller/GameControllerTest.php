<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Game;
use App\Entity\GameImage;
use Doctrine\ORM\EntityManagerInterface;

final class GameControllerTest extends DatabaseWebTestCase
{
    private EntityManagerInterface $entityManager;
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->setUpDatabase($this->entityManager);
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
        $game = new Game(123, 'Catan');
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
