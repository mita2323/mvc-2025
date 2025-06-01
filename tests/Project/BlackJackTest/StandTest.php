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
 * Test cases for the BlackJack stand method.
 */
class StandTest extends TestCase
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
     * Helper method to set private or protected properties on objects during testing.
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
     * Test the stand method.
     */
    public function testStand(): void
    {
        $playerName = 'TestPlayer';
        $existingPlayerEntity = new PlayerEntity();
        $existingPlayerEntity->setName($playerName);
        $existingPlayerEntity->setBalance(1000);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($existingPlayerEntity);

        $game = new BlackJack($playerName, $this->entityManagerMock);
        $game->startGame(1, 100);

        $player = $game->getPlayer();

        // Set player's hand state to 'active' so stand action applies to it.
        $playerReflection = new \ReflectionClass($player);
        $handStatesProperty = $playerReflection->getProperty('handStates');
        $handStatesProperty->setAccessible(true);
        $handStatesProperty->setValue($player, [0 => 'active']);

        $game->stand(0);

        $this->assertEquals('game_over', $game->getStatus());
    }

    /**
     * Tests that the stand method returns early without proceeding when the game
     * status is not appropriate or the conditions are not met.
     */
    public function testStandReturnsEarlyWhenConditionsNotMet(): void
    {
        $playerEntity = new PlayerEntity();
        $playerEntity->setName('TestPlayer');
        $playerEntity->setBalance(1000);
        $this->playerRepositoryMock->method('findOneBy')->willReturn($playerEntity);

        $game = new BlackJack('TestPlayer', $this->entityManagerMock);
        $game->startGame(1, 100);

        $this->setPrivateProperty($game, 'status', 'finished');
        $this->setPrivateProperty($game, 'activeHandIndex', 0);

        $mockPlayer = $this->createMock(BlackJackPlayer::class);

        // Inject mock player to intercept calls.
        $reflection = new \ReflectionClass($game);
        $playerProp = $reflection->getProperty('player');
        $playerProp->setAccessible(true);
        $playerProp->setValue($game, $mockPlayer);

        $game->stand(0);

        $mockPlayer->expects($this->never())->method('stand');
    }
}
