<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\GameImage;
use App\Repository\GameRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GameController
{
    #[Route('/games', name: 'app_games')]
    public function index(GameRepository $gameRepository): Response
    {
        $games = $gameRepository->findAll();

        return new JsonResponse([
            'games' => array_map(fn(Game $game) => $this->gameToArray($game), $games),
        ]);
    }

    #[Route('/games/{slug}', name: 'app_game_show')]
    public function show(string $slug, GameRepository $gameRepository): Response
    {
        $id = null;
        if (preg_match('/-(\d+)$/', $slug, $matches)) {
            $id = (int) $matches[1];
        } elseif (ctype_digit($slug)) {
            $id = (int) $slug;
        }

        if ($id === null) {
            return new JsonResponse(['error' => 'Jeu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $game = $gameRepository->find($id);

        if ($game === null) {
            return new JsonResponse(['error' => 'Jeu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['game' => $this->gameToArray($game)]);
    }

    private function gameToArray(Game $game): array
    {
        $categories = array_map(
            static fn($c) => $c->getName(),
            $game->getCategories()->toArray()
        );

        $images = [];
        $primaryImageUrl = null;
        foreach ($game->getImages() as $image) {
            /** @var GameImage $image */
            $imageUrl = $image->getImageUrl();
            $isPrimary = $image->isPrimary();
            $images[] = [
                'url' => $imageUrl,
                'isPrimary' => $isPrimary,
            ];
            if ($isPrimary && $primaryImageUrl === null) {
                $primaryImageUrl = $imageUrl;
            }
        }

        return [
            'bggId' => $game->getBggId(),
            'slug' => $this->makeSlug($game),
            'name' => $game->getName(),
            'yearPublished' => $game->getYearPublished(),
            'minPlayers' => $game->getMinPlayers(),
            'maxPlayers' => $game->getMaxPlayers(),
            'playTime' => $game->getPlayTime(),
            'minAge' => $game->getMinAge(),
            'description' => $game->getDescription(),
            'priceCents' => $game->getPriceCents(),
            'usersRated' => $game->getUsersRated(),
            'ratingAverage' => $game->getRatingAverage(),
            'bggRank' => $game->getBggRank(),
            'complexityAverage' => $game->getComplexityAverage(),
            'ownedUsers' => $game->getOwnedUsers(),
            'categories' => array_values($categories),
            'images' => $images,
            'primaryImageUrl' => $primaryImageUrl,
        ];
    }

    private function makeSlug(Game $game): string
    {
        $base = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $game->getName()) ?? '', '-'));
        if ($base === '') {
            $base = 'game';
        }

        return sprintf('%s-%d', $base, $game->getBggId());
    }
}
