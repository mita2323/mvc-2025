<?php

namespace App\Tests\Repository;

use App\Entity\Book;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Test cases for BookRepository.
 */
class BookRepositoryTest extends KernelTestCase
{
    private BookRepository $repository;

    /**
     * Set up test environment before each test method.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = static::getContainer()->get(BookRepository::class);
    }

    /**
     * Test the repository constructor.
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(BookRepository::class, $this->repository);
    }
}