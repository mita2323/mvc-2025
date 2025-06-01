<?php

namespace App\Controller;

use App\Project\BlackJack;
use App\Entity\Player as PlayerEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ProjectController class
 */
class ProjectController extends AbstractController
{
    /**
     * @var EntityManagerInterface The Doctrine Entity Manager for database operations.
     */
    private EntityManagerInterface $entityManager;

    /**
     * Constructor for ProjectController.
     * @param EntityManagerInterface $entityManager The Doctrine Entity Manager.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     *
     */
    private function renderPlayPage(BlackJack $game): Response
    {
        return $this->render('project/play.html.twig', [
            'game' => $game,
            'player' => $game->getPlayer(),
            'dealer' => $game->getDealer(),
            'status' => $game->getStatus(),
            'activeHandIndex' => $game->getActiveHandIndex(),
        ]);
    }

    /**
     * Renders the main project landing page.
     * @param SessionInterface $session The current session.
     * @return Response The HTTP response.
     */
    #[Route("/proj", name: "project")]
    public function project(SessionInterface $session): Response
    {
        $session->remove('player_name');
        $session->remove('num_hands');
        $session->remove('game_state');

        return $this->render('project/game.html.twig', [
            'status' => 'not_started',
        ]);
    }

    /**
     * Renders the Blackjack game rules page.
     * @return Response The HTTP response.
     */
    #[Route("/proj/rules", name: "rules")]
    public function rules(): Response
    {
        return $this->render('project/rules.html.twig');
    }

    /**
     * Renders the "About" page for the project.
     * @return Response The HTTP response.
     */
    #[Route("/proj/about", name: "about")]
    public function about(): Response
    {
        return $this->render('project/about.html.twig');
    }

    /**
     * Handles the betting phase of the Blackjack game.
     * @param Request $request The HTTP request.
     * @param SessionInterface $session The current session.
     * @return Response The HTTP response.
     */
    #[Route('/proj/bet', name: 'blackjack_bet', methods: ['GET', 'POST'])]
    public function bet(Request $request, SessionInterface $session): Response
    {
        $playerName = $request->request->get('player_name');
        if (!is_string($playerName) || trim($playerName) === '') {
            $playerName = $session->get('player_name');
            if (!is_string($playerName) || trim($playerName) === '') {
                $this->addFlash('error', 'Please enter your name to start the game.');
                return $this->redirectToRoute('project');
            }
        }

        $numHands = $request->request->get('num_hands');

        if (!is_numeric($numHands)) {
            $numHands = $session->get('num_hands', 1);
            if (!is_numeric($numHands)) {
                $numHands = 1;
            }
        }
        $numHands = (int) $numHands;

        if (empty($playerName)) {
            $this->addFlash('error', 'Please enter your name to start the game.');
            return $this->redirectToRoute('project');
        }

        if ($request->isMethod('POST')) {
            if ($numHands < 1 || $numHands > 3) {
                $this->addFlash('error', 'Number of hands must be between 1 and 3.');
                return $this->redirectToRoute('project');
            }
        }

        $session->set('player_name', $playerName);
        $session->set('num_hands', $numHands);

        $playerNameRaw = $session->get('player_name');
        if (!is_string($playerNameRaw)) {
            $playerName = '';
        } else {
            $playerName = $playerNameRaw;
        }

        $game = new BlackJack($playerName, $this->entityManager);

        $balance = $game->getPlayer()->getBalance();

        if ($balance <= 0) {
            $this->addFlash('error', 'You have no money left! Please reset your balance to play again.');
        }

        return $this->render('project/bet.html.twig', [
            'player_name' => $playerName,
            'num_hands' => $numHands,
            'balance' => $balance,
        ]);
    }

    /**
     * Starts a new Blackjack game round.
     * @param Request $request The HTTP request.
     * @param SessionInterface $session The current session.
     * @return Response The HTTP response.
     */
    #[Route('/proj/start', name: 'blackjack_start', methods: ['POST'])]
    public function start(Request $request, SessionInterface $session): Response
    {
        $playerName = $session->get('player_name');
        if (!is_string($playerName) || trim($playerName) === '') {
            return $this->redirectToRoute('project');
        }

        $numHandsRaw = $session->get('num_hands');
        if (!is_numeric($numHandsRaw)) {
            return $this->redirectToRoute('project');
        }
        $numHands = (int)$numHandsRaw;

        $bet = $request->request->get('bet');
        if (!is_numeric($bet)) {
            $bet = 10;
        }
        $bet = (int) $bet;

        if (empty($playerName)) {
            return $this->redirectToRoute('project');
        }

        $game = new BlackJack($playerName, $this->entityManager);
        $playerBalance = $game->getPlayer()->getBalance();

        if ($bet <= 0 || ($bet * $numHands) > $playerBalance) {
            $this->addFlash('error', 'Invalid bet amount or insufficient funds. Please enter a valid bet.');
            return $this->redirectToRoute('blackjack_bet');
        }

        if ($game->startGame($numHands, $bet)) {
            $session->set('game_state', $game->getState());
            return $this->renderPlayPage($game);
        }

        $this->addFlash('error', 'Could not start game. Please check your bet and balance.');
        return $this->redirectToRoute('blackjack_bet');
    }

