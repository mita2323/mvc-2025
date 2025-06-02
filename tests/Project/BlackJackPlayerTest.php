<?php

namespace App\Tests\Project;

use PHPUnit\Framework\TestCase;
use App\Project\BlackJackPlayer;
use App\Project\BlackJackGraphic;
use App\Entity\Player as PlayerEntity;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test cases for the BlackJackPlayer class.
 */
class BlackJackPlayerTest extends TestCase
{
    /**
     * @var MockObject&PlayerEntity
     */
    private $mockPlayerEntity;

    /**
     * Sets  up the test environment before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockPlayerEntity = $this->createMock(PlayerEntity::class);
    }

    /**
     * Tests the constructor of the BlackJackPlayer class.
     */
    public function testConstruct(): void
    {
        $player = new BlackJackPlayer("Test Player");
        $this->assertEquals("Test Player", $player->getName());
        $this->assertEmpty($player->getHands());
        $this->assertEmpty($player->getBets());
        $this->assertEmpty($player->getOriginalBets());

        $playerWithEntity = new BlackJackPlayer("Entity Player", $this->mockPlayerEntity);
        $this->assertEquals("Entity Player", $playerWithEntity->getName());
        $this->assertSame($this->mockPlayerEntity, $playerWithEntity->getEntity());
    }

    /**
     * Tests the getBalance and setBalance methods.
     */
    public function testGetAndSetBalance(): void
    {
        $this->mockPlayerEntity = $this->createMock(PlayerEntity::class);
        $this->mockPlayerEntity->method('getBalance')->willReturn(1000.0);

        $player = new BlackJackPlayer("Test Player", $this->mockPlayerEntity);
        $this->assertEquals(1000, $player->getBalance());

        $this->mockPlayerEntity->expects($this->once())
                                ->method('setBalance')
                                ->with(1500);
        $player->setBalance(1500);

        $playerWithoutEntity = new BlackJackPlayer("No Entity");
        $this->assertNull($playerWithoutEntity->getEntity());

        /** @phpstan-ignore-next-line instanceof.alwaysTrue */
        if ($this->mockPlayerEntity instanceof PlayerEntity) {
            $playerWithoutEntity->setEntity($this->mockPlayerEntity);
            $this->assertSame($this->mockPlayerEntity, $playerWithoutEntity->getEntity());
        }
    }

    /**
     * Tests the getService and setService methods.
     */
    public function testGetAndSetEntity(): void
    {
        $player = new BlackJackPlayer("Test Player");
        $this->assertNull($player->getEntity());

        $player->setEntity($this->mockPlayerEntity);
        $this->assertSame($this->mockPlayerEntity, $player->getEntity());
    }

    /**
     * Tests that the cards are correctly added to the specific hand and that
     * new hands are initializes as 'active' when a card is added to a new hand index.
     */
    public function testAddCard(): void
    {
        $player = new BlackJackPlayer("Test Player");
        $card1 = new BlackJackGraphic('H', 'A');
        $card2 = new BlackJackGraphic('D', 'K');

        $player->addCard($card1);
        $this->assertCount(1, $player->getHand(0));
        $this->assertSame($card1, $player->getHand(0)[0]);
        $this->assertEquals('active', $player->getHandState(0));

        $player->addCard($card2, 0);
        $this->assertCount(2, $player->getHand(0));
        $this->assertSame($card2, $player->getHand(0)[1]);

        $card3 = new BlackJackGraphic('S', 'Q');
        $player->addCard($card3, 1);
        $this->assertCount(1, $player->getHand(1));
        $this->assertSame($card3, $player->getHand(1)[0]);
        $this->assertEquals('active', $player->getHandState(1));
    }

    /**
     * Tests that all hands are correctly returned as an array of arrays.
     */
    public function testGetHands(): void
    {
        $player = new BlackJackPlayer("Test Player");
        $card1 = new BlackJackGraphic('H', 'A');
        $card2 = new BlackJackGraphic('D', 'K');
        $card3 = new BlackJackGraphic('S', 'Q');

        $player->addCard($card1, 0);
        $player->addCard($card2, 0);
        $player->addCard($card3, 1);

        $hands = $player->getHands();
        $this->assertCount(2, $hands);
        $this->assertCount(2, $hands[0]);
        $this->assertCount(1, $hands[1]);
    }

