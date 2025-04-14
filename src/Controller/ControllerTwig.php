<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ControllerTwig extends AbstractController
{
    #[Route("/", name: "home")]
    public function home(): Response
    {
        return $this->render('home.html.twig');
    }

    #[Route("/about", name: "about")]
    public function about(): Response
    {
        return $this->render('about.html.twig');
    }

    #[Route("/report", name: "report")]
    public function report(): Response
    {
        return $this->render('report.html.twig');
    }

    #[Route("/lucky", name: "lucky")]
    public function lucky(): Response
    {
        $luckyNumber = random_int(1, 100);
        $images = [
            'lucky1.jpg',
            'lucky2.jpg',
            'lucky3.jpg',
            'lucky4.jpg',
            'lucky5.jpg'
        ];

        $randomImage = $images[array_rand($images)];

        return $this->render('lucky.html.twig', [
            'luckyNumber' => $luckyNumber,
            'randomImage' => $randomImage
        ]);
    }

    /*
    #[Route('/api', name: 'api')]
    public function api(): Response
    {
        $routes = [
            ['name' => 'Home', 'route' => '/', 'description' => 'Hemsidan med information om mig'],
            ['name' => 'About', 'route' => '/about', 'description' => 'About sidan med information om kursen'],
            ['name' => 'Report', 'route' => '/report', 'description' => 'Report sidan med alla redovisningstexter'],
            ['name' => 'Lucky Number', 'route' => '/lucky', 'description' => 'Få ett "Lucky number" sidan'],
        ];

        return $this->render('api.html.twig', [
            'routes' => $routes
        ]);
    }*/

    #[Route('/api/quote', name: 'api_quote')]
    public function quote(): Response
    {
        $quotes = [
            ["quote" => "The only limit to our realization of tomorrow is our doubts of today.", "author" => "Franklin D. Roosevelt"],
            ["quote" => "Do what you feel in your heart to be right, for you will be criticized anyway.", "author" => "Eleanor Roosevelt"],
            ["quote" => "Believe you can and you are halfway there.", "author" => "Theodore Roosevelt"],
            ["quote" => "Happiness depends upon ourselves.", "author" => "Aristotle"],
            ["quote" => "You must be the change you wish to see in the world.", "author" => "Mahatma Gandhi"],
            ["quote" => "It is never too late to be what you might have been.", "author" => "George Eliot"],
            ["quote" => "Success is not final, failure is not fatal: it is the courage to continue that counts.", "author" => "Winston Churchill"],
        ];

        $randomQuote = $quotes[array_rand($quotes)];

        $data = [
            "quote" => $randomQuote["quote"],
            "author" => $randomQuote["author"],
            "date" => date("Y-m-d"),
            "timestamp" => date("H:i:s")
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

        return $response;
    }
}
