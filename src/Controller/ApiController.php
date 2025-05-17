<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles the API overview page.
 */
class ApiController extends AbstractController
{
    /**
     * Renders the API overview page with a list of application routes.
     */
    #[Route('/api', name: 'api')]
    public function api(): Response
    {
        $routes = [
            [
                'namn' => 'Home',
                'route' => '/',
                'link' => 'home',
                'metod' => 'GET',
                'beskrivning' => 'Hemsidan med information om mig'
            ],
            [
                'namn' => 'About',
                'route' => '/about',
                'link' => 'about',
                'metod' => 'GET',
                'beskrivning' => 'About sidan med information om kursen'
            ],
            [
                'namn' => 'Report',
                'route' => '/report',
                'link' => 'report',
                'metod' => 'GET',
                'beskrivning' => 'Report sidan med alla redovisningstexter'
            ],
            [
                'namn' => 'Lucky Number',
                'route' => '/lucky',
                'link' => 'lucky',
                'metod' => 'GET',
                'beskrivning' => 'Få ett "Lucky number" sidan'
            ],
            [
                'namn' => 'Landingssida',
                'route' => '/card',
                'link' => 'card',
                'metod' => 'GET',
                'beskrivning' => 'Landningssida som visar samtliga undersidor och innehåller information om uppgiften.'
            ],
            [
                'namn' => 'Visa kortlek',
                'route' => '/card/deck',
                'link' => 'card_deck',
                'metod' => 'GET',
                'beskrivning' => 'Returnerar hela kortleken sorterad efter färg och värde.'
            ],
            [
                'namn' => 'Blanda kortlek',
                'route' => '/card/deck/shuffle',
                'link' => 'deck_shuffle',
                'metod' => 'GET',
                'beskrivning' => 'Blandar kortleken.'
            ],
            [
                'namn' => 'Dra ett kort',
                'route' => '/card/deck/draw',
                'link' => 'deck_draw',
                'metod' => 'GET',
                'beskrivning' => 'Drar ett kort från kortleken.'
            ],
            [
                'namn' => 'Dra flera kort',
                'route' => '/card/deck/draw/number',
                'link' => 'deck_draw_form',
                'metod' => 'GET',
                'beskrivning' => 'Drar ett angivet antal kort från kortleken.'
            ],
            [
                'namn' => 'JSON kortlek sorterad',
                'route' => '/api/deck',
                'link' => 'api_deck_get',
                'metod' => 'GET',
                'beskrivning' => 'Returnerar en JSON struktur med hela kortleken sorterad efter färg och värde.'
            ],
            [
                'namn' => 'JSON kortlek blandat',
                'route' => '/api/deck/shuffle',
                'link' => 'api_deck_shuffle',
                'metod' => 'POST',
                'beskrivning' => 'Blandar kortleken och returnerar en JSON-struktur.'
            ],
            [
                'namn' => 'JSON drar ett kort',
                'route' => '/api/deck/draw',
                'link' => 'api_deck_draw',
                'metod' => 'POST',
                'beskrivning' => 'Drar ett kort från kortleken och returnerar en JSON-struktur.'
            ],
            [
                'namn' => 'JSON drar :number kort',
                'route' => '/api/deck/draw/number',
                'link' => 'api_deck_draw_number_form',
                'metod' => 'GET',
                'beskrivning' => 'Drar ett angivet antal kort från kortleken och returnerar en JSON-struktur med de dragna korten.'
            ],
            [
                'namn' => 'Visa session',
                'route' => '/session',
                'link' => 'session_show',
                'metod' => 'GET',
                'beskrivning' => 'Skriver ut innehållet i sessionen.'
            ],
            [
                'namn' => 'Radera session',
                'route' => '/session/delete',
                'link' => 'session_delete',
                'metod' => 'GET',
                'beskrivning' => 'Raderar innehåller i sessionen.'
            ],
            [
                'namn' => 'JSON Spelstatus',
                'route' => '/api/game',
                'link' => 'api_game_status',
                'metod' => 'GET',
                'beskrivning' => 'Visar upp den aktuella ställningen för spelet i en JSON struktur.'
            ],
            [
                'namn' => 'Böcker i biblioteket',
                'route' => '/api/library/books',
                'link' => 'api_library_books',
                'metod' => 'GET',
                'beskrivning' => 'Visar upp samtliga böcker i biblioteket.'
            ],
            [
                'namn' => 'Bok efter ISBN',
                'route' => '/api/library/book/{isbn}',
                'link' => 'library_book_by_isbn',
                'metod' => 'GET',
                'beskrivning' => 'Visar en specifik bok baserat på dess ISBN-nummer.'
            ]
        ];

        return $this->render('api.html.twig', ['routes' => $routes]);
    }
}