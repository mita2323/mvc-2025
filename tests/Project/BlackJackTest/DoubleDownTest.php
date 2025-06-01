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
 * Test cases for the BlackJack doubleDown method.
 */
class DoubleDownTest extends TestCase
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
     * Helper method to set a private or protected property value visa reflection.
     * @param object $object The object instance to modify.
     * @param string $property The name of the property to set.
     * @param mixed $value The value to assign to the property.
     */
    private function setPrivateProperty($object, $property, $value): void
    {
        $reflection = new \ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }

    /**
     * Test doubleDown method with insufficient funds.
     */
    public function testDoubleDownInsufficientFunds(): void
    {
        $playerName = 'TestPlayer';
        $initialBalance = 150;
        $bet = 100;

        $existingPlayerEntity = new PlayerEntity();
        $existingPlayerEntity->setName($playerName);
        $existingPlayerEntity->setBalance($initialBalance);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($existingPlayerEntity);

        $game = new BlackJack($playerName, $this->entityManagerMock);
        $game->startGame(1, $bet);

        $player = $game->getPlayer();
        $playerReflection = new \ReflectionClass($player);
        $handsProperty = $playerReflection->getProperty('hands');
        $handsProperty->setAccessible(true);
        $handStatesProperty = $playerReflection->getProperty('handStates');
        $handStatesProperty->setAccessible(true);
        $betsProperty = $playerReflection->getProperty('bets');
        $betsProperty->setAccessible(true);

        $handsProperty->setValue($player, [
            0 => [new BlackJackGraphic('H', '6'), new BlackJackGraphic('D', '4')]
        ]);
        $handStatesProperty->setValue($player, [0 => 'active']);
        $betsProperty->setValue($player, [0 => $bet]);
        $player->setBalance($initialBalance - $bet);

        $result = $game->doubleDown(0);

        $this->assertFalse($result);
        $this->assertEquals($initialBalance - $bet, $player->getBalance());
        $this->assertEquals($bet, $player->getBet(0));
        $this->assertCount(2, $player->getHand(0));

        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('flush');
    }

    /**
     * Test doubleDown method when the hand does not have exactly two cards.
     */
    public function testDoubleDownInvalidHandCardCount(): void
    {
        $playerName = 'ManyCardsPlayer';
        $existingPlayerEntity = new PlayerEntity();
        $existingPlayerEntity->setName($playerName);
        $existingPlayerEntity->setBalance(1000);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($existingPlayerEntity);

        $game = new BlackJack($playerName, $this->entityManagerMock);
        $game->startGame(1, 100);

        $player = $game->getPlayer();
        $playerReflection = new \ReflectionClass($player);
        $handsProperty = $playerReflection->getProperty('hands');
        $handsProperty->setAccessible(true);
        $handStatesProperty = $playerReflection->getProperty('handStates');
        $handStatesProperty->setAccessible(true);

        // Test 1: Hand with 3 cards.
        $handsProperty->setValue($player, [
            0 => [new BlackJackGraphic('H', '2'), new BlackJackGraphic('D', '3'), new BlackJackGraphic('S', '4')]
        ]);
        $handStatesProperty->setValue($player, [0 => 'active']);
        $result = $game->doubleDown(0);
        $this->assertFalse($result);

        // Test 2: Hand with only 1 card.
        $handsProperty->setValue($player, [
            0 => [new BlackJackGraphic('H', 'K')]
        ]);
        $handStatesProperty->setValue($player, [0 => 'active']);
        $result = $game->doubleDown(0);
        $this->assertFalse($result);
    }

    /**
     * Test doubleDown when the game is not ongoing or hand is not active/correct index.
     */
    public function testDoubleDownInvalidGameOrHandState(): void
    {
        $playerName = 'StateTestPlayer';
        $existingPlayerEntity = new PlayerEntity();
        $existingPlayerEntity->setName($playerName);
        $existingPlayerEntity->setBalance(1000);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($existingPlayerEntity);

        $game = new BlackJack($playerName, $this->entityManagerMock);

        $result = $game->doubleDown(0);
        $this->assertFalse($result);
        $game->startGame(1, 100);
        
        $player = $game->getPlayer();
        $playerReflection = new \ReflectionClass($player);
        $handStatesProperty = $playerReflection->getProperty('handStates');
        $handStatesProperty->setAccessible(true);

        $handStatesProperty->setValue($player, [0 => 'finished']);
        $result = $game->doubleDown(0);
        $this->assertFalse($result);

        $reflection = new \ReflectionClass($game);
        $activeHandIndexProperty = $reflection->getProperty('activeHandIndex');
        $activeHandIndexProperty->setAccessible(true);
        $activeHandIndexProperty->setValue($game, 1);

        $handStatesProperty->setValue($player, [0 => 'active']);
        $result = $game->doubleDown(0);
        $this->assertFalse($result);

        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('flush');
    }

    /**
     * Test doubleDown method when all conditions are met, card is drawn, and
     * hand finishes successfully.
     */
    public function testDoubleDownDrawAndFinish(): void
    {
        $playerEntity = new PlayerEntity();
        $playerEntity->setName('TestPlayer');
        $playerEntity->setBalance(1000);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($playerEntity);

        $game = new BlackJack('TestPlayer', $this->entityManagerMock);

        // Setup mock player
        $mockPlayer = $this->createMock(BlackJackPlayer::class);
        $mockPlayer->method('isHandActive')->with(0)->willReturn(true);
        $mockPlayer->method('getBalance')->willReturn(900);
        $mockPlayer->method('getBet')->with(0)->willReturn(100);
        $mockPlayer->method('placeBet')->with(100, 0)->willReturn(true);
        $mockPlayer->method('getHand')->with(0)->willReturn([
            new BlackJackGraphic('hearts', '5'),
            new BlackJackGraphic('clubs', '6'),
        ]);
        $mockPlayer->method('getScore')->with(0)->willReturn(20);
        $mockPlayer->expects($this->once())->method('addCard');
        $mockPlayer->expects($this->once())->method('setHandState')->with(0, 'finished');
        $mockPlayer->expects($this->never())->method('bust');

        // Setup mock deck to return a card
        $mockDeck = $this->createMock(BlackJackDeck::class);
        $mockDeck->method('draw')->willReturn(new BlackJackGraphic('spades', '5'));

        // Inject mocks and set game state
        $this->setPrivateProperty($game, 'player', $mockPlayer);
        $this->setPrivateProperty($game, 'deck', $mockDeck);
        $this->setPrivateProperty($game, 'status', 'ongoing');
        $this->setPrivateProperty($game, 'activeHandIndex', 0);

        $result = $game->doubleDown(0);

        $this->assertTrue($result, 'Double down should succeed');
    }

    /**
     * Test doubleDown method when the action causes the hand to bust.
     */
    public function testDoubleDownCausesBust(): void
    {
        $playerEntity = new PlayerEntity();
        $playerEntity->setName('TestPlayer');
        $playerEntity->setBalance(1000);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($playerEntity);

        $game = new BlackJack('TestPlayer', $this->entityManagerMock);
        $game->startGame(1, 100);

        $this->setPrivateProperty($game, 'status', 'ongoing');
        $this->setPrivateProperty($game, 'activeHandIndex', 0);

        $mockPlayer = $this->createMock(BlackJackPlayer::class);
        $mockPlayer->method('getHand')->with(0)->willReturn([
            new BlackJackGraphic('hearts', 'K'),
            new BlackJackGraphic('clubs', 'Q')
        ]);
        $mockPlayer->method('getBet')->with(0)->willReturn(100);
        $mockPlayer->method('getBalance')->willReturn(1000);
        $mockPlayer->method('isHandActive')->with(0)->willReturn(true);
        $mockPlayer->method('placeBet')->with(100, 0)->willReturn(true);

        $mockPlayer->expects($this->once())
            ->method('addCard')
            ->with($this->isInstanceOf(BlackJackGraphic::class), 0);

        $mockPlayer->method('getScore')->with(0)->willReturn(25);
        $mockPlayer->expects($this->once())->method('bust')->with(0);
        $mockPlayer->expects($this->never())->method('setHandState');

        $reflection = new \ReflectionClass($game);
        $playerProp = $reflection->getProperty('player');
        $playerProp->setAccessible(true);
        $playerProp->setValue($game, $mockPlayer);

        $mockDeck = $this->createMock(BlackJackDeck::class);
        $mockDeck->method('draw')->willReturn(new BlackJackGraphic('spades', '5'));

        $deckProp = $reflection->getProperty('deck');
        $deckProp->setAccessible(true);
        $deckProp->setValue($game, $mockDeck);

        $result = $game->doubleDown(0);

        $this->assertTrue($result, 'Double down causing bust should succeed');
    }
}