    /**
     * Tests that a specific hand can be retrieved but its index
     * and that an empty array is returned for non-existent hands.
     */
    public function testGetHand(): void
    {
        $player = new BlackJackPlayer("Test Player");
        $card1 = new BlackJackGraphic('H', 'A');
        $player->addCard($card1, 0);

        $hand = $player->getHand(0);
        $this->assertCount(1, $hand);
        $this->assertSame($card1, $hand[0]);

        $this->assertEmpty($player->getHand(99));
    }

    /**
     * Tests that the player's name is correctly returned.
     */
    public function testGetName(): void
    {
        $player = new BlackJackPlayer("TestName");
        $this->assertEquals("TestName", $player->getName());
    }

    /**
     * Tests that the bets are correctly placed, updated and deducted from the balance.
     */
    public function testPlaceBet(): void
    {
        $this->mockPlayerEntity->/** @scrutinizer ignore-call */method('getBalance')
                                    ->willReturnOnConsecutiveCalls(
                                        1000.0,
                                        1000.0,
                                        900.0,
                                        900.0,
                                        850.0
                                    );

       /** @scrutinizer ignore-deprecated */$this->mockPlayerEntity->expects($this->exactly(2))
                                ->method('setBalance')
                                ->withConsecutive([900], [850]);

        $player = new BlackJackPlayer("Test Player", $this->mockPlayerEntity);

        $this->assertTrue($player->placeBet(100, 0));
        $this->assertEquals(100, $player->getBet(0));
        $this->assertEquals(100, $player->getOriginalBet(0));

        $this->assertTrue($player->placeBet(50, 0));
        $this->assertEquals(150, $player->getBet(0));
        $this->assertEquals(150, $player->getOriginalBet(0));

        $this->assertFalse($player->placeBet(2000, 0));
        $this->assertEquals(150, $player->getBet(0));

        $this->assertFalse($player->placeBet(0, 0));
    }

    /**
     * Tests that a specific bet for a hand can be set, and that it also updates
     * the original bet.
     */
    public function testSetBet(): void
    {
        $player = new BlackJackPlayer("Test Player");
        $player->setBet(200, 0);
        $this->assertEquals(200, $player->getBet(0));
        $this->assertEquals(200, $player->getOriginalBet(0));

        $player->setBet(75, 1);
        $this->assertEquals(75, $player->getBet(1));
    }

    /**
     * Tests that multiple bets can be set for different hands at the same time.
     */
    public function testSetAllBets(): void
    {
        $player = new BlackJackPlayer("Test Player");
        $bets = [0 => 100, 1 => 200];
        $player->setAllBets($bets);
        $this->assertEquals(100, $player->getBet(0));
        $this->assertEquals(200, $player->getBet(1));
        $this->assertEquals(100, $player->getOriginalBet(0));
        $this->assertEquals(200, $player->getOriginalBet(1));
    }

    /**
     * Tests that winning a bet correctly adds the winnings to the player's balance.
     */
    public function testWinBet(): void
    {
        $this->mockPlayerEntity = $this->createMock(PlayerEntity::class);
        $this->mockPlayerEntity->method('getBalance')
                                ->willReturn(1000.0);

        $this->mockPlayerEntity->expects($this->once())
                                ->method('setBalance')
                                ->with(1100.0);

        $playerWithEntity = new BlackJackPlayer("Test Player", $this->mockPlayerEntity);
        $playerWithEntity->setBet(100, 0);
        $this->assertEquals(100, $playerWithEntity->getBet(0));

        $playerWithEntity->winBet(100, 0);
        $this->assertEquals(0, $playerWithEntity->getBet(0));

        $playerWithoutEntity = new BlackJackPlayer("No Entity");
        $playerWithoutEntity->setBet(50, 0);
        $this->assertEquals(50, $playerWithoutEntity->getBet(0));

        $playerWithoutEntity->winBet(50, 0);
        $this->assertEquals(0, $playerWithoutEntity->getBet(0));
    }

    /**
     * Tests that the method returns all current bets as an array with hand indexes and amounts.
     */
    public function testGetBets(): void
    {
        $player = new BlackJackPlayer("Test Player");
        $player->setBet(100, 0);
        $player->setBet(50, 1);
        $this->assertEquals([0 => 100, 1 => 50], $player->getBets());
    }