    /**
     * Renders the Blackjack gameplay page.
     * Restores the game state from the session.
     * @param SessionInterface $session The current session.
     * @return Response The HTTP response.
     */
    #[Route('/proj/play', name: 'blackjack_play')]
    public function play(SessionInterface $session): Response
    {
        $gameState = $session->get('game_state');
        if (!is_array($gameState)) {
            return $this->redirectToRoute('project');
        }

        $playerName = $session->get('player_name');
        if (!is_string($playerName) || trim($playerName) === '') {
            return $this->redirectToRoute('project');
        }

        $game = new BlackJack($playerName, $this->entityManager);
        $game->reset($gameState);

        return $this->renderPlayPage($game);
}

    /**
     * Handles the "Hit" action for a specific player hand.
     * @param int $handIndex The index of the hand to hit.
     * @param SessionInterface $session The current session.
     * @return Response The HTTP response.
     */
    #[Route('/proj/hit/{handIndex}', name: 'blackjack_hit')]
    public function hit(int $handIndex, SessionInterface $session): Response
    {
        $gameState = $session->get('game_state');
        if (!is_array($gameState)) {
            return $this->redirectToRoute('project');
        }

        $playerName = $session->get('player_name');
        if (!is_string($playerName) || trim($playerName) === '') {
            return $this->redirectToRoute('project');
        }

        $game = new BlackJack($playerName, $this->entityManager);
        $game->reset($gameState);

        if ($game->getStatus() === 'ongoing') {
            $game->hit($handIndex);
            $session->set('game_state', $game->getState());
        }

        return $this->renderPlayPage($game);
    }

    /**
     * Handles the "Stand" action for a specific player hand.
     * @param int $handIndex The index of the hand to stand on.
     * @param SessionInterface $session The current session.
     * @return Response The HTTP response.
     */
    #[Route('/proj/stand/{handIndex}', name: 'blackjack_stand', methods: ['GET'])]
    public function stand(int $handIndex, SessionInterface $session): Response
    {
        $gameState = $session->get('game_state');
        if (!is_array($gameState)) {
            return $this->redirectToRoute('project');
        }

        $playerName = $session->get('player_name');
        if (!is_string($playerName) || trim($playerName) === '') {
            return $this->redirectToRoute('project');
        }

        $game = new BlackJack($playerName, $this->entityManager);
        $game->reset($gameState);

        if ($game->getStatus() === 'ongoing') {
            $game->stand($handIndex);
            $session->set('game_state', $game->getState());
        }

        return $this->renderPlayPage($game);
    }

    /**
     * Handles the "Double Down" action for a specific player hand.
     * @param int $handIndex The index of the hand to double down on.
     * @param SessionInterface $session The current session.
     * @return Response The HTTP response.
     */
    #[Route('/proj/double/{handIndex}', name: 'blackjack_double', methods: ['GET'])]
    public function double(int $handIndex, SessionInterface $session): Response
    {
        $gameState = $session->get('game_state');
        if (!is_array($gameState)) {
            return $this->redirectToRoute('project');
        }

        $playerName = $session->get('player_name');
        if (!is_string($playerName) || trim($playerName) === '') {
            return $this->redirectToRoute('project');
        }

        $game = new BlackJack($playerName, $this->entityManager);
        $game->reset($gameState);

        if ($game->getStatus() === 'ongoing') {
            $game->doubleDown($handIndex);
            $session->set('game_state', $game->getState());
        }

        return $this->renderPlayPage($game);
    }

    /**
     * Handles the "Split" action for a specific player hand.
     * @param int $handIndex The index of the hand to split.
     * @param SessionInterface $session The current session.
     * @return Response The HTTP response.
     */
    #[Route('/proj/split/{handIndex}', name: 'blackjack_split')]
    public function split(int $handIndex, SessionInterface $session): Response
    {
        $gameState = $session->get('game_state');
        if (!is_array($gameState)) {
            return $this->redirectToRoute('project');
        }

        $playerName = $session->get('player_name');
        if (!is_string($playerName) || trim($playerName) === '') {
            return $this->redirectToRoute('project');
        }

        $game = new BlackJack($playerName, $this->entityManager);
        $game->reset($gameState);

        if ($game->getStatus() === 'ongoing') {
            $game->split($handIndex);
            $session->set('game_state', $game->getState());
        }

        return $this->renderPlayPage($game);
    }

    /**
     * Resets the player's balance to the initial amount (1000) in the database
     * and clears all session data for a fresh start.
     * @param SessionInterface $session The current session.
     * @return Response The HTTP response.
     */
    #[Route('/proj/reset', name: 'blackjack_reset')]
    public function reset(SessionInterface $session): Response
    {
        $playerName = $session->get('player_name');
        if (!is_string($playerName) || trim($playerName) === '') {
            return $this->redirectToRoute('project');
        }

        if ($playerName) {
            $playerEntity = $this->entityManager->getRepository(PlayerEntity::class)->findOneBy(['name' => $playerName]);
            if ($playerEntity) {
                $playerEntity->setBalance(1000);
                $this->entityManager->persist($playerEntity);
                $this->entityManager->flush();
            }
        }
        $session->clear();
        $this->addFlash('success', 'Your balance has been reset to 1000');
        return $this->redirectToRoute('project');
    }
}
