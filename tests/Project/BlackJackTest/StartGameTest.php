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
 * Test cases for the BlackJack startGame method.
 */
class StartGameTest extends TestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManagerMock;
    /**
     * @var EntityRepository
     */
    private $playerRepositoryMock;
    /**
     * @var EntityRepository
     */
    private $gameSessionRepositoryMock;
    /**
     * @var EntityRepository
     */
    private $cardStatRepositoryMock;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|BlackJackPlayer
     */
    private $mockPlayer;

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
    }

    /**
     * Helper method to call private/protected methods for testing.
     * @param object $object The object instance.
     * @param string $methodName The name of the private/protected method.
     * @param array $parameters Parameters to pass to the method.
     * @return mixed The result of the method call.
     * @throws \ReflectionException
     */
    protected function callPrivateMethod(object $object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Test starting a game with invalid number of hands.
     */
    public function testStartGameInvalidNumHands(): void
    {
        // Setup a player with enough balance.
        $playerName = 'TestPlayer';
        $existingPlayerEntity = new PlayerEntity();
        $existingPlayerEntity->setName($playerName);
        $existingPlayerEntity->setBalance(1000);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($existingPlayerEntity);

        $game = new BlackJack($playerName, $this->entityManagerMock);

        // Test with 0 hands.
        $result = $game->startGame(0, 100);
        $this->assertFalse($result, 'Should return false for numHands < 1');

        // Test with 4 hands
        $result = $game->startGame(4, 100);
        $this->assertFalse($result, 'Should return false for numHands > 3');

        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('flush');
    }

    /**
     * Test starting a game with insufficient funds.
     */
    public function testStartGameInsufficientFunds(): void
    {
        $playerName = 'TestPlayer';
        $existingPlayerEntity = new PlayerEntity();
        $existingPlayerEntity->setName($playerName);
        $existingPlayerEntity->setBalance(50);

        $this->playerRepositoryMock->method('findOneBy')->willReturn($existingPlayerEntity);

        $game = new BlackJack($playerName, $this->entityManagerMock);

        $numHands = 1;
        $betPerHand = 100;
        $result = $game->startGame($numHands, $betPerHand);

        $this->assertFalse($result);
        $this->assertEquals('not_started', $game->getStatus());

        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('flush');
    }

    /**
     * Test startGame returns false if placing bet fails.
     */
    public function testStartGameFailsWhenPlaceBetFails(): void
    {
        $mockPlayer = $this->createMock(BlackJackPlayer::class);
        $mockPlayer->method('getBalance')->willReturn(1000);
        $mockPlayer->method('placeBet')->willReturn(false);

        $game = new BlackJack('TestPlayer', $this->entityManagerMock);
        $this->setPrivateProperty($game, 'player', $mockPlayer);

        $this->assertFalse($game->startGame(1, 100));
    }

    /**
     * Test startGame returns false if second card draw for player is null.
     */
    public function testStartGameFailsWhenSecondCardIsNull(): void
    {
        $playerEntity = new PlayerEntity();
        $playerEntity->setName('TestPlayer');
        $playerEntity->setBalance(1000);

        $this->playerRepositoryMock
            ->method('findOneBy')
            ->willReturn($playerEntity);

        $mockPlayer = $this->createMock(BlackJackPlayer::class);
        $mockPlayer->method('getBalance')->willReturn(1000);
        $mockPlayer->method('getEntity')->willReturn($playerEntity);
        $mockPlayer->method('placeBet')->willReturn(true);
        $mockPlayer->method('getHands')->willReturn([]);
        $mockPlayer->method('getScore')->willReturn(10);
        $mockPlayer->method('isBlackjack')->willReturn(false);
        $mockPlayer->method('initializeNewHand')->willReturnCallback(function () {});

        $mockDeck = $this->createMock(BlackJackDeck::class);
        $mockDeck->expects($this->once())->method('shuffle');
        $mockDeck->method('draw')->willReturnOnConsecutiveCalls(
            new BlackJackGraphic('hearts', '10'),
            null
        );

        $game = $this->getMockBuilder(BlackJack::class)
            ->setConstructorArgs(['TestPlayer', $this->entityManagerMock])
            ->onlyMethods(['createDeck'])
            ->getMock();

        $game->method('createDeck')->willReturn($mockDeck);

        $reflection   = new \ReflectionClass(\App\Project\BlackJack::class);
        $playerProp   = $reflection->getProperty('player');
        $playerProp->setAccessible(true);
        $playerProp->setValue($game, $mockPlayer);

        $result = $game->startGame(1, 100);

        $this->assertFalse($result, 'startGame should return false when second card draw fails');
    }

    /**
     * Tests that startGame() returns false if the dealer’s second card draw is null.
     */
    public function testStartGameFailsWhenDealerSecondCardIsNull(): void
    {
        $playerEntity = new PlayerEntity();
        $playerEntity->setName('TestPlayer');
        $playerEntity->setBalance(1000);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($playerEntity);

        $mockDeck = $this->createMock(BlackJackDeck::class);
        $mockDeck->expects($this->once())->method('shuffle');
        $mockDeck->method('draw')->willReturnOnConsecutiveCalls(
            new BlackJackGraphic('hearts', '9'),
            new BlackJackGraphic('spades', '7'),
            new BlackJackGraphic('clubs', '8'),
            null
        );

        $game = $this->getMockBuilder(\App\Project\BlackJack::class)
                    ->setConstructorArgs(['TestPlayer', $this->entityManagerMock])
                    ->onlyMethods(['createDeck'])
                    ->getMock();
        $game->method('createDeck')->willReturn($mockDeck);

        $mockPlayer = $this->createMock(BlackJackPlayer::class);
        $mockPlayer->method('getBalance')->willReturn(1000);
        $mockPlayer->method('getEntity')->willReturn($playerEntity);
        $mockPlayer->method('placeBet')->willReturn(true);
        $mockPlayer->method('initializeNewHand')->willReturnCallback(function () {});
        $mockPlayer->method('clearHands')->willReturnCallback(function () {});
        $mockPlayer->method('getHands')->willReturn([0 => []]);
        $mockPlayer->method('getScore')->willReturn(16);
        $mockPlayer->method('isBlackjack')->willReturn(false);

        $r = new \ReflectionClass(\App\Project\BlackJack::class);
        $playerProp = $r->getProperty('player');
        $playerProp->setAccessible(true);
        $playerProp->setValue($game, $mockPlayer);

        $mockDealer = $this->createMock(BlackJackPlayer::class);
        $dealerProp = $r->getProperty('dealer');
        $dealerProp->setAccessible(true);
        $dealerProp->setValue($game, $mockDealer);

        $result = $game->startGame(1, 100);
        $this->assertFalse($result, 'startGame should return false when dealer’s second card is null (line 150)');
    }

    /**
     * Tests scenario where dealer get blackjack immediately.
     */
    public function testDealerGetsBlackjack(): void
    {
        $playerEntity = new PlayerEntity();
        $playerEntity->setName('DealerBJ');
        $playerEntity->setBalance(1000);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($playerEntity);

        $mockDeck = $this->getMockBuilder(BlackJackDeck::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['shuffle', 'draw'])
            ->getMock();

        $mockDeck->expects($this->any())
            ->method('draw')
            ->willReturnOnConsecutiveCalls(
                new BlackJackGraphic('Hearts', '9'),
                new BlackJackGraphic('Clubs', '9'),
                new BlackJackGraphic('Spades', 'A'),
                new BlackJackGraphic('Spades', 'K')
            );

        $game = $this->getMockBuilder(BlackJack::class)
            ->setConstructorArgs(['DealerBJ', $this->entityManagerMock])
            ->onlyMethods(['createDeck'])
            ->getMock();

        $game->expects($this->once())->method('createDeck')->willReturn($mockDeck);

        $game->startGame(1, 100);

        $status = $this->callPrivateMethod($game, 'getStatus');
        $this->assertEquals('game_over', $status);

        $this->assertEquals(900, $game->getPlayer()->getBalance());
    }

    /**
     * Helper method to set private properties via reflection.
     * @param object $object The target object.
     * @param string $property Property name.
     * @param mixed $value Value to set.
     */
    private function setPrivateProperty($object, $property, $value): void
    {
        $reflection = new \ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }

    /**
     * Tests updateHandStateAfterAction private method calls bust and setHandState appropriately.
     */
    public function testUpdateHandStateAfterActionCallsBustAndSetHandState(): void
    {
        // Setup for a player entity with sufficient balance.
        $playerName = 'TestPlayer';
        $existingPlayerEntity = new PlayerEntity();
        $existingPlayerEntity->setName($playerName);
        $existingPlayerEntity->setBalance(1000);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($existingPlayerEntity);

        $game = new BlackJack($playerName, $this->entityManagerMock);
        $this->setPrivateProperty($game, 'player', $this->mockPlayer);

        $handIndex = 0;

        // Simulate hand with score > 21, expect bust () to be triggered.
        $this->mockPlayer->expects($this->once())
            ->method('getScore')
            ->with($handIndex)
            ->willReturn(22);

        $this->mockPlayer->expects($this->once())
            ->method('bust')
            ->with($handIndex);

        $this->callPrivateMethod($game, 'updateHandStateAfterAction', [$handIndex]);

        // Reconfigure mock to simulate blackjack scenario.
        $this->mockPlayer = $this->createMock(BlackJackPlayer::class);
        $this->setPrivateProperty($game, 'player', $this->mockPlayer);

        $this->mockPlayer->expects($this->once())
            ->method('getScore')
            ->with($handIndex)
            ->willReturn(21);

        $this->mockPlayer->expects($this->once())
            ->method('isBlackjack')
            ->with($handIndex)
            ->willReturn(true);

        // Simulate a blackjack hand (Ace + King)
        $this->mockPlayer->expects($this->once())
            ->method('getHand')
            ->with($handIndex)
            ->willReturn([
                new BlackJackGraphic('H', 'A'),
                new BlackJackGraphic('S', 'K')
            ]);

        $this->mockPlayer->expects($this->once())
            ->method('setHandState')
            ->with($handIndex, 'finished');

        $this->callPrivateMethod($game, 'updateHandStateAfterAction', [$handIndex]);
    }
}
