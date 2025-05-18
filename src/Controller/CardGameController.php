<?php

namespace App\Controller;

use App\Card\DeckOfCards;
use App\Card\CardHand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CardGameController class.
 */
class CardGameController extends AbstractController
{
    /**
     * Displays all session data.
     * @param SessionInterface $session The session for accessing data.
     * @return Response The rendered session data template.
     */
    #[Route('/session', name: 'session_show')]
    public function show(SessionInterface $session): Response
    {
        $allSessionData = $session->all();

        return $this->render('card/session.html.twig', [
            'sessionData' => $allSessionData
        ]);
    }

    /**
     * Clears the session and redirects to session display.
     * @param SessionInterface $session The session to clear.
     * @return Response The redirect response.
     */
    #[Route('/session/delete', name: 'session_delete')]
    public function delete(SessionInterface $session): Response
    {
        $session->clear();
        $this->addFlash('notice', 'Sessionen har raderats.');

        return $this->redirectToRoute('session_show');
    }

    /**
     * Displays the card game homepage.
     * @return Response The rendered homepage template.
     */
    #[Route("/card", name: "card")]
    public function home(): Response
    {
        return $this->render('card/card.html.twig');
    }

    /**
     * Displays a sorted deck of cards.
     * @param SessionInterface $session The session for deck storage.
     * @return Response The rendered deck template.
     */
    #[Route("/card/deck", name: "card_deck")]
    public function cardDeck(SessionInterface $session): Response
    {
        if (!$session->has('deck')) {
            $session->set('deck', new DeckOfCards());
        }

        $deck = $session->get('deck');
        if (!$deck instanceof DeckOfCards) {
            $deck = new DeckOfCards();
            $session->set('deck', $deck);
        }

        $cards = $deck->sortedCards();

        return $this->render('card/deck.html.twig', [
            'cards' => $cards,
        ]);
    }

    /**
     * Shuffles the deck and displays it.
     * @param SessionInterface $session The session for deck storage.
     * @return Response The rendered shuffle template.
     */
    #[Route("/card/deck/shuffle", name: "deck_shuffle")]
    public function shuffleDeck(SessionInterface $session): Response
    {
        $deck = new DeckOfCards();
        $deck->shuffle();
        $session->set('deck', $deck);

        $cards = $deck->getCards();

        return $this->render('card/shuffle.html.twig', [
            'cards' => $cards,
        ]);
    }

    /**
     * Draws a single card from the deck.
     * @param SessionInterface $session The session for deck storage.
     * @return Response The rendered draw template.
     */
    #[Route("/card/deck/draw", name: "deck_draw")]
    public function drawCard(SessionInterface $session): Response
    {
        $deck = $session->get('deck');

        if (!$deck instanceof DeckOfCards) {
            $deck = new DeckOfCards();
            $session->set('deck', $deck);
        }

        $card = $deck->draw();
        $hand = new CardHand();
        if ($card) {
            $hand->addCard($card);
        } else {
            $this->addFlash('warning', 'No cards left!');
        }

        $remaining = $deck->count();
        $session->set('deck', $deck);

        return $this->render('card/draw.html.twig', [
            'hand' => $hand->getCards(),
            'remaining' => $remaining,
            'message' => $card ? null : 'No cards left in the deck.'
        ]);
    }

    /**
     * Renders a form for drawing multiple cards.
     * @return Response The rendered form template.
     */
    #[Route("/card/deck/draw/number", name: "deck_draw_form", methods: ['GET'])]
    public function drawForm(): Response
    {
        return $this->render('card/draw_form.html.twig');
    }

    /**
     * Processes the draw form and redirects to draw cards.
     * @param Request $request The HTTP request containing form data.
     * @return Response The redirect response.
     */
    #[Route("/card/deck/draw/number", name: "deck_draw_init", methods: ['POST'])]
    public function drawInit(Request $request): Response
    {
        $numCards = (int) $request->request->get('num_cards');

        if ($numCards < 1 || $numCards > 52) {
            return $this->redirectToRoute('deck_draw_form');
        }

        return $this->redirectToRoute('deck_draw_number', ['number' => $numCards]);
    }

    /**
     * Draws multiple cards form the deck.
     * @param SessionInterface $session The session for deck storage.
     * @param int $number The number of cards to draw.
     * @return Response The rendered draw number template.
     */
    #[Route("/card/deck/draw/{number<\d+>}", name: "deck_draw_number")]
    public function drawCards(SessionInterface $session, int $number): Response
    {
        if ($number < 1) {
            return $this->render('card/drawNumber.html.twig', [
                'hand' => [],
                'remaining' => 0,
                'message' => 'Invalid number of cards requested.'
            ]);
        }

        $deck = $session->get('deck');

        if (!$deck instanceof DeckOfCards) {
            $deck = new DeckOfCards();
            $session->set('deck', $deck);
        }

        $remainingBeforeDraw = $deck->count();
        if ($number > $remainingBeforeDraw) {
            return $this->render('card/drawNumber.html.twig', [
                'hand' => [],
                'remaining' => $remainingBeforeDraw,
                'message' => 'Not enough cards in the deck to draw ' . $number . '.'
            ]);
        }

        $cards = $deck->drawMany($number);
        $hand = new CardHand();
        foreach ($cards as $card) {
            $hand->addCard($card);
        }
        $remaining = $deck->count();
        $session->set('deck', $deck);

        return $this->render('card/drawNumber.html.twig', [
            'hand' => $hand->getCards(),
            'remaining' => $remaining,
        ]);
    }
}