    /**
     * Tests that the original bets can be set for all hands.
     */
    public function testSetOriginalBets(): void
    {
        $player = new BlackJackPlayer("Test Player");
        $bets = [0 => 50, 1 => 75];
        $player->setOriginalBets($bets);
        $this->assertEquals($bets, $player->getOriginalBets());

        $stringBets = [0 => '100', 1 => '200'];
        $intBets = array_map('intval', $stringBets);
        $player->setOriginalBets($intBets);
        $this->assertEquals([0 => 100, 1 => 200], $player->getOriginalBets());
    }

    /**
     * Tests that the bet for a specific hand can be retrieved.
     */
    public function testGetBet(): void
    {
        $player = new BlackJackPlayer("Test Player");
        $player->setBet(100, 0);
        $this->assertEquals(100, $player->getBet(0));
        $this->assertEquals(0, $player->getBet(1));
    }

    /**
     * Tests the 'getScore' method.
     * @param array<int, array{0: string, 1: string}> $cards The cards to add to the player's hand.
     * @param int $expectedScore The expected score for the hand.
     * @return void
     * @dataProvider getScoreDataProvider
     */
    public function testGetScore(array $cards, int $expectedScore): void
    {
        $player = new BlackJackPlayer("Test Player");
        foreach ($cards as $cardData) {
            $player->addCard(new BlackJackGraphic($cardData[0], $cardData[1]));
        }
        $this->assertEquals($expectedScore, $player->getScore());
    }

    /**
     * Provides data for testing the 'getScore' method.
     * @return array<string, array{0: array<int, array{0: string, 1: string}>, 1: int}>
     */
    public static function getScoreDataProvider(): array
    {
        return [
            'empty hand' => [[], 0],
            'single card (numeric 7)' => [[['H', '7']], 7],
            'single card (numeric 10)' => [[['D', '10']], 10],
            'single card (face K)' => [[['S', 'K']], 10],
            'single card (face J)' => [[['C', 'J']], 10],
            'single card (face Q)' => [[['H', 'Q']], 10],
            'single card (Ace)' => [[['C', 'A']], 11],
            'normal hand' => [[['H', '5'], ['D', '8']], 13],
            'face cards' => [[['S', 'K'], ['D', 'Q']], 20],
            'blackjack' => [[['H', 'A'], ['D', 'K']], 21],
            'soft 17' => [[['H', 'A'], ['D', '6']], 17],
            'soft 21' => [[['H', 'A'], ['D', 'J'], ['C', 'Q']], 21],
            'two aces' => [[['H', 'A'], ['D', 'A']], 12],
            'ace and high cards' => [[['H', 'A'], ['D', '10'], ['C', '5']], 16],
            'multiple aces' => [[['H', 'A'], ['D', 'A'], ['C', 'A'], ['S', '9']], 12],
            'bust' => [[['H', '10'], ['D', 'J'], ['C', 'K']], 30],
        ];
    }

    /**
     * Tests that all hands, bets, original bets, and hand states are reset to their
     * initial empty or 'finished' states.
     */
    public function testClearHands(): void
    {
        $player = new BlackJackPlayer("Test Player");
        $player->addCard(new BlackJackGraphic('H', 'A'), 0);
        $player->placeBet(100, 0);
        $player->initializeNewHand(1);
        $player->setHandState(0, 'busted');

        $this->assertNotEmpty($player->getHands());
        $this->assertNotEmpty($player->getBets());
        $this->assertEquals('busted', $player->getHandState(0));

        $player->clearHands();
        $this->assertEmpty($player->getHands());
        $this->assertEmpty($player->getBets());
        $this->assertEmpty($player->getOriginalBets());
        $this->assertEquals('finished', $player->getHandState(0));
    }

    /**
     * Tests  that a new  hand is correctly initialized with an empty card array, a zero bet,
     * and an 'active' state.
     */
    public function testInitializeNewHand(): void
    {
        $player = new BlackJackPlayer("Test Player");
        $player->initializeNewHand(0);
        $this->assertEmpty($player->getHand(0));
        $this->assertEquals(0, $player->getBet(0));
        $this->assertEquals('active', $player->getHandState(0));
        $this->assertEquals(0, $player->getOriginalBet(0));

        $player->addCard(new BlackJackGraphic('H', '7'), 0);
        $player->initializeNewHand(1);
        $this->assertCount(1, $player->getHand(0));
        $this->assertEmpty($player->getHand(1));
    }

