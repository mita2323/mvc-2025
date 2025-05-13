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
    public function createBook(Request $request, ManagerRegistry $doctrine): Response
    {
        if ($request->isMethod('POST')) {
            $entityManager = $doctrine->getManager();

            $book = new Book();

            $title = (string) $request->request->get('title');
            $isbn = (string) $request->request->get('isbn');
            $author = (string) $request->request->get('author');
            $imageUrl = $request->request->get('image_url', null);

            $book->setTitle($title);
            $book->setIsbn($isbn);
            $book->setAuthor($author);
            $book->setImageUrl($imageUrl ? (string) $imageUrl : null);

            if (empty($title) || empty($isbn) || empty($author)) {
                throw new \Exception('Title, ISBN, and author are required');
            }

            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('book_show_all');

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

    #[Route('/library/show/{id}', name: 'book_show')]
    public function showBookById(
        BookRepository $bookRepository,
        int $id
    ): Response {
        $book = $bookRepository
            ->find($id);

        return $this->render('book/show_one.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/library/update/{id}', name: 'book_update')]
    public function updateBook(
        Request $request,
        ManagerRegistry $doctrine,
        int $id
    ): Response {
        $entityManager = $doctrine->getManager();
        $book = $entityManager->getRepository(Book::class)->find($id);

        if (!$book) {
            throw $this->createNotFoundException(
                'No book found for id '.$id
            );
        }

        if ($request->isMethod('POST')) {
            $title = (string) $request->request->get('title');
            $isbn = (string) $request->request->get('isbn');
            $author = (string) $request->request->get('author');
            $imageUrl = $request->request->get('image_url', null);

            if ($imageUrl && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                throw new \Exception('Invalid image URL.');
            }

            if (empty($title) || empty($isbn) || empty($author)) {
                throw new \Exception('Title, ISBN, and author are required.');
            }

            $book->setTitle($title);
            $book->setIsbn($isbn);
            $book->setAuthor($author);
            $book->setImageUrl($imageUrl ? (string) $imageUrl : null);

            $entityManager->flush();

            return $this->redirectToRoute('book_show_all');
        }

        return $this->render('book/update.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/library/delete/{id}', name: 'book_delete_by_id', methods: ['GET'])]
    public function deleteBookById(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $book = $entityManager->getRepository(Book::class)->find($id);

        if (!$book) {
            throw $this->createNotFoundException('No book found for id ' . $id);
        }

        $entityManager->remove($book);
        $entityManager->flush();

        return $this->redirectToRoute('book_show_all');
    }
}
