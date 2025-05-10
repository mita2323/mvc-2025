<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookController extends AbstractController
{
    #[Route('/library', name: 'library')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }

    #[Route('/library/create', name: 'book_create')]
    public function createBook(
        Request $request, ManagerRegistry $doctrine
    ): Response {
        if ($request->isMethod('POST')) {
            $book = new Book();
            $book->setTitle($request->request->get('title'));
            $book->setIsbn($request->request->get('isbn'));
            $book->setAuthor($request->request->get('author'));

            if ($imageFile = $request->files->get('image')) {
                $newFilename = uniqid('book_', true) . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir') . '/assets/images', $newFilename);
                $book->setImage($newFilename);
            }

            if ($book->getTitle() && $book->getIsbn() && $book->getAuthor()) {
                $entityManager = $doctrine->getManager();
                $entityManager->persist($book);
                $entityManager->flush();
                return $this->redirectToRoute('book_show_all');
            }

            $this->addFlash('error', 'All fields except image are required.');
        }

        return $this->render('book/create.html.twig');
    }

    
    #[Route('/library/show', name: 'book_show_all')]
    public function showAllBooks(
        BookRepository $bookRepository
    ): Response {
        $books = $bookRepository
            ->findAll();

        return $this->render('book/show_all.html.twig', [
            'books' => $books,
        ]);
    }
}
