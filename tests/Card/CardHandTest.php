<?php

namespace App\Tests\Card;

use App\Card\CardGraphic;
use App\Card\CardHand;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for CardHand class.
 */
class CardHandTest extends TestCase
{
    /**
     * Test adding cards, retrieving them, and then clearing the hand.
     */
    public function testAddGetClearCards(): void
    {
        $hand = new CardHand();

        $this->assertEmpty($hand->getCards());

        $card1 = new CardGraphic('hearts', 'A');
        $card2 = new CardGraphic('spades', 'K');

        $hand->addCard($card1);
        $hand->addCard($card2);

        $cards = $hand->getCards();
        $this->assertCount(2, $cards);
        $this->assertSame($card1, $cards[0]);
        $this->assertSame($card2, $cards[1]);

        $hand->clear();
        $this->assertEmpty($hand->getCards());
    }
}
