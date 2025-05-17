<?php
namespace App\Card;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for CardGraphic class.
 */
class CardGraphicTest extends TestCase
{
    /**
     * Test that known suits and values return correct string representations.
     */
    public function testGetAsString(): void
    {
        $card = new CardGraphic("hearts", "A");
        $this->assertEquals("A♥", $card->getAsString());

        $card = new CardGraphic("spades", "K");
        $this->assertEquals("K♠", (string) $card);

        $card = new CardGraphic("diamonds", "Q");
        $this->assertEquals("Q♦", $card->getAsString());

        $card = new CardGraphic("clubs", "J");
        $this->assertEquals("J♣", (string) $card);
    }

    /**
     * Test getAsString with invalid suit.
     */
    public function testGetAsStringInvalidSuit(): void
    {
        $card = $this->getMockBuilder(CardGraphic::class)
            ->setConstructorArgs(['hearts', 'A'])
            ->onlyMethods(['getRawSuit'])
            ->getMock();
        $card->method('getRawSuit')->willReturn('unknown');

        $this->assertEquals('A', $card->getAsString());
    }
}
