<?php

namespace App\Card;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for DeckOfCards class.
 */
class DeckOfCardsTest extends TestCase
{
    /**
     * Test deck initialization and initial card count.
     */
    public function testConstructAndGetCards(): void
    {
        $deck = new DeckOfCards();
        $this->assertInstanceOf(DeckOfCards::class, $deck);
        $this->assertCount(52, $deck->getCards());
        $this->assertEquals(52, $deck->count());

        $cards = $deck->getCards();
        $this->assertInstanceOf(CardGraphic::class, $cards[0]);
        $this->assertEquals('2♥', $cards[0]->getAsString());
    }

    /**
     * Test that shuffle changes the order of the deck.
     */
    public function testShuffle(): void
    {
        $deck = new DeckOfCards();
        $originalCards = $deck->getCards();

        $deck->shuffle();
        $shuffledCards = $deck->getCards();

        $this->assertCount(52, $shuffledCards);

        $this->assertNotEquals($originalCards, $shuffledCards, 'Shuffle did not change the order');
    }

    /**
     * Test drawing a single card decreases deck size and returns correct card.
     */
    public function testDraw(): void
    {
        $deck = new DeckOfCards();
        $card = $deck->draw();

        $this->assertInstanceOf(CardGraphic::class, $card);
        $this->assertEquals(51, $deck->count());

        for ($i = 0; $i < 51; $i++) {
            $deck->draw();
        }

        $this->assertNull($deck->draw());
    }

    /**
     * Test drawing multiple cards reduces deck count accordingly.
     */
    public function testDrawMany(): void
    {
        $deck = new DeckOfCards();
        $cards = $deck->drawMany(5);

        $this->assertCount(5, $cards);
        foreach ($cards as $card) {
            $this->assertInstanceOf(CardGraphic::class, $card);
        }

        $this->assertEquals(47, $deck->count());

        $deck->drawMany(47);
        $this->assertEmpty($deck->drawMany(1));
    }

    /**
     * Test sortedCards returns cards sorted by suit and value without changing original deck.
     */
    public function testSortedCards(): void
    {
        $deck = new DeckOfCards();
        $deck->shuffle();

        $sorted = $deck->sortedCards();

        $this->assertCount(52, $sorted);
        $this->assertEquals('2♥', $sorted[0]->getAsString());
        $this->assertEquals('A♠', $sorted[51]->getAsString());

        $this->assertEquals(52, $deck->count());
    }

    /**
     * Test count reflects the number of cards in deck.
     */
    public function testCount(): void
    {
        $deck = new DeckOfCards();
        $this->assertEquals(52, $deck->count());

        $deck->drawMany(10);
        $this->assertEquals(42, $deck->count());

        $deck->drawMany(42);
        $this->assertEquals(0, $deck->count());
    }
}
