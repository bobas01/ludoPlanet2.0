<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Domain;
use App\Repository\DomainRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/domains')]
final class AdminDomainController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DomainRepository $domainRepository,
    ) {}

    #[Route('', name: 'admin_domain_index', methods: ['GET'])]
    public function index(): Response
    {
        $domains = $this->domainRepository->findAll();

        return new JsonResponse([
            'domains' => array_map(
                static fn(Domain $domain) => [
                    'id' => $domain->getId(),
                    'name' => $domain->getName(),
                ],
                $domains
            ),
        ]);
    }

    #[Route('', name: 'admin_domain_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true) ?? [];
        $name = isset($data['name']) ? trim((string) $data['name']) : '';

        if ($name === '') {
            return new JsonResponse(['error' => 'Le nom est obligatoire'], Response::HTTP_BAD_REQUEST);
        }

        $existing = $this->domainRepository->findOneBy(['name' => $name]);
        if ($existing !== null) {
            return new JsonResponse(['error' => 'Ce domaine existe déjà'], Response::HTTP_CONFLICT);
        }

        $domain = new Domain($name);
        $this->entityManager->persist($domain);
        $this->entityManager->flush();

        return new JsonResponse(
            ['id' => $domain->getId(), 'name' => $domain->getName()],
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'admin_domain_update', requirements: ['id' => '\d+'], methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): Response
    {
        $domain = $this->domainRepository->find($id);
        if ($domain === null) {
            return new JsonResponse(['error' => 'Domaine non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode((string) $request->getContent(), true) ?? [];
        if (isset($data['name'])) {
            $name = trim((string) $data['name']);
            if ($name === '') {
                return new JsonResponse(['error' => 'Le nom ne peut pas être vide'], Response::HTTP_BAD_REQUEST);
            }

            $existing = $this->domainRepository->findOneBy(['name' => $name]);
            if ($existing !== null && $existing->getId() !== $domain->getId()) {
                return new JsonResponse(['error' => 'Un autre domaine porte déjà ce nom'], Response::HTTP_CONFLICT);
            }

            $domain->setName($name);
        }

        $this->entityManager->flush();

        return new JsonResponse(['id' => $domain->getId(), 'name' => $domain->getName()]);
    }

    #[Route('/{id}', name: 'admin_domain_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $domain = $this->domainRepository->find($id);
        if ($domain === null) {
            return new JsonResponse(['error' => 'Domaine non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($domain);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