    /**
     * Tests that the method correctly identifies a black.
     */
    public function testIsBlackjack(): void
    {
        $player = new BlackJackPlayer("Test Player");

        $this->assertFalse($player->isBlackjack(0));

        $player->addCard(new BlackJackGraphic('H', 'A'), 0);
        $this->assertFalse($player->isBlackjack(0));

        $player->addCard(new BlackJackGraphic('D', '5'), 0);
        $this->assertFalse($player->isBlackjack(0));

        $player->clearHands();
        $player->addCard(new BlackJackGraphic('H', 'A'), 0);
        $player->addCard(new BlackJackGraphic('D', 'K'), 0);
        $this->assertTrue($player->isBlackjack(0));

        $player->clearHands();
        $player->addCard(new BlackJackGraphic('H', '7'), 0);
        $player->addCard(new BlackJackGraphic('D', '7'), 0);
        $player->addCard(new BlackJackGraphic('S', '7'), 0);
        $this->assertFalse($player->isBlackjack(0));
    }

    /**
     * Tests that hand states can be correctly retrieved and set.
     */
    public function testGetAndSetHandState(): void
    {
        $player = new BlackJackPlayer("Test Player");
        $player->initializeNewHand(0);

        $this->assertEquals('active', $player->getHandState(0));
        $this->assertEquals('finished', $player->getHandState(1));

        $player->setHandState(0, 'stood');
        $this->assertEquals('stood', $player->getHandState(0));

        $player->setHandState(0, 'busted');
        $this->assertEquals('busted', $player->getHandState(0));

        $player->setHandState(0, 'finished');
        $this->assertEquals('finished', $player->getHandState(0));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid hand state: invalid_state");
        $player->setHandState(0, 'invalid_state');
    }

    /**
     * Tests that calling 'stand' on a hand correctly sets its state to 'stood'.
     */
    public function testStand(): void
    {
        $player = new BlackJackPlayer("Test Player");
        $player->initializeNewHand(0);
        $player->stand(0);
        $this->assertEquals('stood', $player->getHandState(0));
    }

    /**
     * Tests that calling 'bust' on a hand correctly sets it state to 'busted'.
     */
    public function testBust(): void
    {
        $player = new BlackJackPlayer("Test Player");
        $player->initializeNewHand(0);
        $player->bust(0);
        $this->assertEquals('busted', $player->getHandState(0));
    }

    /**
     * Tests that the method correctly returns true only when a hands state is 'active'.
     */
    public function testIsHandActive(): void
    {
        $player = new BlackJackPlayer("Test Player");

        $this->assertFalse($player->isHandActive(0));

        $player->initializeNewHand(0);
        $this->assertTrue($player->isHandActive(0));

        $player->setHandState(0, 'stood');
        $this->assertFalse($player->isHandActive(0));

        $player->setHandState(0, 'busted');
        $this->assertFalse($player->isHandActive(0));

        $player->setHandState(0, 'finished');
        $this->assertFalse($player->isHandActive(0));
    }

    /**
     * Tests that the entire set of hands can be replaced with a new array.
     */
    public function testSetHands(): void
    {
        $player = new BlackJackPlayer("Test Player");
        $card1 = new BlackJackGraphic('H', '2');
        $card2 = new BlackJackGraphic('D', '3');
        $hands = [
            0 => [$card1],
            1 => [$card2]
        ];
        $player->setHands($hands);
        $this->assertSame($hands, $player->getHands());
    }

    /**
     * Tests that multiple hand states can be set simultaneously for different hands.
     */
    public function testSetAllHandStates(): void
    {
        $player = new BlackJackPlayer("Test Player");
        $states = [
            0 => 'stood',
            1 => 'busted'
        ];
        $player->setAllHandStates($states);
        $this->assertEquals('stood', $player->getHandState(0));
        $this->assertEquals('busted', $player->getHandState(1));
    }

