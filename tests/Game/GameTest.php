<?php

namespace App\Tests\Game;

use PHPUnit\Framework\TestCase;
use App\Game\Game;
use App\Game\DeckOfCardsGame;
use App\Game\Player;
use App\Game\CardGameGraphic;

/**
 * Test cases for the Game class.
 */
class GameTest extends TestCase
{
    public function testGameStarts(): void
    {
        $game = new Game();
        $game->startGame();
        $this->assertEquals('ongoing', $game->getStatus());
    }

    public function testPlayerHit(): void
    {
        $game = new Game();
        $game->startGame();
        $initial = count($game->getPlayer()->getHand());
        $game->hit();
        $this->assertGreaterThan($initial, count($game->getPlayer()->getHand()));
    }

    public function testGameStand(): void
    {
        $game = new Game();
        $game->startGame();
        $game->stand();
        $this->assertContains($game->getStatus(), [
            'dealer_win',
            'player_win',
            'dealer_bust'
        ]);
    }

    public function testGetDealerReturnsDealerInstance(): void
    {
        $game = new Game();
        $this->assertInstanceOf(Player::class, $game->getDealer());
    }

    public function testGetDeckReturnsDeckInstance(): void
    {
        $game = new Game();
        $this->assertInstanceOf(DeckOfCardsGame::class, $game->getDeck());
    }

    public function testHitWhenGameNotOngoing(): void
    {
        $game = new Game();
        $initial = count($game->getPlayer()->getHand());
        $game->hit();
        $this->assertEquals($initial, count($game->getPlayer()->getHand()));
    }

    public function testStandWhenGameNotOngoing(): void
    {
        $game = new Game();
        $game->stand();
        $this->assertEquals('not_started', $game->getStatus());
    }

    public function testPlayerBustOnHit(): void
    {
        $game = new Game();
        $game->startGame();

        while ($game->getPlayer()->getScore() <= 21) {
            $game->hit();
        }

        $this->assertEquals('player_bust', $game->getStatus());
    }

    public function testDealerDrawsCardsBelow17(): void
    {
        $game = new Game();
        $game->startGame();

        $game->getPlayer()->addCard(new CardGameGraphic('clubs', '10'));
        $game->getPlayer()->addCard(new CardGameGraphic('spades', '7'));

        $game->getDealer()->addCard(new CardGameGraphic('hearts', '6'));
        $game->getDealer()->addCard(new CardGameGraphic('diamonds', '5'));

        $initialCount = count($game->getDealer()->getHand());
        $game->stand();
        $finalCount = count($game->getDealer()->getHand());

        $this->assertGreaterThan($initialCount, $finalCount);
        $this->assertGreaterThanOrEqual(17, $game->getDealer()->getScore());
    }
}
