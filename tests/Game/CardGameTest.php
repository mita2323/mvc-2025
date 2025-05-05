<?php

namespace App\Tests\Game;

use PHPUnit\Framework\TestCase;
use App\Game\CardGame;

/**
 * Test cases for the CardGame class.
 */
class CardGameTest extends TestCase
{
    public function testCreateCard(): void
    {
        $card = new CardGame('hearts', 'Q');
        $this->assertInstanceOf(CardGame::class, $card);
        $this->assertEquals('hearts', $card->getSuit());
        $this->assertEquals('Q', $card->getValue());
    }

    public function testToString(): void
    {
        $card = new CardGame('diamonds', '10');
        $this->assertEquals('10 of diamonds', $card->getAsString());
    }

    public function testNumericValue(): void
    {
        $card = new CardGame('spades', 'K');
        $this->assertEquals(10, $card->getNumValue());

        $card = new CardGame('spades', 'A');
        $this->assertEquals(11, $card->getNumValue());

        $card = new CardGame('spades', '7');
        $this->assertEquals(7, $card->getNumValue());
    }

    public function testGetAsString(): void
    {
        $card = new CardGame('hearts', 'K');
        $this->assertEquals('K of hearts', $card->getAsString());
    }
}
