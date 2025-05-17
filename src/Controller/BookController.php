<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Manages book-related operations for the library application.
 */
final class BookController extends AbstractController
{
    /**
     * Displays the library index page.
     * @return Response The rendered index template.
     */
    #[Route('/library', name: 'library')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }

    /**
     * Creates a new book entry.
     * @param Request $request The HTTP request containing form data.
     * @param ManagerRegistry $doctrine The Doctrine registry for database operations.
     * @return Response The form template or redirect response.
     * @throws \Exception If required fields are empty.
     */
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

    /**
     * Displays all books in the library.
     * @param BookRepository $bookRepository The repository for the book queries.
     * @return Response The rendered book list template.
     */
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

    /**
     * Displays a single book by ID.
     * @param BookRepository $bookRepository The repository for book queries.
     * @param int $id The ID of the book to display.
     * @return Response The rendered book details template.
     */
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

    /**
     * Updates an existing book.
     * @param Request $request The HTTP request containing form data.
     * @param ManagerRegistry $doctrine The Doctrine registry for database operations.
     * @param int $id The ID of the book to update.
     * @return Response  The form template or redirect response.
     * @throws \Exception If required fields are empty or image URL is invalid.
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If book is not found.
     */
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

    /**
     * Deletes a book by ID.
     * @param ManagerRegistry $doctrine The Doctrine registry for database operations.
     * @param int $id The ID of the book to delete.
     * @return Response The redirect response.
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If book is not found.
     */
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
