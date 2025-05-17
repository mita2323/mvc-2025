<?php

namespace App\Controller\Api;

use App\Entity\Book;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles API requests for the library application.
 */
#[Route('/api/library', name: 'api_library_')]
class LibraryApiController extends AbstractController
{
    /**
     * Retrieves all books in the library.
     * @param ManagerRegistry $doctrine The Doctrine registry for database operations.
     * @return JsonResponse The book list or error message.
     */
    #[Route('/books', name: 'books', methods: ['GET'])]
    public function getAllBooks(ManagerRegistry $doctrine): JsonResponse
    {
        $books = $doctrine->getRepository(Book::class)->findAll();

        if (empty($books)) {
            return new JsonResponse([
                'message' => 'No books in the library.'
            ], 404);
        }

        $jsonData = array_map(function ($book) {
            return [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                'isbn' => $book->getIsbn(),
                'author' => $book->getAuthor(),
                'imageUrl' => $book->getImageUrl(),
            ];
        }, $books);

        return new JsonResponse($jsonData);
    }

    /**
     * Retrieves a book by its ISBN.
     * @param string $isbn The ISBN of the book to retrieve.
     * @param ManagerRegistry $doctrine The Doctrine registry for database operations.
     * @return JsonResponse The book details or error message.
     */
    #[Route('/book/{isbn}', name: 'book_by_isbn', methods: ['GET'])]
    public function getBookByIsbn(string $isbn, ManagerRegistry $doctrine): JsonResponse
    {
        $book = $doctrine->getRepository(Book::class)->findOneBy(['isbn' => $isbn]);

        if (!$book) {
            return new JsonResponse([
                'message' => 'No book found for ISBN ' . $isbn
            ], 404);
        }

        $jsonData = [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'isbn' => $book->getIsbn(),
            'author' => $book->getAuthor(),
            'imageUrl' => $book->getImageUrl(),
        ];

        return new JsonResponse($jsonData);
    }
}