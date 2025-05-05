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
    public function testCreatePlayer(): void
    {
        $player = new Player("Test");
        $this->assertEquals("Test", $player->getName());
    }

    public function testAddCardAndGetScore(): void
    {
        $player = new Player("Test");
        $player->addCard(new CardGameGraphic("hearts", "10"));
        $player->addCard(new CardGameGraphic("spades", "A"));
        $this->assertEquals(21, $player->getScore());
    }

    public function testClearHand(): void
    {
        $player = new Player("Test");
        $player->addCard(new CardGameGraphic("clubs", "5"));
        $player->clearHand();
        $this->assertCount(0, $player->getHand());
    }

    public function testAceCountedAsOneWhenNeeded(): void
    {
        $player = new Player("Test");
        $player->addCard(new CardGameGraphic("hearts", "10"));
        $player->addCard(new CardGameGraphic("spades", "5"));
        $player->addCard(new CardGameGraphic("clubs", "A"));
        $this->assertEquals(16, $player->getScore());
    }
}
