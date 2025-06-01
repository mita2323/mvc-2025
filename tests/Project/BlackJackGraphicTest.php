<?php

namespace App\Project;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for the BlackJackGraphic class.
 */
class BlackJackGraphicTest extends TestCase
{
    /**
     * Test the constructor and basic getters.
     */
    public function testConstructorAndGetters(): void
    {
        $card = new BlackJackGraphic('hearts', 'K');
        $this->assertInstanceOf(BlackJackGraphic::class, $card);
        $this->assertEquals('hearts', $card->getSuit());
        $this->assertEquals('K', $card->getRank());
        $this->assertEquals(10, $card->getValue());

        $card2 = new BlackJackGraphic('spades', 'A');
        $this->assertEquals('spades', $card2->getSuit());
        $this->assertEquals('A', $card2->getRank());
        $this->assertEquals(11, $card2->getValue());

        $card3 = new BlackJackGraphic('clubs', '7');
        $this->assertEquals('clubs', $card3->getSuit());
        $this->assertEquals('7', $card3->getRank());
        $this->assertEquals(7, $card3->getValue());
    }

    /**
     * Test determineValue method for various ranks.
     */
    public function testDetermineValue(): void
    {
        $cardJ = new BlackJackGraphic('diamonds', 'J');
        $this->assertEquals(10, $cardJ->getValue());

        $cardQ = new BlackJackGraphic('diamonds', 'Q');
        $this->assertEquals(10, $cardQ->getValue());

        $cardK = new BlackJackGraphic('diamonds', 'K');
        $this->assertEquals(10, $cardK->getValue());

        $cardA = new BlackJackGraphic('hearts', 'A');
        $this->assertEquals(11, $cardA->getValue());

        $card2 = new BlackJackGraphic('spades', '2');
        $this->assertEquals(2, $card2->getValue());

        $card10 = new BlackJackGraphic('clubs', '10');
        $this->assertEquals(10, $card10->getValue());
    }

    /**
     * Test getSuitUnicode method.
     */
    public function testGetSuitUnicode(): void
    {
        $cardHearts = new BlackJackGraphic('hearts', '2');
        $this->assertEquals('♥', $cardHearts->getSuitUnicode());

        $cardDiamonds = new BlackJackGraphic('diamonds', '2');
        $this->assertEquals('♦', $cardDiamonds->getSuitUnicode());

        $cardClubs = new BlackJackGraphic('clubs', '2');
        $this->assertEquals('♣', $cardClubs->getSuitUnicode());

        $cardSpades = new BlackJackGraphic('spades', '2');
        $this->assertEquals('♠', $cardSpades->getSuitUnicode());

        $cardUnknown = new BlackJackGraphic('unknown', '2');
        $this->assertEquals('', $cardUnknown->getSuitUnicode());
    }

    /**
     * Test __toString method.
     */
    public function testToString(): void
    {
        $card = new BlackJackGraphic('hearts', 'A');
        $this->assertEquals('A♥', (string) $card);

        $card2 = new BlackJackGraphic('spades', '10');
        $this->assertEquals('10♠', (string) $card2);
    }

    /**
     * Test jsonSerialize method.
     */
    public function testJsonSerialize(): void
    {
        $card = new BlackJackGraphic('clubs', 'Q');
        $expected = [
            'suit' => 'clubs',
            'rank' => 'Q',
            'value' => 10,
            'asString' => 'Q♣',
        ];
        $this->assertEquals($expected, $card->jsonSerialize());

        $jsonExpected = json_encode($expected);
        $this->assertNotFalse($jsonExpected, 'json_encode on $expected failed');

        $jsonEncoded = json_encode($card);
        $this->assertNotFalse($jsonEncoded, 'json_encode on $card failed');

        $this->assertJsonStringEqualsJsonString($jsonExpected, $jsonEncoded);
    }
}
