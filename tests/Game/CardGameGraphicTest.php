<?php

namespace App\Tests\Game;

use PHPUnit\Framework\TestCase;
use App\Game\CardGameGraphic;

/**
 * Test cases for the CardGameGraphic class.
 */
class CardGameGraphicTest extends TestCase
{
    public function testCreateCard(): void
    {
        $card = new CardGameGraphic('hearts', 'A');
        $this->assertInstanceOf(CardGameGraphic::class, $card);
        $this->assertEquals('hearts', $card->getSuit());
        $this->assertEquals('A', $card->getValue());
    }

    public function testGraphicCardToString(): void
    {
        $card = new CardGameGraphic('clubs', 'J');
        $this->assertEquals('J♣', $card->getAsString());

        $card = new CardGameGraphic('hearts', 'A');
        $this->assertEquals('A♥', $card->getAsString());
    }

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

    public function testGetAsString(): void
    {
        $card = new CardGameGraphic('spades', 'A');
        $this->assertEquals('A♠', $card->getAsString());
    }

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
