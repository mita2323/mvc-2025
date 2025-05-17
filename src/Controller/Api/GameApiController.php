<?php

namespace App\Controller\Api;

use App\Game\Game;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles API requests for the game.
 */
#[Route('/api/game', name: 'api_game_')]
class GameApiController extends AbstractController
{
    /**
     * Retrieves the current game status from the session.
     * @param SessionInterface $session The session storage.
     * @return JsonReponse The game status or error message.
     */
    #[Route('', name: 'status', methods: ['GET'])]
    public function gameStatus(SessionInterface $session): JsonResponse
    {
        $game = $session->get('game');
        if (!$game instanceof Game) {
            return new JsonResponse([
                'error' => 'No active game found.'
            ], 404);
        }

        $player = $game->getPlayer();
        $dealer = $game->getDealer();

        $playerHand = array_map(function ($card) {
            return $card->getAsString();
        }, $player->getHand());

        $dealerHand = array_map(function ($card) {
            return $card->getAsString();
        }, $dealer->getHand());

        return new JsonResponse([
            'game' => [
                'status' => $game->getStatus(),
                'player' => [
                    'name' => $player->getName(),
                    'hand' => $playerHand,
                    'score' => $player->getScore(),
                ],
                'dealer' => [
                    'name' => $dealer->getName(),
                    'hand' => $dealerHand,
                    'score' => $dealer->getScore(),
                ],
            ]
        ]);
    }
}
