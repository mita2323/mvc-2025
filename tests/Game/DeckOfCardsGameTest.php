<?php

namespace App\Tests\Game;

use PHPUnit\Framework\TestCase;
use App\Game\DeckOfCardsGame;

/**
 * Test cases for the DeckOfCardsGame class.
 */
class DeckOfCardsGameTest extends TestCase
{
    public function testDeckHas52CardsInitially(): void
    {
        $deck = new DeckOfCardsGame();
        $this->assertCount(52, $deck->getCards());
    }

    public function testShuffleDeck(): void
    {
        $deck = new DeckOfCardsGame();
        $original = $deck->getCards();
        $deck->shuffle();
        $shuffled = $deck->getCards();
        $this->assertCount(52, $shuffled);
        $this->assertNotEquals($original, $shuffled);
    }

    public function testDrawOneCard(): void
    {
        $deck = new DeckOfCardsGame();
        $card = $deck->draw();
        $this->assertNotNull($card);
        $this->assertCount(51, $deck->getCards());
    }

    public function testDrawManyCards(): void
    {
        $deck = new DeckOfCardsGame();
        $drawn = $deck->drawMany(5);
        $this->assertCount(5, $drawn);
        $this->assertCount(47, $deck->getCards());
    }

    public function testSortedCards(): void
    {
        $deck = new DeckOfCardsGame();
        $sorted = $deck->sortedCards();
        $this->assertCount(52, $sorted);
        $this->assertEquals('2', $sorted[0]->getValue());
    }

    public function testDrawWhenDeckIsEmpty(): void
    {
        $deck = new DeckOfCardsGame();
        foreach ($deck->getCards() as $card) {
            $deck->draw();
        }
        $this->assertNull($deck->draw());
    }

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
