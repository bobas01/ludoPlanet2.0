<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class AdminWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;
    protected UserPasswordHasherInterface $passwordHasher;
    protected Connection $connection;

    /** Schéma créé une seule fois ; chaque test démarre avec des tables vides (truncate). */
    private static bool $schemaCreated = false;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->connection = $this->entityManager->getConnection();
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        if (!self::$schemaCreated) {
            $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
            if ($metadata !== []) {
                $schemaTool = new SchemaTool($this->entityManager);
                $schemaTool->dropSchema($metadata);
                $schemaTool->createSchema($metadata);
            }
            self::$schemaCreated = true;
        } else {
            $this->truncateTables();
        }
    }

    private function truncateTables(): void
    {
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        if ($metadata === []) {
            return;
        }

        $schemaTool = new SchemaTool($this->entityManager);
        $schema = $schemaTool->getSchemaFromMetadata($metadata);
        $platform = $this->connection->getDatabasePlatform();
        $isMysql = $platform instanceof AbstractMySQLPlatform;

        if ($isMysql) {
            $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        }

        try {
            foreach ($schema->getTables() as $table) {
                $name = $table->getShortestName($schema->getName());
                $quoted = $platform->quoteIdentifier($name);
                $this->connection->executeStatement($isMysql ? 'TRUNCATE ' . $quoted : 'DELETE FROM ' . $quoted);
            }
            $this->entityManager->clear();
        } finally {
            if ($isMysql) {
                $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
            }
        }
    }

    protected function createUser(string $email, string $plainPassword, array $roles = ['ROLE_USER']): User
    {
        $user = new User($email, '');
        $user->setFirstName('Jean');
        $user->setLastName('Dupont');
        $user->setAddress('12 rue des Lilas');
        $user->setPhoneNumber('0612345678');
        $user->setBirthDate(new \DateTimeImmutable('2000-05-12'));

        $passwordHash = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPasswordHash($passwordHash);
        $user->setRoles($roles);

        return $user;
    }

    protected function loginAndGetToken(string $email, string $password): string
    {
        $this->client->request('POST', '/api/login', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'email' => $email,
            'password' => $password,
        ]));

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        return $data['token'] ?? '';
    }

    protected function requestAdmin(string $method, string $uri, array $options = []): void
    {
        $admin = $this->createUser('admin@test.com', 'Password!1234', ['ROLE_ADMIN']);
        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $token = $this->loginAndGetToken('admin@test.com', 'Password!1234');

        $server = $options['server'] ?? [];
        $server['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        if (isset($options['content'])) {
            $server['CONTENT_TYPE'] = $server['CONTENT_TYPE'] ?? 'application/json';
        }

        $this->client->request($method, $uri, server: $server, content: $options['content'] ?? null);
    }
}
