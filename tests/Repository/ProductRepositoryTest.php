<?php

namespace App\Tests\Repository;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Test cases for ProductRepository.
 */
class ProductRepositoryTest extends KernelTestCase
{
    /**
     * @var ProductRepository
     */
    private ProductRepository $repository;

    /**
     * Setup the test environment and load sample products.
     * @return void
     */
    protected function setUp(): void
    {
        self::bootKernel();
        /** @var ProductRepository $repository */
        $repository = static::getContainer()->get(ProductRepository::class);
        $this->repository = $repository;

        /** @var ManagerRegistry $doctrine */
        $doctrine = static::getContainer()->get(ManagerRegistry::class);
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
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
     * @return void
     */
    public function testFindByMinimumValue(): void
    {
        $products = $this->repository->findByMinimumValue(50);
        $this->assertCount(2, $products);
        $this->assertGreaterThanOrEqual(50, $products[0]->getValue());
    }

    /**
     * Test native SQL-based minimum value filter.
     * @return void
     */
    public function testFindByMinimumValue2(): void
    {
        $rawResults = $this->repository->findByMinimumValue2(50);
        $this->assertCount(2, $rawResults);
        $this->assertGreaterThanOrEqual(50, $rawResults[0]['value']);
    }
}
