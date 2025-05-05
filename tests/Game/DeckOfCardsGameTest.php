<?php

namespace App\Tests\Game;

use PHPUnit\Framework\TestCase;
use App\Game\DeckOfCardsGame;

/**
 * Test cases for the DeckOfCardsGame class.
 */
class DeckOfCardsGameTest extends TestCase
{
    /**
     * Test that the deck initially has 52 cards.
     */
    public function testDeckHas52CardsInitially(): void
    {
        $deck = new DeckOfCardsGame();
        $this->assertCount(52, $deck->getCards());
    }

    /**
     * Test that shuffling the deck changes the order of the cards.
     */
    public function testShuffleDeck(): void
    {
        $deck = new DeckOfCardsGame();
        $original = $deck->getCards();
        $deck->shuffle();
        $shuffled = $deck->getCards();
        $this->assertCount(52, $shuffled);
        $this->assertNotEquals($original, $shuffled);
    }

    /**
     * Test that drawing one card reduces the deck by one.
     */
    public function testDrawOneCard(): void
    {
        $deck = new DeckOfCardsGame();
        $card = $deck->draw();
        $this->assertNotNull($card);
        $this->assertCount(51, $deck->getCards());
    }

    /**
     * Test that drawing multiple cards reduces the deck by the correct number.
     */
    public function testDrawManyCards(): void
    {
        $deck = new DeckOfCardsGame();
        $drawn = $deck->drawMany(5);
        $this->assertCount(5, $drawn);
        $this->assertCount(47, $deck->getCards());
    }

    /**
     * Test that the deck returns sorted cards in the correct order.
     */
    public function testSortedCards(): void
    {
        $deck = new DeckOfCardsGame();
        $sorted = $deck->sortedCards();
        $this->assertCount(52, $sorted);
        $this->assertEquals('2', $sorted[0]->getValue());
    }

    /**
     * Test that drawing from an empty deck returns null.
     */
    public function testDrawWhenDeckIsEmpty(): void
    {
        $deck = new DeckOfCardsGame();
        foreach ($deck->getCards() as $card) {
            $deck->draw();
        }
        $this->assertNull($deck->draw());
    }

    /**
     * Test that the deck count reflects the number of remaining cards.
     */
    public function testCount(): void
    {
        $deck = new DeckOfCardsGame();
        $this->assertEquals(52, $deck->count());
        $deck->draw();
        $this->assertEquals(51, $deck->count());
        $deck->drawMany(5);
        $this->assertEquals(46, $deck->count());
    }
}
