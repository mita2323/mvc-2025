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
            ->with(CardStat::class)
            ->willReturn($this->entityManager);

            $classMetadata = new class(CardStat::class) extends ClassMetadata {
                public string $name;
                public function __construct(string $name)
                {
                    parent::__construct($name);
                    $this->name = $name;
                }
            };

            $this->entityManager->method('getClassMetadata')
                ->with(CardStat::class)
                ->willReturnCallback(function ($className) use ($classMetadata) {
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
        $repository = new CardStatRepository($this->registry);
        $this->assertInstanceOf(CardStatRepository::class, $repository);
        $this->assertSame($this->entityManager, $repository->getEntityManager());
    }
}