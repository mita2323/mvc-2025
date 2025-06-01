<?php

namespace App\Tests\Project;

use App\Project\BlackJack;
use App\Project\BlackJackPlayer;
use App\Project\BlackJackDeck;
use App\Project\BlackJackGraphic;
use App\Entity\Player as PlayerEntity;
use App\Entity\GameSession;
use App\Entity\CardStat;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for the BlackJack class constructor.
 */
class ConstructorTest extends TestCase
{
    /**
     * @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $entityManagerMock;
    /**
     * @var EntityRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $playerRepositoryMock;
    /**
     * @var EntityRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $gameSessionRepositoryMock;
    /**
     * @var EntityRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cardStatRepositoryMock;
    /**
     * @var BlackJackPlayer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mockPlayer;
    /**
     * Mock instance of the dealer player for testing dealer-related functionality.
     * @var BlackJackPlayer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mockDealer;
    /**
     * Mock instance of the deck for testing deck-related interactions.
     * @var BlackJackDeck|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mockDeck;

    /**
     * Initializes the entity manager and repositories for Player, GameSession, and CardStat.
     */
    protected function setUp(): void
    {
        $this->playerRepositoryMock = $this->createMock(EntityRepository::class);
        $this->gameSessionRepositoryMock = $this->createMock(EntityRepository::class);
        $this->cardStatRepositoryMock = $this->createMock(EntityRepository::class);

        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $this->entityManagerMock->method('getRepository')
            ->willReturnMap([
                [PlayerEntity::class, $this->playerRepositoryMock],
                [GameSession::class, $this->gameSessionRepositoryMock],
                [CardStat::class, $this->cardStatRepositoryMock],
            ]);
        
        $this->mockPlayer = $this->createMock(BlackJackPlayer::class);
        $this->mockDealer = $this->createMock(BlackJackPlayer::class);
        $this->mockDeck = $this->createMock(BlackJackDeck::class);
    }

    /**
     * Test the constructor of the BlackJack class.
     */
    public function testConstructor(): void
    {
        $playerName = 'TestPlayer';

        $this->playerRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => $playerName])
            ->willReturn(null);

        $this->entityManagerMock->expects($this->atLeastOnce())
            ->method('persist')
            ->with($this->isInstanceOf(PlayerEntity::class));
        $this->entityManagerMock->expects($this->atLeastOnce())
            ->method('flush');

        $game = new BlackJack($playerName, $this->entityManagerMock);

        $this->assertIsObject($game);
        $this->assertInstanceOf(BlackJack::class, $game);
        $this->assertEquals('not_started', $game->getStatus());
        $this->assertInstanceOf(BlackJackDeck::class, $game->getDeck());
        $this->assertInstanceOf(BlackJackPlayer::class, $game->getPlayer());
        $this->assertEquals($playerName, $game->getPlayer()->getName());
        $this->assertInstanceOf(BlackJackPlayer::class, $game->getDealer());
        $this->assertEquals('Dealer', $game->getDealer()->getName());
    }

    /**
     * Test the constructor when player already exists.
     */
    public function testConstructorPlayerExists(): void
    {
        $playerName = 'ExistingPlayer';
        $existingPlayerEntity = new PlayerEntity();
        $existingPlayerEntity->setName($playerName);
        $existingPlayerEntity->setBalance(500);

        $this->playerRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => $playerName])
            ->willReturn($existingPlayerEntity);

        $this->entityManagerMock->expects($this->never())
            ->method('persist')
            ->with($this->isInstanceOf(PlayerEntity::class));
        $this->entityManagerMock->expects($this->never())
            ->method('flush');

        $game = new BlackJack($playerName, $this->entityManagerMock);

        $this->assertIsObject($game);
        $this->assertInstanceOf(BlackJack::class, $game);
        $this->assertEquals($playerName, $game->getPlayer()->getName());
        $this->assertEquals(500, $game->getPlayer()->getBalance());
    }
}
