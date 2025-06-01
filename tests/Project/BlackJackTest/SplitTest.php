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
 * Test cases for the BlackJack split method.
 */
class SplitTest extends TestCase
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
     * Helper to set private or protected property on objects for test setup.
     */
    private function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }

    /**
     * Test split method with invalid hand index or non-active hand.
     */
    public function testSplitInvalidHandOrNotActive(): void
    {
        $playerName = 'TestPlayer';
        $existingPlayerEntity = new PlayerEntity();
        $existingPlayerEntity->setName($playerName);
        $existingPlayerEntity->setBalance(1000);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($existingPlayerEntity);

        $game = new BlackJack($playerName, $this->entityManagerMock);
        $game->startGame(1, 100);

        $player = $game->getPlayer();
        $playerReflection = new \ReflectionClass($player);
        $handStatesProperty = $playerReflection->getProperty('handStates');
        $handStatesProperty->setAccessible(true);

        // Set hand state to finished (not active).
        $handStatesProperty->setValue($player, [0 => 'finished']);

        // Passing invalid and inactive hand indexes should return false
        $this->assertFalse($game->split(0));
        $this->assertFalse($game->split(-1));
        $this->assertFalse($game->split(99));

        $this->entityManagerMock->/** @scrutinizer ignore-call */expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('flush');
    }

    /**
     * Test split method with insufficient funds.
     */
    public function testSplitInsufficientFunds(): void
    {
        $playerName = 'BrokePlayer';
        $initialBalance = 50;
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

        // Set a splittable hand of two 7s and active state.
        $handsProperty->setValue($player, [
            0 => [new BlackJackGraphic('H', '7'), new BlackJackGraphic('D', '7')]
        ]);
        $handStatesProperty->setValue($player, [0 => 'active']);
        $betsProperty->setValue($player, [0 => $bet]);

        // Simulate player having less balance than bet.
        $player->setBalance($initialBalance - $bet);

        $result = $game->split(0);

        $this->assertFalse($result);
        $this->assertEquals(
            $initialBalance - $bet,
            $player->getBalance(),
            "Balance should remain unchanged when split fails due to insufficient funds."
        );
        $this->assertCount(1, $player->getHands());

        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('flush');
    }

    /**
     * Test split method when the hand is not splittable.
     */
    public function testSplitHandNotSplittable(): void
    {
        $playerName = 'NotSplittablePlayer';
        $initialBalance = 1000;
        $bet = 100;
        $existingPlayerEntity = new PlayerEntity();
        $existingPlayerEntity->setName($playerName);
        $existingPlayerEntity->setBalance($initialBalance);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($existingPlayerEntity);

        $game = new BlackJack($playerName, $this->entityManagerMock);
        $game->startGame(1, $bet);

        $player = $game->getPlayer();

        // Confirm balance deducted for initial bet.
        $this->assertEquals(
            $initialBalance - $bet,
            $player->getBalance(),
            "Balance after initial bet deduction"
        );

        $playerReflection = new \ReflectionClass($player);
        $handsProperty = $playerReflection->getProperty('hands');
        $handsProperty->setAccessible(true);
        $handStatesProperty = $playerReflection->getProperty('handStates');
        $handStatesProperty->setAccessible(true);
        $betsProperty = $playerReflection->getProperty('bets');
        $betsProperty->setAccessible(true);

        // Hand has cards 7 and 10 which are not splittable.
        $handsProperty->setValue($player, [
            0 => [new BlackJackGraphic('H', '7'), new BlackJackGraphic('D', '10')]
        ]);
        $handStatesProperty->setValue($player, [0 => 'active']);
        $betsProperty->setValue($player, [0 => $bet]);

        // Replace deck with mock that expects no draw calls.
        $reflection = new \ReflectionClass($game);
        $deckProperty = $reflection->getProperty('deck');
        $deckProperty->setAccessible(true);
        $mockDeck = $this->createMock(BlackJackDeck::class);
        $mockDeck->expects($this->never())->method('draw');
        $deckProperty->setValue($game, $mockDeck);

        $result = $game->split(0);

        $this->assertFalse($result);
        $this->assertEquals(
            $initialBalance - $bet,
            $player->getBalance(),
            "Balance should not change when split fails due to non-splittable hand."
        );
        $this->assertCount(1, $player->getHands());

        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('flush');
    }

    /**
     * Test various split scenarios for success and failure cases.
     */
    public function testSplitScenarios(): void
    {
        $playerEntity = new PlayerEntity();
        $playerEntity->setName('TestPlayer');
        $playerEntity->setBalance(1000);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($playerEntity);

        $game = new BlackJack('TestPlayer', $this->entityManagerMock);
        $game->startGame(1, 100);
        $player = $game->getPlayer();

        // Reset player state to default splittable hand, bet, and active state.
        $resetPlayerState = function () use ($player, $game) {
            $this->setPrivateProperty($player, 'hands', [[new BlackJackGraphic('H', '8'), new BlackJackGraphic('S', '8')]]);
            $this->setPrivateProperty($player, 'bets', [0 => 100]);
            $this->setPrivateProperty($player, 'handStates', [0 => 'active']);
            $this->setPrivateProperty($game, 'activeHandIndex', 0);
        };

        $resetPlayerState();

        // Insufficient balance for doubling bet to split.
        $playerEntity->setBalance(50);
        $this->assertFalse($game->split(0), 'Should fail split due to insufficient balance');

        $resetPlayerState();

        // Deck returns null when drawing new cards after split.
        $playerEntity->setBalance(1000);
        $emptyDeck = $this->createMock(BlackJackDeck::class);
        $emptyDeck->method('draw')->willReturn(null);
        $this->setPrivateProperty($game, 'deck', $emptyDeck);
        $this->assertFalse($game->split(0), 'Should fail split due to null cards drawn');

        $resetPlayerState();

        // Successful split scenario, sufficient balance and valid cards drawn from deck.
        $playerEntity->setBalance(1000);
        $deck = $this->createMock(BlackJackDeck::class);

        $deck->method('draw')->willReturnCallback(function () {
            return new BlackJackGraphic('H', '9');
        });

        $this->setPrivateProperty($game, 'deck', $deck);

        $result = $game->split(0);
        $this->assertTrue($result, 'Should succeed split with valid setup');
    }
}
