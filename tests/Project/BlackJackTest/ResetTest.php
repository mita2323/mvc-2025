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
 * Test cases for the BlackJack reset method.
 */
class ResetTest extends TestCase
{
    /**
     * @var EntityManagerInterface&MockObject
     */
    private $entityManagerMock;

    /**
     * @var EntityRepository<PlayerEntity>&MockObject
     */
    private $playerRepositoryMock;

    /**
     * @var EntityRepository<GameSession>&MockObject
     */
    private $gameSessionRepositoryMock;

    /**
     * @var EntityRepository<CardStat>&MockObject
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
     * Test the reset method.
     */
    public function testReset(): void
    {
        $playerName = 'ResetPlayer';

        // Create a PlayerEntity with initial balance and name.
        $existingPlayerEntity = new PlayerEntity();
        $existingPlayerEntity->setName($playerName);
        $existingPlayerEntity->setBalance(1000);

        // Mock repository to return the existing player entity when searched by name.
        $this->playerRepositoryMock->method('findOneBy')->willReturn($existingPlayerEntity);

        // Create a new game instance with the mocked entity manager.
        $game = new BlackJack($playerName, $this->entityManagerMock);

        // State array to reset the game to.
        $resetState = [
            'player' => [
                'name' => 'ResetPlayer',
                'balance' => 750,
                'activeHandIndex' => 1,
                'hands' => [
                    0 => [
                        'data' => [['suit' => 'H', 'value' => 'J'], ['suit' => 'D', 'value' => '5']],
                        'bet' => 50,
                        'state' => 'finished'
                    ],
                    1 => [
                        'data' => [['suit' => 'C', 'value' => 'A'], ['suit' => 'S', 'value' => '7']],
                        'bet' => 100,
                        'state' => 'active'
                    ]
                ],
            ],
            'dealer' => [
                'hands' => [[['suit' => 'D', 'value' => 'Q'], ['suit' => 'C', 'value' => '9']]]
            ],
            'deck' => [
                ['suit' => 'H', 'value' => '2'],
                ['suit' => 'S', 'value' => '3']
            ],
            'status' => 'ongoing'
        ];

        $game->reset($resetState);

        $this->assertEquals('ongoing', $game->getStatus());
        $this->assertEquals(1, $game->getActiveHandIndex());
        $this->assertEquals(750, $game->getPlayer()->getBalance());

        $this->assertCount(2, $game->getPlayer()->getHands());

        $this->assertEquals(50, $game->getPlayer()->getBet(0));
        $this->assertEquals(100, $game->getPlayer()->getBet(1));
        $this->assertEquals('finished', $game->getPlayer()->getHandState(0));
        $this->assertEquals('active', $game->getPlayer()->getHandState(1));
        $this->assertEquals(15, $game->getPlayer()->getScore(0));
        $this->assertEquals(18, $game->getPlayer()->getScore(1));

        $this->assertCount(2, $game->getDealer()->getHand());

        $this->assertEquals(19, $game->getDealer()->getScore());

        $this->assertCount(2, $game->getDeck()->getCards());
    }
}
