<?php

declare(strict_types=1);

namespace App\Tests\Controller\Auth;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AuthControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        if ($metadata !== []) {
            $schemaTool = new SchemaTool($this->entityManager);
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        }
    }

    public function testRegister(): void
    {
        $payload = [
            'email' => 'test@example.com',
            'password' => 'Password!1234',
            'firstName' => 'Jean',
            'lastName' => 'Dupont',
            'address' => '12 rue des Lilas',
            'phoneNumber' => '0612345678',
            'birthDate' => '2000-05-12',
        ];

        $this->client->request('POST', '/api/register', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode($payload));

        self::assertResponseStatusCodeSame(201);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('test@example.com', $data['user']['email']);
        self::assertSame('Jean', $data['user']['firstName']);
    }

    public function testLoginAndMe(): void
    {
        $user = $this->createUser('login@example.com', 'Password!1234');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->request('POST', '/api/login', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'email' => 'login@example.com',
            'password' => 'Password!1234',
        ]));

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('token', $data);

        $this->client->request('GET', '/api/me', server: ['HTTP_AUTHORIZATION' => 'Bearer ' . $data['token']]);
        self::assertResponseIsSuccessful();
        $me = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('login@example.com', $me['user']['email']);
    }

    public function testUpdateAndDelete(): void
    {
        $user = $this->createUser('update@example.com', 'Password!1234');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $token = $this->loginAndGetToken($this->client, 'update@example.com', 'Password!1234');

        $this->client->request('PUT', '/api/me', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ], content: json_encode([
            'address' => 'Nouvelle adresse',
        ]));

        self::assertResponseIsSuccessful();
        $updated = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Nouvelle adresse', $updated['user']['address']);

        $this->client->request('DELETE', '/api/me', server: ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]);
        self::assertResponseIsSuccessful();
        self::assertNull($this->entityManager->getRepository(User::class)->findOneBy(['email' => 'update@example.com']));
    }

    private function createUser(string $email, string $plainPassword): User
    {
        $user = new User($email, '');
        $user->setFirstName('Jean');
        $user->setLastName('Dupont');
        $user->setAddress('12 rue des Lilas');
        $user->setPhoneNumber('0612345678');
        $user->setBirthDate(new \DateTimeImmutable('2000-05-12'));

        $passwordHash = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPasswordHash($passwordHash);
        $user->setRoles(['ROLE_USER']);

        return $user;
    }

    private function loginAndGetToken($client, string $email, string $password): string
    {
        $client->request('POST', '/api/login', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'email' => $email,
            'password' => $password,
        ]));

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        return $data['token'] ?? '';
    }
}
