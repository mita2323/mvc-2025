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

            $classMetadata = new class(GameSession::class) extends ClassMetadata {
                public string $name;

                public function __construct(string $name)
                {
                    parent::__construct($name);
                    $this->name = $name;
                }
            };

            $this->entityManager->method('getClassMetadata')
                ->with(GameSession::class)
                ->willReturnCallback(function (/** @scrutinizer ignore-unused */$className) use ($classMetadata) {
                    if (!$classMetadata instanceof ClassMetadata) {
                        throw new \LogicException('Expected instance of ClassMetadata');
                    }
                    return $classMetadata;
                });
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
