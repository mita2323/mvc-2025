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
     * @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $registry;
    /**
     * @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject
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

        $classMetadata = new class (GameSession::class) extends ClassMetadata {
            public function __construct(string $name)
            {
                parent::__construct($name);
            }
        };

        $this->entityManager->method('getClassMetadata')
            ->with(GameSession::class)
            ->willReturnCallback(function (/** @scrutinizer ignore-unused */$className) use ($classMetadata) {
                return $classMetadata;
            });
    }

    /**
     * Tests the constructor.
     */
    public function testConstruct(): void
    {
        // @phpstan-ignore-next-line
        $repository = new GameSessionRepository($this->registry);
        $this->assertInstanceOf(GameSessionRepository::class, $repository);

        $refMethod = new \ReflectionMethod($repository, 'getEntityManager');
        $refMethod->setAccessible(true);
        $entityManager = $refMethod->invoke($repository);

        $this->assertSame($this->entityManager, $entityManager);
    }
}
