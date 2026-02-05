<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Domain;
use App\Entity\Game;
use App\Entity\GameImage;
use App\Entity\Mechanic;
use App\Repository\CategoryRepository;
use App\Repository\DomainRepository;
use App\Repository\GameRepository;
use App\Repository\MechanicRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/games')]
final class AdminGameController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GameRepository $gameRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly DomainRepository $domainRepository,
        private readonly MechanicRepository $mechanicRepository,
    ) {}

    #[Route('', name: 'admin_game_index', methods: ['GET'])]
    public function index(): Response
    {
        $games = $this->gameRepository->findAll();

        return new JsonResponse([
            'games' => array_map(
                function (Game $game): array {
                    return [
                        'bggId' => $game->getBggId(),
                        'name' => $game->getName(),
                        'description' => $game->getDescription(),
                        'priceCents' => $game->getPriceCents(),
                        'domainIds' => array_map(
                            static fn(Domain $domain): int => $domain->getId(),
                            $game->getDomains()->toArray()
                        ),
                        'mechanicIds' => array_map(
                            static fn(Mechanic $mechanic): int => $mechanic->getId(),
                            $game->getMechanics()->toArray()
                        ),
                    ];
                },
                $games
            ),
        ]);
    }

    #[Route('', name: 'admin_game_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true) ?? [];

        if (!isset($data['bggId'], $data['name'])) {
            return new JsonResponse(['error' => 'bggId et name sont obligatoires'], Response::HTTP_BAD_REQUEST);
        }

        $bggId = (int) $data['bggId'];
        $name = trim((string) $data['name']);

        if ($name === '') {
            return new JsonResponse(['error' => 'Le nom ne peut pas être vide'], Response::HTTP_BAD_REQUEST);
        }

        if ($this->gameRepository->find($bggId) !== null) {
            return new JsonResponse(['error' => 'Un jeu avec ce bggId existe déjà'], Response::HTTP_CONFLICT);
        }

        $game = new Game($bggId, $name);
        $this->applyGameData($game, $data);

        $this->entityManager->persist($game);
        $this->entityManager->flush();

        return new JsonResponse(['bggId' => $game->getBggId(), 'name' => $game->getName()], Response::HTTP_CREATED);
    }

    #[Route('/{bggId}', name: 'admin_game_update', requirements: ['bggId' => '\d+'], methods: ['PUT', 'PATCH'])]
    public function update(int $bggId, Request $request): Response
    {
        $game = $this->gameRepository->find($bggId);
        if ($game === null) {
            return new JsonResponse(['error' => 'Jeu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode((string) $request->getContent(), true) ?? [];

        if (isset($data['name'])) {
            $name = trim((string) $data['name']);
            if ($name === '') {
                return new JsonResponse(['error' => 'Le nom ne peut pas être vide'], Response::HTTP_BAD_REQUEST);
            }
            $game->setName($name);
        }

        $this->applyGameData($game, $data, true);
        $this->entityManager->flush();

        return new JsonResponse(['bggId' => $game->getBggId(), 'name' => $game->getName()]);
    }

    #[Route('/{bggId}', name: 'admin_game_delete', requirements: ['bggId' => '\d+'], methods: ['DELETE'])]
    public function delete(int $bggId): Response
    {
        $game = $this->gameRepository->find($bggId);
        if ($game === null) {
            return new JsonResponse(['error' => 'Jeu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($game);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function applyGameData(Game $game, array $data, bool $isUpdate = false): void
    {
        if (isset($data['yearPublished'])) {
            $game->setYearPublished($data['yearPublished'] !== null ? (int) $data['yearPublished'] : null);
        }
        if (isset($data['minPlayers'])) {
            $game->setMinPlayers($data['minPlayers'] !== null ? (int) $data['minPlayers'] : null);
        }
        if (isset($data['maxPlayers'])) {
            $game->setMaxPlayers($data['maxPlayers'] !== null ? (int) $data['maxPlayers'] : null);
        }
        if (isset($data['playTime'])) {
            $game->setPlayTime($data['playTime'] !== null ? (int) $data['playTime'] : null);
        }
        if (isset($data['minAge'])) {
            $game->setMinAge($data['minAge'] !== null ? (int) $data['minAge'] : null);
        }
        if (array_key_exists('description', $data)) {
            $game->setDescription($data['description'] !== null ? (string) $data['description'] : null);
        }
        if (isset($data['priceCents'])) {
            $game->setPriceCents($data['priceCents'] !== null ? (int) $data['priceCents'] : null);
        }

        if (isset($data['categoryIds']) && is_array($data['categoryIds'])) {
            if ($isUpdate) {
                foreach ($game->getCategories() as $existing) {
                    $game->removeCategory($existing);
                }
            }

            /** @var list<int> $ids */
            $ids = array_map('intval', $data['categoryIds']);
            foreach ($ids as $id) {
                $category = $this->categoryRepository->find($id);
                if ($category instanceof Category) {
                    $game->addCategory($category);
                }
            }
        }

        if (isset($data['domainIds']) && is_array($data['domainIds'])) {
            if ($isUpdate) {
                foreach ($game->getDomains() as $existing) {
                    $game->removeDomain($existing);
                }
            }

            /** @var list<int> $ids */
            $ids = array_map('intval', $data['domainIds']);
            foreach ($ids as $id) {
                $domain = $this->domainRepository->find($id);
                if ($domain instanceof Domain) {
                    $game->addDomain($domain);
                }
            }
        }

        if (isset($data['mechanicIds']) && is_array($data['mechanicIds'])) {
            if ($isUpdate) {
                foreach ($game->getMechanics() as $existing) {
                    $game->removeMechanic($existing);
                }
            }

            /** @var list<int> $ids */
            $ids = array_map('intval', $data['mechanicIds']);
            foreach ($ids as $id) {
                $mechanic = $this->mechanicRepository->find($id);
                if ($mechanic instanceof Mechanic) {
                    $game->addMechanic($mechanic);
                }
            }
        }

        if (isset($data['images']) && is_array($data['images'])) {
            if ($isUpdate) {
                foreach ($game->getImages() as $existing) {
                    $game->removeImage($existing);
                    $this->entityManager->remove($existing);
                }
            }

            foreach ($data['images'] as $imageData) {
                if (!isset($imageData['url'])) {
                    continue;
                }
                $url = trim((string) $imageData['url']);
                if ($url === '') {
                    continue;
                }

                $isPrimary = isset($imageData['isPrimary']) ? (bool) $imageData['isPrimary'] : false;
                $image = new GameImage($url, $isPrimary);
                $game->addImage($image);
                $this->entityManager->persist($image);
            }
        }
    }
}
