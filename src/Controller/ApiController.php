<?php

namespace App\Controller;

use App\Card\DeckOfCards;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'api')]
    public function api(): Response
    {
        $routes = [
            [
                'namn' => 'Home',
                'route' => '/',
                'metod' => 'GET',
                'beskrivning' => 'Hemsidan med information om mig'
            ],
            [
                'namn' => 'About',
                'route' => '/about',
                'metod' => 'GET',
                'beskrivning' => 'About sidan med information om kursen'
            ],
            [
                'namn' => 'Report',
                'route' => '/report',
                'metod' => 'GET',
                'beskrivning' => 'Report sidan med alla redovisningstexter'
            ],
            [
                'namn' => 'Lucky Number',
                'route' => '/lucky',
                'metod' => 'GET',
                'beskrivning' => 'Få ett "Lucky number" sidan'
            ],
            [
                'namn' => 'Landingssida',
                'route' => '/card',
                'metod' => 'GET',
                'beskrivning' => 'Landningssida som visar samtliga undersidor och innehåller information om uppgiften.'
            ],
            [
                'namn' => 'Visa kortlek',
                'route' => '/card/deck',
                'metod' => 'GET',
                'beskrivning' => 'Returnerar hela kortleken sorterad efter färg och värde.'
            ],
            [
                'namn' => 'Blanda kortlek',
                'route' => '/card/deck/shuffle',
                'metod' => 'GET',
                'beskrivning' => 'Blandar kortleken.'
            ],
            [
                'namn' => 'Dra ett kort',
                'route' => '/card/deck/draw',
                'metod' => 'GET',
                'beskrivning' => 'Drar ett kort från kortleken.'
            ],
            [
                'namn' => 'Dra flera kort',
                'route' => '/card/deck/draw/number',
                'metod' => 'GET',
                'beskrivning' => 'Drar ett angivet antal kort från kortleken.'
            ],
            [
                'namn' => 'JSON kortlek sorterad',
                'route' => '/api/deck',
                'metod' => 'GET',
                'beskrivning' => 'Returnerar en JSON struktur med hela kortleken sorterad efter färg och värde.'
            ],
            [
                'namn' => 'JSON kortlek blandat',
                'route' => '/api/deck/shuffle',
                'metod' => 'POST',
                'beskrivning' => 'Blandar kortleken och returnerar en JSON-struktur.'
            ],
            [
                'namn' => 'JSON drar ett kort',
                'route' => '/api/deck/draw',
                'metod' => 'POST',
                'beskrivning' => 'Drar ett kort från kortleken och returnerar en JSON-struktur.'
            ],
            [
                'namn' => 'JSON drar :number kort',
                'route' => '/api/deck/draw/number',
                'metod' => 'GET',
                'beskrivning' => 'Drar ett angivet antal kort från kortleken och returnerar en JSON-struktur med de dragna korten.'
            ],
            [
                'namn' => 'Visa session',
                'route' => '/session',
                'metod' => 'GET',
                'beskrivning' => 'Skriver ut innehållet i sessionen.'
            ],
            [
                'namn' => 'Radera session',
                'route' => '/session/delete',
                'metod' => 'GET',
                'beskrivning' => 'Raderar innehåller i sessionen.'
            ],
        ];

        return $this->render('api.html.twig', [
            'routes' => $routes,
        ]);
    }

    #[Route('/api/deck', name: 'api_deck', methods: ['GET'])]
    public function deck(): JsonResponse
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

    #[Route('/api/deck/shuffle', name: 'api_deck_shuffle', methods: ['POST'])]
    public function shuffle(SessionInterface $session): JsonResponse
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

    #[Route('/api/deck/draw', name: 'api_deck_draw', methods: ['POST'])]
    public function draw(SessionInterface $session): JsonResponse
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

    #[Route('/api/deck/draw/number', name: 'api_deck_draw_number_form', methods: ['GET'])]
    public function drawNumberForm(): Response
    {
        return $this->render('card/api_draw_number.html.twig');
    }

    #[Route('/api/deck/draw/{number<\d+>}', name: 'api_deck_draw_number', methods: ['POST'])]
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
