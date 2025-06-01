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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for the BlackJack hit method.
 */
class HitTest extends TestCase
{
    /**
     * @var MockObject&EntityManagerInterface
     */
    private $entityManagerMock;

    /**
     * @var MockObject&EntityRepository<PlayerEntity>
     */
    private $playerRepositoryMock;

    /**
     * @var MockObject&EntityRepository<GameSession>
     */
    private $gameSessionRepositoryMock;

    /**
     * @var MockObject&EntityRepository<CardStat>
     */
    private $cardStatRepositoryMock;

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
    }

    /**
     * Helper method to call private/protected methods for testing.
     * @param object $object The object instance.
     * @param string $methodName The name of the private/protected method.
     * @param mixed[] $parameters Parameters to pass to the method.
     * @return mixed The result of the method call.
     * @throws \ReflectionException
     */
    protected function callPrivateMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Helper method to retrieve a private or protected property value for testing.
     * @param object $object The object instance.
     * @param string $propertyName The property name.
     * @return mixed The value of the property.
     */
    protected function getPrivateProperty(object $object, string $propertyName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    /**
     * Test successful hit where the player's hand remains active after drawing a card.
     */
    public function testHitSuccessfulAndHandActive(): void
    {
        $playerName = 'HitPlayer';
        $existingPlayerEntity = new PlayerEntity();
        $existingPlayerEntity->setName($playerName);
        $existingPlayerEntity->setBalance(1000);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($existingPlayerEntity);

        $game = new BlackJack($playerName, $this->entityManagerMock);
        $game->startGame(1, 100);

        $gameReflection = new \ReflectionClass($game);

        $statusProperty = $gameReflection->getProperty('status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($game, 'ongoing');

        $activeHandIndexProperty = $gameReflection->getProperty('activeHandIndex');
        $activeHandIndexProperty->setAccessible(true);
        $activeHandIndexProperty->setValue($game, 0);

        $player = $game->getPlayer();
        $playerReflection = new \ReflectionClass($player);

        $handsProperty = $playerReflection->getProperty('hands');
        $handsProperty->setAccessible(true);

        $handStatesProperty = $playerReflection->getProperty('handStates');
        $handStatesProperty->setAccessible(true);

        $handsProperty->setValue($player, [
            0 => [new BlackJackGraphic('H', '5'), new BlackJackGraphic('D', '3')]
        ]);
        $handStatesProperty->setValue($player, [0 => 'active']);

        $deckProperty = $gameReflection->getProperty('deck');
        $deckProperty->setAccessible(true);

        $mockDeck = $this->createMock(BlackJackDeck::class);
        $mockDeck->expects($this->once())->method('draw')
            ->willReturn(new BlackJackGraphic('S', '2'));

        $deckProperty->setValue($game, $mockDeck);

        $this->cardStatRepositoryMock->method('findOneBy')->willReturn(null);
        $this->entityManagerMock->/** @scrutinizer ignore-call */expects($this->atLeastOnce())
            ->method('persist')
            ->with($this->isInstanceOf(CardStat::class));
        $this->entityManagerMock->expects($this->atLeastOnce())->method('flush');

        $result = $game->hit(0);

        $this->assertTrue($result);

        $this->assertCount(3, $player->getHand(0));

        // Verify actual cards by their suit and rank.
        $handCards = $player->getHand(0);
        $this->assertEquals('H', $handCards[0]->getSuit());
        $this->assertEquals('5', $handCards[0]->getRank());
        $this->assertEquals('D', $handCards[1]->getSuit());
        $this->assertEquals('3', $handCards[1]->getRank());
        $this->assertEquals('S', $handCards[2]->getSuit());
        $this->assertEquals('2', $handCards[2]->getRank());

        $this->assertEquals(10, $player->getScore(0));
        $this->assertEquals('active', $player->getHandState(0));
    }

    /**
     * Test hit method returns false if pre-conditions (status, active hand, hand state) are not met.
     */
    public function testHitReturnsFalseOnInvalidPreconditions(): void
    {
        $playerName = 'PreconditionPlayer';
        $existingPlayerEntity = new PlayerEntity();
        $existingPlayerEntity->setName($playerName);
        $existingPlayerEntity->setBalance(1000);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($existingPlayerEntity);

        $game = new BlackJack($playerName, $this->entityManagerMock);
        $game->startGame(2, 100);

        $gameReflection = new \ReflectionClass($game);

        $statusProperty = $gameReflection->getProperty('status');
        $statusProperty->setAccessible(true);

        $activeHandIndexProperty = $gameReflection->getProperty('activeHandIndex');
        $activeHandIndexProperty->setAccessible(true);

        $player = $game->getPlayer();
        $playerReflection = new \ReflectionClass($player);

        $handsProperty = $playerReflection->getProperty('hands');
        $handsProperty->setAccessible(true);

        $handStatesProperty = $playerReflection->getProperty('handStates');
        $handStatesProperty->setAccessible(true);

        // Test 1: Game not ongoing
        $statusProperty->setValue($game, 'not_started');
        $handsProperty->setValue($player, [0 => [new BlackJackGraphic('H', '7'), new BlackJackGraphic('D', '7')], 1 => []]);
        $handStatesProperty->setValue($player, [0 => 'active', 1 => 'active']);
        $activeHandIndexProperty->setValue($game, 0);
        $this->assertFalse($game->hit(0), 'Should return false when game is not ongoing.');
        $this->assertCount(2, $player->getHand(0), 'Hand should not change when game is not ongoing.');

        // Test 2: handIndex is not the activeHandIndex
        $statusProperty->setValue($game, 'ongoing');
        $activeHandIndexProperty->setValue($game, 1);
        $handsProperty->setValue($player, [0 => [new BlackJackGraphic('H', '7'), new BlackJackGraphic('D', '7')], 1 => []]);
        $handStatesProperty->setValue($player, [0 => 'active', 1 => 'active']);
        $this->assertFalse($game->hit(0), 'Should return false when handIndex is not activeHandIndex.');
        $this->assertCount(2, $player->getHand(0), 'Hand 0 should not change when not active hand.');

        // Test 3: Player hand is not active
        $activeHandIndexProperty->setValue($game, 0);
        $handStatesProperty->setValue($player, [0 => 'finished', 1 => 'active']);
        $this->assertFalse($game->hit(0), 'Should return false when player hand is not active.');
        $this->assertCount(2, $player->getHand(0), 'Hand should not change when not active.');
    }

    /**
     * Test hit method returns false if the deck fails to draw a card.
     */
    public function testHitReturnsFalseOnDeckDrawFailure(): void
    {
        $playerName = 'EmptyDeckPlayer';
        $existingPlayerEntity = new PlayerEntity();
        $existingPlayerEntity->setName($playerName);
        $existingPlayerEntity->setBalance(1000);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($existingPlayerEntity);

        $game = new BlackJack($playerName, $this->entityManagerMock);
        $game->startGame(1, 100);

        $gameReflection = new \ReflectionClass($game);

        $statusProperty = $gameReflection->getProperty('status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($game, 'ongoing');

        $activeHandIndexProperty = $gameReflection->getProperty('activeHandIndex');
        $activeHandIndexProperty->setAccessible(true);
        $activeHandIndexProperty->setValue($game, 0);

        $player = $game->getPlayer();
        $playerReflection = new \ReflectionClass($player);

        $handsProperty = $playerReflection->getProperty('hands');
        $handsProperty->setAccessible(true);

        $handStatesProperty = $playerReflection->getProperty('handStates');
        $handStatesProperty->setAccessible(true);

        $handsProperty->setValue($player, [
            0 => [new BlackJackGraphic('H', '5'), new BlackJackGraphic('D', '3')]
        ]);
        $handStatesProperty->setValue($player, [0 => 'active']);

        $deckProperty = $gameReflection->getProperty('deck');
        $deckProperty->setAccessible(true);

        $mockDeck = $this->createMock(BlackJackDeck::class);
        $mockDeck->expects($this->once())->method('draw')->willReturn(null);

        $deckProperty->setValue($game, $mockDeck);

        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('flush');

        $result = $game->hit(0);

        $this->assertFalse($result, 'Should return false when deck fails to draw a card.');
        $this->assertCount(2, $player->getHand(0), 'Hand should not change when card draw fails.');
        $this->assertEquals('active', $player->getHandState(0), 'Hand state should remain active when card draw fails.');
    }

    /**
     * Test hit method calls player bust logic when the hand score exceeds 21.
     */
    public function testHitCallsPlayerBustOnScoreExceeds21(): void
    {
        $playerName = 'BusterCallPlayer';
        $initialPlayerBalance = 1000;
        $existingPlayerEntity = new PlayerEntity();
        $existingPlayerEntity->setName($playerName);
        $existingPlayerEntity->setBalance($initialPlayerBalance);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($existingPlayerEntity);

        $mockPlayer = $this->createMock(BlackJackPlayer::class);
        $mockPlayer->method('isHandActive')->with(0)->willReturn(true);
        $mockPlayer->method('addCard')->with($this->isInstanceOf(BlackJackGraphic::class), 0);
        $mockPlayer->method('getScore')->with(0)->willReturn(23);
        $mockPlayer->expects($this->once())->method('bust')->with(0);

        $game = new BlackJack($playerName, $this->entityManagerMock);

        $gameReflection = new \ReflectionClass($game);

        $playerProperty = $gameReflection->getProperty('player');
        $playerProperty->setAccessible(true);
        $playerProperty->setValue($game, $mockPlayer);

        $game->startGame(1, 100);

        $statusProperty = $gameReflection->getProperty('status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($game, 'ongoing');

        $activeHandIndexProperty = $gameReflection->getProperty('activeHandIndex');
        $activeHandIndexProperty->setAccessible(true);
        $activeHandIndexProperty->setValue($game, 0);

        $deckProperty = $gameReflection->getProperty('deck');
        $deckProperty->setAccessible(true);

        $mockDeck = $this->createMock(BlackJackDeck::class);
        $mockDeck->expects($this->once())->method('draw')->willReturn(new BlackJackGraphic('S', '5'));

        $deckProperty->setValue($game, $mockDeck);

        $result = $game->hit(0);

        $this->assertTrue($result, 'Hit should return true indicating a card was drawn.');
    }

    /**
     * Test scenario where the player busts and verify that the bust method is called
     * and balance is updated accordingly.
     */
    public function testPlayerBustsAndBustMethodIsCalled(): void
    {
        $playerEntity = new PlayerEntity();
        $playerEntity->setName('BustPlayer');
        $playerEntity->setBalance(1000);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($playerEntity);

        $mockDeck = $this->createMock(BlackJackDeck::class);
        $mockDeck->method('shuffle');
        $mockDeck->method('draw')->willReturnOnConsecutiveCalls(
            new BlackJackGraphic('H', '10'),
            new BlackJackGraphic('C', '9'),
            new BlackJackGraphic('H', '8'),
            new BlackJackGraphic('S', '7'),
            new BlackJackGraphic('D', '5')
        );

        $game = $this->getMockBuilder(BlackJack::class)
            ->setConstructorArgs(['BustPlayer', $this->entityManagerMock])
            ->onlyMethods(['createDeck'])
            ->getMock();

        $game->method('createDeck')->willReturn($mockDeck);

        $game->startGame(1, 100);
        $game->hit(0);

        $this->callPrivateMethod($game, 'evaluateGame');

        $this->assertEquals(900, $game->getPlayer()->getBalance());
    }
}
