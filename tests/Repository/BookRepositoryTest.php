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
    /**
     * @var BookRepository
     */
    private BookRepository $repository;

    /**
     * Set up test environment before each test method.
     * @return void
     */
    protected function setUp(): void
    {
        self::bootKernel();
        /** @var BookRepository $repository */
        $repository = static::getContainer()->get(BookRepository::class);
        $this->repository = $repository;
    }

    /**
     * Test the repository constructor.
     * @return void
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(BookRepository::class, $this->repository);
    }
}
