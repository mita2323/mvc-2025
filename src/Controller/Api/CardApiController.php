<?php

namespace App\Controller\Api;

use App\Card\DeckOfCards;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles API requests for deck operations in the card game.
 */
#[Route('/api/deck', name: 'api_deck_')]
class CardApiController extends AbstractController
{
    /**
     * Retrieves a sorted deck of cards.
     * @return JsonResponse The sorted deck as JSON.
     */
    #[Route('', name: 'get', methods: ['GET'])]
    public function getSortedDeck(): JsonResponse
    {
        $deck = new DeckOfCards();
        $sortedCards = $deck->sortedCards();

        $jsonData = array_map(function ($card) {
            return [
                'suit' => $card->getSuit(),
                'value' => $card->getValue(),
            ];
        }, $sortedCards);

        return new JsonResponse($jsonData);
    }

    /**
     * Shuffles the deck and stores it in the session.
     * @param SessionInterface $session The session for storing the deck.
     * @return JsonResponse The shuffled deck as JSON.
     */
    #[Route('/shuffle', name: 'shuffle', methods: ['POST'])]
    public function shuffleDeck(SessionInterface $session): JsonResponse
    {
        $deck = new DeckOfCards();
        $deck->shuffle();
        $session->set('deck', $deck);

        $shuffledCards = $deck->getCards();

        $jsonData = array_map(function ($card) {
            return [
                'suit' => $card->getSuit(),
                'value' => $card->getValue(),
            ];
        }, $shuffledCards);

        return new JsonResponse($jsonData);
    }

    /**
     * Draws a single card from the deck.
     * @param SessionInterface $session The session for accessing the deck.
     * @return JsonResponse The drawn card and remaining count, or an error.
     */
    #[Route('/draw', name: 'draw', methods: ['POST'])]
    public function drawCard(SessionInterface $session): JsonResponse
    {
        $deck = $session->get('deck', new DeckOfCards());
        if (!$deck instanceof DeckOfCards) {
            $deck = new DeckOfCards();
        }

        if ($deck->count() === 0) {
            return new JsonResponse(['error' => 'No cards left in the deck'], 400);
        }

        $drawnCard = $deck->draw();
        $session->set('deck', $deck);

        if ($drawnCard === null) {
            return new JsonResponse(['error' => 'Failed to draw a card'], 500);
        }

        $jsonData = [
            'cards' => [
                [
                    'suit' => $drawnCard->getSuit(),
                    'value' => $drawnCard->getValue(),
                ]
            ],
            'remaining' => $deck->count()
        ];

        return new JsonResponse($jsonData);
    }

    /**
     * Renders a form for drawing multiple cards.
     * @return Response The rendered form template.
     */
    #[Route('/draw/number', name: 'draw_number_form', methods: ['GET'])]
    public function drawNumberForm(): Response
    {
        return $this->render('card/api_draw_number.html.twig');
    }

    /**
     * Draws multiple cards form the deck.
     * @param int $number The number of cards to draw.
     * @param SessionInterface $session The session for accessing the deck.
     * @return JsonResponse The drawn cards and remaining count, or an error.
     */
    #[Route('/draw/{number<\d+>}', name: 'draw_number', methods: ['POST'])]
    public function drawNumber(int $number, SessionInterface $session): JsonResponse
    {
        $deck = $session->get('deck', new DeckOfCards());
        if (!$deck instanceof DeckOfCards) {
            $deck = new DeckOfCards();
        }

        $remainingCards = $deck->count();
        if ($number <= 0) {
            return new JsonResponse(['error' => 'Number of cards must be greater than zero'], 400);
        }
        if ($number > $remainingCards) {
            return new JsonResponse(['error' => 'Not enough cards left in the deck'], 400);
        }

        $drawnCards = $deck->drawMany($number);
        $session->set('deck', $deck);

        $jsonData = [
            'cards' => array_map(function ($card) {
                return [
                    'suit' => $card->getSuit(),
                    'value' => $card->getValue(),
                ];
            }, $drawnCards),
            'remaining' => $deck->count()
        ];

        return new JsonResponse($jsonData);
    }
}