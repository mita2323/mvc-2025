<?php

namespace App\Tests\Repository;

use App\Entity\GameSession;
use App\Repository\GameSessionRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for GameSessionRepository.
 */
class GameSessionRepositoryTest extends TestCase
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
            ->with(GameSession::class)
            ->willReturn($this->entityManager);

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->name = GameSession::class;
        $this->entityManager->method('getClassMetadata')
            ->with(GameSession::class)
            ->willReturn($classMetadata);
    }

    /**
     * Tests the constructor.
     */
    public function testConstruct(): void
    {
        $repository = new GameSessionRepository($this->registry);
        $this->assertInstanceOf(GameSessionRepository::class, $repository);
        $this->assertSame($this->entityManager, $repository->getEntityManager());
    }
}
