<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/category-images')]
#[IsGranted('ROLE_ADMIN')]
final class AdminCategoryImageController
{
    /** @var array<string, string> */
    private const ALLOWED_CATEGORIES = [
        'enfants' => 'enfants.png',
        'ambiance' => 'ambiance.png',
        'plateau' => 'plateau.png',
        'cartes' => 'cartes.png',
        'expert' => 'expert.png',
    ];

    public function __construct(
        private readonly KernelInterface $kernel,
    ) {}

    #[Route('/{slug}', name: 'admin_category_image_update', requirements: ['slug' => '[a-z]+'], methods: ['POST'])]
    public function update(string $slug, Request $request): Response
    {
        if (!isset(self::ALLOWED_CATEGORIES[$slug])) {
            return new JsonResponse(['error' => 'Catégorie inconnue'], Response::HTTP_BAD_REQUEST);
        }

        $file = $request->files->get('image');
        if (!$file instanceof UploadedFile) {
            return new JsonResponse(['error' => 'Fichier image manquant'], Response::HTTP_BAD_REQUEST);
        }

        if (!in_array($file->getClientMimeType(), ['image/png'], true)) {
            return new JsonResponse(['error' => 'Seules les images PNG sont acceptées'], Response::HTTP_BAD_REQUEST);
        }

        $projectDir = $this->kernel->getProjectDir();
        $targetPath = $projectDir . '/backend/public/images/categories/' . self::ALLOWED_CATEGORIES[$slug];
        $directory = \dirname($targetPath);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            return new JsonResponse(['error' => 'Impossible de créer le dossier d\'images'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            $file->move($directory, self::ALLOWED_CATEGORIES[$slug]);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Erreur lors de l\'enregistrement de l\'image'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'ok' => true,
            'path' => '/images/categories/' . self::ALLOWED_CATEGORIES[$slug],
        ]);
    }
}
