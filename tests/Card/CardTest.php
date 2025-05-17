<?php

namespace App\Card;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for Card class.
 */
class CardTest extends TestCase
{
    /**
     * Test creating a Card and getting its properties.
     */
    public function testCreateCard(): void
    {
        $card = new Card("hearts", "A");
        $this->assertInstanceOf(Card::class, $card);
        $this->assertEquals("A", $card->getValue());
        $this->assertEquals("hearts", $card->getSuit());
        $this->assertEquals("A of hearts", $card->getAsString());
    }

    /**
     * Test different suits and values.
     */
    public function testDifferentSuitsAndValues(): void
    {
        $card = new Card("spades", "K");
        $this->assertEquals("K", $card->getValue());
        $this->assertEquals("spades", $card->getSuit());
        $this->assertEquals("K of spades", $card->getAsString());

        $card = new Card("diamonds", "2");
        $this->assertEquals("2", $card->getValue());
        $this->assertEquals("diamonds", $card->getSuit());
        $this->assertEquals("2 of diamonds", $card->getAsString());
    }

    /**
     * Test invalid suit throws exception.
     */
    public function testInvalidSuit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid suit: unknown");
        new Card("unknown", "A");
    }

    /**
     * Test invalid value throws exception.
     */
    public function testInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid value: X");
        new Card("hearts", "X");
    }
}
