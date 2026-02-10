<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Mechanic;
use App\Repository\MechanicRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/mechanics')]
#[IsGranted('ROLE_ADMIN')]
final class AdminMechanicController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MechanicRepository $mechanicRepository,
    ) {}

    #[Route('', name: 'admin_mechanic_index', methods: ['GET'])]
    public function index(): Response
    {
        $mechanics = $this->mechanicRepository->findAll();

        return new JsonResponse([
            'mechanics' => array_map(
                static fn(Mechanic $mechanic) => [
                    'id' => $mechanic->getId(),
                    'name' => $mechanic->getName(),
                ],
                $mechanics
            ),
        ]);
    }

    #[Route('', name: 'admin_mechanic_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true) ?? [];
        $name = isset($data['name']) ? trim((string) $data['name']) : '';

        if ($name === '') {
            return new JsonResponse(['error' => 'Le nom est obligatoire'], Response::HTTP_BAD_REQUEST);
        }

        $existing = $this->mechanicRepository->findOneBy(['name' => $name]);
        if ($existing !== null) {
            return new JsonResponse(['error' => 'Cette mécanique existe déjà'], Response::HTTP_CONFLICT);
        }

        $mechanic = new Mechanic($name);
        $this->entityManager->persist($mechanic);
        $this->entityManager->flush();

        return new JsonResponse(
            ['id' => $mechanic->getId(), 'name' => $mechanic->getName()],
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'admin_mechanic_update', requirements: ['id' => '\d+'], methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): Response
    {
        $mechanic = $this->mechanicRepository->find($id);
        if ($mechanic === null) {
            return new JsonResponse(['error' => 'Mécanique non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode((string) $request->getContent(), true) ?? [];
        if (isset($data['name'])) {
            $name = trim((string) $data['name']);
            if ($name === '') {
                return new JsonResponse(['error' => 'Le nom ne peut pas être vide'], Response::HTTP_BAD_REQUEST);
            }

            $existing = $this->mechanicRepository->findOneBy(['name' => $name]);
            if ($existing !== null && $existing->getId() !== $mechanic->getId()) {
                return new JsonResponse(['error' => 'Une autre mécanique porte déjà ce nom'], Response::HTTP_CONFLICT);
            }

            $mechanic->setName($name);
        }

        $this->entityManager->flush();

        return new JsonResponse(['id' => $mechanic->getId(), 'name' => $mechanic->getName()]);
    }

    #[Route('/{id}', name: 'admin_mechanic_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $mechanic = $this->mechanicRepository->find($id);
        if ($mechanic === null) {
            return new JsonResponse(['error' => 'Mécanique non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($mechanic);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
