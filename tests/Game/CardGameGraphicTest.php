<?php

namespace App\Tests\Game;

use PHPUnit\Framework\TestCase;
use App\Game\CardGameGraphic;

/**
 * Test cases for the CardGameGraphic class.
 */
class CardGameGraphicTest extends TestCase
{
    /**
     * Test create card with suit and value.
     */
    public function testCreateCard(): void
    {
        $card = new CardGameGraphic('hearts', 'A');
        $this->assertInstanceOf(CardGameGraphic::class, $card);
        $this->assertEquals('hearts', $card->getSuit());
        $this->assertEquals('A', $card->getValue());
    }

    /**
     * Test card string representation.
     */
    public function testGraphicCardToString(): void
    {
        $card = new CardGameGraphic('clubs', 'J');
        $this->assertEquals('J♣', $card->getAsString());

        $card = new CardGameGraphic('hearts', 'A');
        $this->assertEquals('A♥', $card->getAsString());
    }

    /**
     * Test string representation for all suits (hearts, diamonds, clubs, spades).
     */
    public function testToStringAllSuits(): void
    {
        $cards = [
            ['hearts', 'A', 'A♥'],
            ['diamonds', 'Q', 'Q♦'],
            ['clubs', 'J', 'J♣'],
            ['spades', 'K', 'K♠'],
        ];

        foreach ($cards as $card) {
            $cardObject = new CardGameGraphic($card[0], $card[1]);
            $this->assertEquals($card[2], $cardObject->getAsString());
        }
    }

    /**
     * Test getting the string representation of a card.
     */
    public function testGetAsString(): void
    {
        $card = new CardGameGraphic('spades', 'A');
        $this->assertEquals('A♠', $card->getAsString());
    }

    /**
     * Test the unicode representation of the card's suit.
     */
    public function testGetSuitUnicode(): void
    {
        $cards = [
            ['hearts', 'A', '♥'],
            ['diamonds', 'A', '♦'],
            ['clubs', 'A', '♣'],
            ['spades', 'A', '♠'],
            ['invalid_suit', 'A', ''],
        ];

        foreach ($cards as $card) {
            $cardObject = new CardGameGraphic($card[0], $card[1]);
            $this->assertEquals($card[2], $cardObject->getSuitUnicode());
        }
    }
}
