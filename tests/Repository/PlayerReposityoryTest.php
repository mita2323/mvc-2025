<?php

namespace App\Tests\Repository;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for PlayerRepository.
 */
class PlayerRepositoryTest extends TestCase
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
            ->with(Player::class)
            ->willReturn($this->entityManager);

        $classMetadata = new class(Player::class) extends ClassMetadata {
            public string $name;

            public function __construct(string $name)
            {
                parent::__construct($name);
                $this->name = $name;
            }
        };

        $this->entityManager->method('getClassMetadata')
            ->with(Player::class)
            ->willReturn($classMetadata);
    }

    /**
     * Tests the constructor.
     */
    public function testConstruct(): void
    {
        $repository = new PlayerRepository($this->registry);
        $this->assertInstanceOf(PlayerRepository::class, $repository);
        $this->assertSame($this->entityManager, $repository->getEntityManager());
    }
}
