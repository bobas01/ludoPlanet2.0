<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class AdminCategoryImageControllerTest extends AdminWebTestCase
{
    public function testUpdateRequiresAdmin(): void
    {
        $this->client->request('POST', '/api/admin/category-images/enfants');

        self::assertResponseStatusCodeSame(401);
    }

    public function testUpdateReturns400WhenSlugUnknown(): void
    {
        // Slug doit matcher la route [a-z]+ ; "invalide" n'est pas dans ALLOWED_CATEGORIES
        $this->requestAdmin('POST', '/api/admin/category-images/invalide');

        self::assertResponseStatusCodeSame(400);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Catégorie inconnue', $data['error'] ?? null);
    }

    public function testUpdateReturns400WhenImageMissing(): void
    {
        $this->requestAdmin('POST', '/api/admin/category-images/enfants');

        self::assertResponseStatusCodeSame(400);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Fichier image manquant', $data['error'] ?? null);
    }

    public function testUpdateReturns400WhenNotPng(): void
    {
        $admin = $this->createUser('admin-cat@test.com', 'Password!1234', ['ROLE_ADMIN']);
        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $token = $this->loginAndGetToken('admin-cat@test.com', 'Password!1234');

        $tmpFile = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($tmpFile, 'not a png');
        $upload = new UploadedFile($tmpFile, 'fake.jpg', 'image/jpeg', null, true);

        $this->client->request('POST', '/api/admin/category-images/enfants', [], [
            'image' => $upload,
        ], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        self::assertResponseStatusCodeSame(400);
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame('Seules les images PNG sont acceptées', $data['error'] ?? null);

        @unlink($tmpFile);
    }
}
