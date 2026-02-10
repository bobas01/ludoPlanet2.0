<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/categories')]
#[IsGranted('ROLE_ADMIN')]
final class AdminCategoryController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CategoryRepository $categoryRepository,
    ) {}

    #[Route('', name: 'admin_category_index', methods: ['GET'])]
    public function index(): Response
    {
        $categories = $this->categoryRepository->findAll();

        return new JsonResponse([
            'categories' => array_map(
                static fn(Category $category) => [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                ],
                $categories
            ),
        ]);
    }

    #[Route('', name: 'admin_category_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true) ?? [];
        $name = isset($data['name']) ? trim((string) $data['name']) : '';

        if ($name === '') {
            return new JsonResponse(['error' => 'Le nom est obligatoire'], Response::HTTP_BAD_REQUEST);
        }

        $existing = $this->categoryRepository->findOneBy(['name' => $name]);
        if ($existing !== null) {
            return new JsonResponse(['error' => 'Cette catégorie existe déjà'], Response::HTTP_CONFLICT);
        }

        $category = new Category($name);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return new JsonResponse(
            ['id' => $category->getId(), 'name' => $category->getName()],
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'admin_category_update', requirements: ['id' => '\d+'], methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): Response
    {
        $category = $this->categoryRepository->find($id);
        if ($category === null) {
            return new JsonResponse(['error' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode((string) $request->getContent(), true) ?? [];
        if (isset($data['name'])) {
            $name = trim((string) $data['name']);
            if ($name === '') {
                return new JsonResponse(['error' => 'Le nom ne peut pas être vide'], Response::HTTP_BAD_REQUEST);
            }

            $existing = $this->categoryRepository->findOneBy(['name' => $name]);
            if ($existing !== null && $existing->getId() !== $category->getId()) {
                return new JsonResponse(['error' => 'Une autre catégorie porte déjà ce nom'], Response::HTTP_CONFLICT);
            }

            $category->setName($name);
        }

        $this->entityManager->flush();

        return new JsonResponse(['id' => $category->getId(), 'name' => $category->getName()]);
    }

    #[Route('/{id}', name: 'admin_category_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $category = $this->categoryRepository->find($id);
        if ($category === null) {
            return new JsonResponse(['error' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
