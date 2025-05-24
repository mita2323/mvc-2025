<?php

namespace App\Controller;

use App\Project\BlackJack;
use App\Project\BlackJackGame;
use App\Project\BlackJackDeck;
use App\Project\BlackJackPlayer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    #[Route("/proj", name: "project")]
    public function project(): Response
    {
        return $this->render('project/game.html.twig');
    }

    #[Route("/proj/rules", name: "rules")]
    public function rules(): Response
    {
        return $this->render('project/rules.html.twig');
    }

    #[Route("/proj/about", name: "about")]
    public function doc(): Response
    {
        return $this->render('project/about.html.twig');
    }

    #[Route('/proj/start', name: 'game_start')]
    public function start(SessionInterface $session): Response
    {
        $game = new BlackJack();
        $game->startGame();
        $session->set('game', $game);
        return $this->redirectToRoute('game_play');
    }

    #[Route('/proj/play', name: 'game_play')]
    public function play(SessionInterface $session): Response
    {
        $game = $session->get('game');
        if (!$game instanceof BlackJack) {
            return $this->redirectToRoute('game');
        }

        return $this->render('game/play.html.twig', [
            'game' => $game,
            'player' => $game->getPlayer(),
            'dealer' => $game->getDealer(),
            'status' => $game->getStatus(),
        ]);
    }

    #[Route('/proj/hit', name: 'game_hit')]
    public function hit(SessionInterface $session): Response
    {
        $game = $session->get('game');
        if ($game instanceof BlackJack && $game->getStatus() === 'ongoing') {
            $game->hit();
            $session->set('game', $game);
        }
        return $this->redirectToRoute('game_play');
    }

    #[Route('/proj/stand', name: 'game_stand')]
    public function stand(SessionInterface $session): Response
    {
        $game = $session->get('game');
        if ($game instanceof BlackJack && $game->getStatus() === 'ongoing') {
            $game->stand();
            $session->set('game', $game);
        }
        return $this->redirectToRoute('game_play');
    }

    #[Route('/proj/reset', name: 'game_reset')]
    public function reset(SessionInterface $session): Response
    {
        $session->remove('game');
        return $this->redirectToRoute('game_start');
    }
}