<?php

namespace App\Controller;

use App\Entity\Game;
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
            'games' => array_map(fn (Game $game) => $this->gameToArray($game), $games),
        ]);
    }

    #[Route('/games/{id}', name: 'app_game_show', requirements: ['id' => '\d+'])]
    public function show(int $id, GameRepository $gameRepository): Response
    {
        $game = $gameRepository->find($id);

        if ($game === null) {
            return new JsonResponse(['error' => 'Jeu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['game' => $this->gameToArray($game)]);
    }

    private function gameToArray(Game $game): array
    {
        $categories = array_map(
            static fn ($c) => $c->getName(),
            $game->getCategories()->toArray()
        );

        return [
            'bggId' => $game->getBggId(),
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
        ];
    }
}
