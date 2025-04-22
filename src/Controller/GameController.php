<?php

namespace App\Controller;

use App\Game\Game;
use App\Game\CardGame;
use App\Game\DeckOfCardsGame;
use App\Game\Player;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    #[Route("/game", name: "game")]
    public function game(): Response
    {
        return $this->render('game/game.html.twig');
    }

    #[Route("/game/doc", name: "doc")]
    public function doc(): Response
    {
        return $this->render('game/doc.html.twig');
    }

    #[Route('/game/start', name: 'game_start')]
    public function start(SessionInterface $session): Response
    {
        $game = new Game();
        $game->startGame();
        $session->set('game', $game);
        return $this->redirectToRoute('game_play');
    }

    #[Route('/game/play', name: 'game_play')]
    public function play(SessionInterface $session): Response
    {
        $game = $session->get('game');
        if (!$game instanceof Game) {
            return $this->redirectToRoute('game');
        }

        return $this->render('game/play.html.twig', [
            'game' => $game,
            'player' => $game->getPlayer(),
            'dealer' => $game->getDealer(),
            'status' => $game->getStatus(),
        ]);
    }

    #[Route('/game/hit', name: 'game_hit')]
    public function hit(SessionInterface $session): Response
    {
        $game = $session->get('game');
        if ($game instanceof Game && $game->getStatus() === 'ongoing') {
            $game->hit();
            $session->set('game', $game);
        }
        return $this->redirectToRoute('game_play');
    }

    #[Route('/game/stand', name: 'game_stand')]
    public function stand(SessionInterface $session): Response
    {
        $game = $session->get('game');
        if ($game instanceof Game && $game->getStatus() === 'ongoing') {
            $game->stand();
            $session->set('game', $game);
        }
        return $this->redirectToRoute('game_play');
    }

    #[Route('/game/reset', name: 'game_reset')]
    public function reset(SessionInterface $session): Response
    {
        $session->remove('game');
        return $this->redirectToRoute('game_start');
    }
}