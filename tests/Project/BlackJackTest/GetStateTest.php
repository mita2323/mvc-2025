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
 * Test cases for the BlackJack getState method.
 */
class GetStateTest extends TestCase
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
     * @var BlackJackPlayer&\PHPUnit\Framework\MockObject\MockObject
     */
    private $mockPlayer;
    /**
     * Mock instance of the dealer player for testing dealer-related functionality.
     * @var BlackJackPlayer&\PHPUnit\Framework\MockObject\MockObject
     */
    private $mockDealer;
    /**
     * Mock instance of the deck for testing deck-related interactions.
     * @var BlackJackDeck&\PHPUnit\Framework\MockObject\MockObject
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
     * Helper for reflection property setting.
     * @param object $obj
     * @param string $prop
     * @param mixed $val
     */
    private function setPrivateProperty(object $obj, string $prop, mixed $val): void
    {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);
        $property->setValue($obj, $val);
    }
    /**
     * Test getState method when the player has no hands and the deck is empty.
     */
    public function testGetStateWithNoHandsAndEmptyDeck(): void
    {
        // Setup mocks for empty hands and balance
        $this->mockPlayer->/** @scrutinizer ignore-call */method('getName')->willReturn('TestPlayer');
        $this->mockPlayer->/** @scrutinizer ignore-call */method('getBalance')->willReturn(1000);
        $this->mockPlayer->/** @scrutinizer ignore-call */method('getHands')->willReturn([]);
        // Dealer has no hands
        $this->mockDealer->/** @scrutinizer ignore-call */method('getHands')->willReturn([]);
        // Deck is empty
        $this->mockDeck->/** @scrutinizer ignore-call */method('getCards')->willReturn([]);
        $game = new BlackJack('TestPlayer', $this->entityManagerMock);
        // Inject mocks via reflection
        $this->setPrivateProperty($game, 'player', $this->mockPlayer);
        $this->setPrivateProperty($game, 'dealer', $this->mockDealer);
        $this->setPrivateProperty($game, 'deck', $this->mockDeck);
        $this->setPrivateProperty($game, 'status', 'initial');
        $this->setPrivateProperty($game, 'activeHandIndex', 0);
        $expectedState = [
            'player' => [
                'name' => 'TestPlayer',
                'hands' => [],
                'balance' => 1000.00,
                'activeHandIndex' => 0
            ],
            'dealer' => ['hands' => [[]]],
            'deck' => [],
            'status' => 'initial',
        ];
        $this->assertEquals($expectedState, $game->getState());
    }
    /**
     * Test getState when the player and dealer have hands and there are
     * card remaining in the deck.
     */
    public function testGetStateWithPlayerAndDealerHandsAndDeck(): void
    {
        // Create card objects for player, dealer, and deck.
        $playerCard1 = new BlackJackGraphic('Hearts', 'King');
        $playerCard2 = new BlackJackGraphic('Diamonds', 'Ace');
        $dealerCard1 = new BlackJackGraphic('Spades', 'Queen');
        $dealerCard2 = new BlackJackGraphic('Clubs', 'Five');
        $deckCard1 = new BlackJackGraphic('Hearts', 'Two');
        $deckCard2 = new BlackJackGraphic('Clubs', 'Jack');
        // Setup player mock to return one hand with two cards and bet info.
        $this->mockPlayer->/** @scrutinizer ignore-call */method('getName')->willReturn('PlayerOne');
        $this->mockPlayer->method('getBalance')->willReturn(500);
        $this->mockPlayer->method('getHands')->willReturn([0 => [$playerCard1, $playerCard2]]);
        $this->mockPlayer->method('getBet')->with(0)->willReturn(50);
        $this->mockPlayer->method('getHandState')->with(0)->willReturn('playing');
        // Dealer mock returns one hand
        $this->mockDealer->method('getHands')->willReturn([[ $dealerCard1, $dealerCard2 ]]);
        // Deck mock return two cards
        $this->mockDeck->/** @scrutinizer ignore-call */method('getCards')->willReturn([$deckCard1, $deckCard2]);
        $game = new BlackJack('PlayerOne', $this->entityManagerMock);
        // Inject mocks
        $this->setPrivateProperty($game, 'player', $this->mockPlayer);
        $this->setPrivateProperty($game, 'dealer', $this->mockDealer);
        $this->setPrivateProperty($game, 'deck', $this->mockDeck);
        $this->setPrivateProperty($game, 'status', 'playing');
        $this->setPrivateProperty($game, 'activeHandIndex', 0);
        $expectedState = [
            'player' => [
                'name' => 'PlayerOne',
                'hands' => [
                    0 => [
                        'data' => [
                            ['suit' => 'Hearts', 'value' => 'King'],
                            ['suit' => 'Diamonds', 'value' => 'Ace']
                        ],
                        'bet' => 50.00,
                        'state' => 'playing'
                    ]
                ],
                'balance' => 500.00,
                'activeHandIndex' => 0
            ],
            'dealer' => [
                'hands' => [
                    [
                        ['suit' => 'Spades', 'value' => 'Queen'],
                        ['suit' => 'Clubs', 'value' => 'Five']
                    ]
                ]
            ],
            'deck' => [
                ['suit' => 'Hearts', 'value' => 'Two'],
                ['suit' => 'Clubs', 'value' => 'Jack']
            ],
            'status' => 'playing'
        ];
        $this->assertEquals($expectedState, $game->getState());
    }
}
