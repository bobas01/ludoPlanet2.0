<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Game;
use App\Entity\GameImage;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;

final class OrderControllerTest extends AdminWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testAdminCanCreateOrderWithGameDetails(): void
    {
        $admin = $this->createUser('admin@example.com', 'Password!1234', ['ROLE_ADMIN']);
        $customer = $this->createUser('customer@example.com', 'Password!1234');
        $game = $this->createGameWithImage(13, 'Catan', '/uploads/catan.png');

        $this->entityManager->persist($admin);
        $this->entityManager->persist($customer);
        $this->entityManager->persist($game);
        $this->entityManager->flush();

        $token = $this->loginAndGetToken('admin@example.com', 'Password!1234');

        $payload = [
            'user_id' => $customer->getId(),
            'status' => 'paid',
            'currency' => 'EUR',
            'shipping' => [
                'full_name' => 'Bobas Admin',
                'address_line1' => '12 rue Exemple',
                'address_line2' => '',
                'postal_code' => '75000',
                'city' => 'Paris',
                'country' => 'FR',
                'phone' => '0600000000',
            ],
            'items' => [
                ['game_id' => 13, 'quantity' => 2, 'unit_price_cents' => 4594],
            ],
        ];

        $this->client->request('POST', '/api/orders', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ], content: json_encode($payload));

        self::assertResponseStatusCodeSame(201);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('paid', $data['order']['status']);
        self::assertSame($customer->getId(), $data['order']['user_id']);
        self::assertSame(9188, $data['order']['total_cents']);

        $item = $data['order']['items'][0] ?? null;
        self::assertNotNull($item);
        self::assertSame(13, $item['game_id']);
        self::assertSame('Catan', $item['game_name']);
        self::assertSame('/uploads/catan.png', $item['game_image_url']);
    }

    public function testMyOrdersRequiresAuth(): void
    {
        $this->client->request('GET', '/api/my-orders');

        self::assertResponseStatusCodeSame(401);
    }

    public function testAdminIndexRequiresAdminRole(): void
    {
        $customer = $this->createUser('customer3@example.com', 'Password!1234');
        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        $token = $this->loginAndGetToken('customer3@example.com', 'Password!1234');

        $this->client->request('GET', '/api/orders', server: [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testMyOrdersFilterAndPagination(): void
    {
        $admin = $this->createUser('admin2@example.com', 'Password!1234', ['ROLE_ADMIN']);
        $customer = $this->createUser('customer2@example.com', 'Password!1234');
        $game = $this->createGameWithImage(42, 'Tigris & Euphrates', '/uploads/tigris.png');

        $this->entityManager->persist($admin);
        $this->entityManager->persist($customer);
        $this->entityManager->persist($game);

        $olderPaid = $this->createOrder($customer, $game, 'paid', new \DateTimeImmutable('2024-01-01 10:00:00'));
        $newerPaid = $this->createOrder($customer, $game, 'paid', new \DateTimeImmutable('2024-01-02 10:00:00'));
        $this->createOrder($customer, $game, 'pending', new \DateTimeImmutable('2024-01-03 10:00:00'));

        $this->entityManager->flush();

        $token = $this->loginAndGetToken('customer2@example.com', 'Password!1234');

        $this->client->request('GET', '/api/my-orders?status=paid&limit=1&sort=desc', server: [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertCount(1, $data['orders']);
        self::assertSame($newerPaid->getId(), $data['orders'][0]['id']);
        self::assertSame('paid', $data['orders'][0]['status']);
        self::assertNotSame($olderPaid->getId(), $data['orders'][0]['id']);
    }

    private function createGameWithImage(int $bggId, string $name, string $imageUrl): Game
    {
        $game = new Game($bggId, $name);
        $image = new GameImage($imageUrl, true);
        $game->addImage($image);

        return $game;
    }

    private function createOrder(User $user, Game $game, string $status, \DateTimeImmutable $createdAt): Order
    {
        $order = new Order($user);
        $order->setStatus($status);
        $order->setCurrency('EUR');
        $order->setCreatedAt($createdAt);
        $order->setUpdatedAt($createdAt);

        $item = new OrderItem($order, $game, 1, 5000);
        $order->addItem($item);
        $order->setTotalCents(5000);

        $this->entityManager->persist($order);
        $this->entityManager->persist($item);

        return $order;
    }
}