    /**
     * Helper method to call private/protected methods for testing.
     * @param object $object The object instance.
     * @param string $methodName The name of the private/protected method.
     * @param array<int, mixed> $parameters Parameters to pass to the method.
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
     * Tests that the method correctly returns the integer value for different card ranks.
     */
    public function testGetCardValuePrivate(): void
    {
        $player = new BlackJackPlayer("Test");

        $this->assertEquals(10, $this->callPrivateMethod($player, 'getCardValue', ['J']));
        $this->assertEquals(10, $this->callPrivateMethod($player, 'getCardValue', ['Q']));
        $this->assertEquals(10, $this->callPrivateMethod($player, 'getCardValue', ['K']));

        $this->assertEquals(11, $this->callPrivateMethod($player, 'getCardValue', ['A']));

        $this->assertEquals(2, $this->callPrivateMethod($player, 'getCardValue', ['2']));
        $this->assertEquals(7, $this->callPrivateMethod($player, 'getCardValue', ['7']));
        $this->assertEquals(10, $this->callPrivateMethod($player, 'getCardValue', ['10']));
    }

    /**
     * Tests the splitHand method under various conditions.
     */
    public function testSplitHand(): void
    {
        $player = new BlackJackPlayer("Test Player", $this->mockPlayerEntity);

        // Test 1: Hand does not exist
        $this->assertFalse($player->splitHand(0), "Should fail if hand does not exist");

        // Test 2: Hand has fewer than 2 cards
        $player->addCard(new BlackJackGraphic('H', 'A'), 0);
        $this->assertFalse($player->splitHand(0), "Should fail if hand has fewer than 2 cards");

        // Test 3: Hand has more than 2 cards
        $player->clearHands();
        $player->addCard(new BlackJackGraphic('H', 'A'), 0);
        $player->addCard(new BlackJackGraphic('H', 'A'), 0);
        $player->addCard(new BlackJackGraphic('D', 'K'), 0);
        $this->assertFalse($player->splitHand(0), "Should fail if hand has more than 2 cards");

        // Test 4: Cards have different ranks
        $player->clearHands();
        $player->addCard(new BlackJackGraphic('H', 'A'), 0);
        $player->addCard(new BlackJackGraphic('D', 'K'), 0);
        $this->assertFalse($player->splitHand(0), "Should fail if cards have different ranks");

        // Test 5: Hand is not active
        $player->clearHands();
        $player->addCard(new BlackJackGraphic('H', 'A'), 0);
        $player->addCard(new BlackJackGraphic('H', 'A'), 0);
        $player->setHandState(0, 'stood');
        $this->assertFalse($player->splitHand(0), "Should fail if hand is not active");

        // Test 6: Insufficient balance
        $this->mockPlayerEntity->expects($this->any())
                            ->method('getBalance')
                            ->willReturn(50.0);
        $player->clearHands();
        $player->addCard(new BlackJackGraphic('H', 'A'), 0);
        $player->addCard(new BlackJackGraphic('H', 'A'), 0);
        $this->assertFalse($player->placeBet(100, 0), "placeBet should fail with insufficient balance");
        $player->setBet(100, 0);
        $this->assertFalse($player->splitHand(0), "Should fail if balance is insufficient");

        // Test 7: Successful split
        $this->mockPlayerEntity = $this->createMock(PlayerEntity::class);
        $this->mockPlayerEntity->expects($this->exactly(4))
                            ->method('getBalance')
                            ->willReturnOnConsecutiveCalls(200.0, 200.0, 100.0, 100.0);
        /** @scrutinizer ignore-deprecated */$this->mockPlayerEntity->/** @scrutinizer ignore-deprecated */expects($this->exactly(2))
                            ->method('setBalance')
                            ->withConsecutive([100.0], [0.0]);
        $player = new BlackJackPlayer("Test Player", $this->mockPlayerEntity);
        $player->clearHands();
        $player->addCard(new BlackJackGraphic('H', 'A'), 0);
        $player->addCard(new BlackJackGraphic('D', 'A'), 0);
        $this->assertTrue($player->placeBet(100, 0), "placeBet should succeed with sufficient balance");
        $this->assertTrue($player->splitHand(0), "Should succeed with valid conditions");
        $this->assertCount(1, $player->getHand(0), "Original hand should have 1 card");
        $this->assertCount(1, $player->getHand(1), "New hand should have 1 card");
        $this->assertEquals(100, $player->getBet(0), "Original hand bet should remain");
        $this->assertEquals(100, $player->getBet(1), "New hand should have same bet");
        $this->assertEquals('active', $player->getHandState(0), "Original hand should be active");
        $this->assertEquals('active', $player->getHandState(1), "New hand should be active");
    }
}