<?php

namespace App\Tests\Repository;

use App\Entity\CardStat;
use App\Repository\CardStatRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CardStatRepository.
 */
class CardStatRepositoryTest extends TestCase
{
    /**
     * @var ManagerRegistry
     */
    private $registry;
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Sets up the test environment.
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->registry->method('getManagerForClass')
            ->with(CardStat::class)
            ->willReturn($this->entityManager);

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->name = CardStat::class;
        $this->entityManager->method('getClassMetadata')
            ->with(CardStat::class)
            ->willReturn($classMetadata);
    }

    /**
     * Tests the constructor.
     */
    public function testConstruct(): void
    {
        $repository = new CardStatRepository($this->registry);
        $this->assertInstanceOf(CardStatRepository::class, $repository);
        $this->assertSame($this->entityManager, $repository->getEntityManager());
    }
}