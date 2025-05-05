<?php

namespace App\Tests\Game;

use PHPUnit\Framework\TestCase;
use App\Game\Player;
use App\Game\CardGameGraphic;

/**
 * Test cases for the Player class.
 */
class PlayerTest extends TestCase
{
    /**
     * Test that the player is created with the correct name.
     */
    public function testCreatePlayer(): void
    {
        $player = new Player("Test");
        $this->assertEquals("Test", $player->getName());
    }

    /**
     * Test that adding cards to the player updates their score correctly.
     */
    public function testAddCardAndGetScore(): void
    {
        $player = new Player("Test");
        $player->addCard(new CardGameGraphic("hearts", "10"));
        $player->addCard(new CardGameGraphic("spades", "A"));
        $this->assertEquals(21, $player->getScore());
    }

    /**
     * Test that clearing the player's hand removes all cards.
     */
    public function testClearHand(): void
    {
        $player = new Player("Test");
        $player->addCard(new CardGameGraphic("clubs", "5"));
        $player->clearHand();
        $this->assertCount(0, $player->getHand());
    }

    /**
     * Test that Aces are counted as 1 when counting them as 11 would bust the hand.
     */
    public function testAceCountedAsOneWhenNeeded(): void
    {
        $player = new Player("Test");
        $player->addCard(new CardGameGraphic("hearts", "10"));
        $player->addCard(new CardGameGraphic("spades", "5"));
        $player->addCard(new CardGameGraphic("clubs", "A"));
        $this->assertEquals(16, $player->getScore());
    }
}
