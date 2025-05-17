<?php

namespace App\Tests\Repository;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Test cases for ProductRepository.
 */
class ProductRepositoryTest extends KernelTestCase
{
    private ProductRepository $repository;

    /**
     * Setup the test environment and load sample products.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = static::getContainer()->get(ProductRepository::class);

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->createQuery('DELETE FROM App\Entity\Product p')->execute();

        $product1 = (new Product())->setName('Cheap Product')->setValue(10);
        $product2 = (new Product())->setName('Mid Product')->setValue(50);
        $product3 = (new Product())->setName('Expensive Product')->setValue(100);

        $entityManager->persist($product1);
        $entityManager->persist($product2);
        $entityManager->persist($product3);
        $entityManager->flush();
    }

    /**
     * Test DQL-based minimum value filter.
     */
    public function testFindByMinimumValue(): void
    {
        $products = $this->repository->findByMinimumValue(50);
        $this->assertCount(2, $products);
        $this->assertGreaterThanOrEqual(50, $products[0]->getValue());
    }

    /**
     * Test native SQL-based minimum value filter.
     */
    public function testFindByMinimumValue2(): void
    {
        $rawResults = $this->repository->findByMinimumValue2(50);
        $this->assertIsArray($rawResults);
        $this->assertCount(2, $rawResults);
        $this->assertGreaterThanOrEqual(50, $rawResults[0]['value']);
    }
}