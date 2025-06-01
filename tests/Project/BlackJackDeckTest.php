<?php

namespace App\Project;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for the BlackJackDeck class.
 */
class BlackJackDeckTest extends TestCase
{
    /**
     * Test the constructor to ensure a full deck is created.
     */
    public function testConstructorCreatesFullDeck(): void
    {
        $deck = new BlackJackDeck();
        $cards = $deck->getCards();

        $this->assertCount(52, $cards, "The deck should contain 52 cards after construction.");

        $foundAceOfHearts = false;
        $foundTwoOfClubs = false;
        $foundKingOfSpades = false;

        foreach ($cards as $card) {
            $this->assertInstanceOf(BlackJackGraphic::class, $card, "Each element in the deck should be a BlackJackGraphic object.");

            if ($card->getSuit() === 'hearts' && $card->getRank() === 'A') {
                $foundAceOfHearts = true;
                $this->assertEquals(11, $card->getValue(), "Ace of Hearts should have value 11.");
            }
            if ($card->getSuit() === 'clubs' && $card->getRank() === '2') {
                $foundTwoOfClubs = true;
                $this->assertEquals(2, $card->getValue(), "Two of Clubs should have value 2.");
            }
            if ($card->getSuit() === 'spades' && $card->getRank() === 'K') {
                $foundKingOfSpades = true;
                $this->assertEquals(10, $card->getValue(), "King of Spades should have value 10.");
            }
        }

        $this->assertTrue($foundAceOfHearts, "Deck should contain an Ace of Hearts.");
        $this->assertTrue($foundTwoOfClubs, "Deck should contain a Two of Clubs.");
        $this->assertTrue($foundKingOfSpades, "Deck should contain a King of Spades.");
    }

    /**
     * Test getCards method.
     */
    public function testGetCardsReturnsAllCards(): void
    {
        $deck = new BlackJackDeck();
        $cards = $deck->getCards();
        $this->assertCount(52, $cards);
    }

    /**
     * Test shuffle method.
     */
    public function testShuffleChangesCardOrder(): void
    {
        $deck = new BlackJackDeck();
        $initialCards = $deck->getCards();

        $deck->shuffle();
        $shuffledCards = $deck->getCards();

        $this->assertNotSame($initialCards, $shuffledCards, "Shuffling should change the array instance.");
        $this->assertNotEquals($initialCards, $shuffledCards, "Shuffled deck should not have the same order as the initial deck (statistically likely).");
        $this->assertCount(52, $shuffledCards, "Shuffling should not change the number of cards.");

        $initialCardStrings = array_map(fn ($card) => (string)$card, $initialCards);
        $shuffledCardStrings = array_map(fn ($card) => (string)$card, $shuffledCards);
        sort($initialCardStrings);
        sort($shuffledCardStrings);
        $this->assertEquals($initialCardStrings, $shuffledCardStrings, "Shuffling should retain all original cards.");
    }

    /**
     * Test draw method.
     */
    public function testDrawRemovesCardFromDeck(): void
    {
        $deck = new BlackJackDeck();
        $initialCount = count($deck->getCards());

        $drawnCard = $deck->draw();
        $this->assertInstanceOf(BlackJackGraphic::class, $drawnCard, "Drawing a card should return a BlackJackGraphic object.");
        $this->assertCount($initialCount - 1, $deck->getCards(), "Deck count should decrease by one after drawing.");


        for ($i = 0; $i < $initialCount - 1; $i++) {
            $deck->draw();
        }
        $this->assertCount(0, $deck->getCards(), "Deck should be empty after drawing all cards.");

        $emptyDraw = $deck->draw();
        $this->assertNull($emptyDraw, "Drawing from an empty deck should return null.");
    }

    /**
     * Test setCards method.
     */
    public function testSetCardsWithBlackJackGraphicObjects(): void
    {
        $deck = new BlackJackDeck();
        $this->assertCount(52, $deck->getCards());

        $card1 = new BlackJackGraphic('hearts', 'A');
        $card2 = new BlackJackGraphic('spades', 'K');
        $newCards = [$card1, $card2];

        $deck->setCards($newCards);
        $this->assertCount(2, $deck->getCards(), "setCards should replace the deck with the provided cards.");
        $this->assertSame($card1, $deck->getCards()[0], "First card should be the provided BlackJackGraphic object.");
        $this->assertSame($card2, $deck->getCards()[1], "Second card should be the provided BlackJackGraphic object.");
    }

    /**
     * Test setCards method with array data for cards.
     */
    public function testSetCardsWithArrayData(): void
    {
        $deck = new BlackJackDeck();
        $this->assertCount(52, $deck->getCards());

        $cardData1 = ['suit' => 'clubs', 'rank' => 'Q', 'value' => 10];
        $cardData2 = ['suit' => 'diamonds', 'rank' => 'J', 'value' => 10];

        $newCardsData = [$cardData1, $cardData2];

        $deck->setCards($newCardsData);
        $this->assertCount(2, $deck->getCards(), "setCards should create cards from array data.");

        $cardFromDeck1 = $deck->getCards()[0];
        $this->assertInstanceOf(BlackJackGraphic::class, $cardFromDeck1);
        $this->assertEquals('clubs', $cardFromDeck1->getSuit());
        $this->assertEquals('Q', $cardFromDeck1->getRank());
        $this->assertEquals(10, $cardFromDeck1->getValue());

        $cardFromDeck2 = $deck->getCards()[1];
        $this->assertInstanceOf(BlackJackGraphic::class, $cardFromDeck2);
        $this->assertEquals('diamonds', $cardFromDeck2->getSuit());
        $this->assertEquals('J', $cardFromDeck2->getRank());
        $this->assertEquals(10, $cardFromDeck2->getValue());
    }

    /**
     * Test setCards method with a mix of BlackJackGraphic objects and array data.
     */
    public function testSetCardsWithMixedData(): void
    {
        $deck = new BlackJackDeck();
        $cardObj = new BlackJackGraphic('spades', '9');
        $cardData = ['suit' => 'hearts', 'rank' => 'A', 'value' => 11];

        $mixedCards = [$cardObj, $cardData];
        $deck->setCards($mixedCards);

        $this->assertCount(2, $deck->getCards());
        $this->assertSame($cardObj, $deck->getCards()[0]);

        $createdCard = $deck->getCards()[1];
        $this->assertInstanceOf(BlackJackGraphic::class, $createdCard);
        $this->assertEquals('hearts', $createdCard->getSuit());
        $this->assertEquals('A', $createdCard->getRank());
        $this->assertEquals(11, $createdCard->getValue());
    }

    /**
     * Test setCards with an empty array.
     */
    public function testSetCardsWithEmptyArray(): void
    {
        $deck = new BlackJackDeck();
        $this->assertCount(52, $deck->getCards());

        $deck->setCards([]);
        $this->assertCount(0, $deck->getCards(), "Setting cards with an empty array should result in an empty deck.");
    }
}
