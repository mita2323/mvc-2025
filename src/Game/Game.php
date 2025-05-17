<?php

namespace App\Game;

use App\Game\DeckOfCardsGame;
use App\Game\Player;

/**
 * Game class.
 */
class Game
{
    /**
     * @var DeckOfCardsGame The deck of cards for the game.
     */
    private DeckOfCardsGame $deck;
    /**
     * @var Player The player in the game.
     */
    private Player $player;
    /**
     * @var Player the dealer in the game.
     */
    private Player $dealer;
    /**
     * @var string The game status.
     */
    private string $status;

    /**
     * Initializes a new card game.
     */
    public function __construct()
    {
        $this->deck = new DeckOfCardsGame();
        $this->deck->shuffle();
        $this->player = new Player('Player');
        $this->dealer = new Player('Dealer');
        $this->status = 'not_started';
    }

    /**
     * Starts a new card game.
     */
    public function startGame(): void
    {
        $this->deck = new DeckOfCardsGame();
        $this->deck->shuffle();
        $this->player->clearHand();
        $this->dealer->clearHand();
        $this->status = 'ongoing';
    }

    /**
     * Allows the player to hit (draw a card).
     */
    public function hit(): void
    {
        if ($this->status === 'ongoing') {
            $card = $this->deck->draw();
            if ($card) {
                $this->player->addCard($card);
                if ($this->player->getScore() > 21) {
                    $this->status = 'player_bust';
                }
            }
        }
    }

    /**
     * Allows teh player to stand (end their turn).
     */
    public function stand(): void
    {
        if ($this->status !== 'ongoing') {
            return;
        }

        while ($this->dealer->getScore() < 17) {
            $card = $this->deck->draw();
            if ($card === null) {
                break;
            }
            $this->dealer->addCard($card);
        }

        $playerScore = $this->player->getScore();
        $dealerScore = $this->dealer->getScore();

        if ($dealerScore > 21) {
            $this->status = 'dealer_bust';
        } elseif ($dealerScore >= $playerScore) {
            $this->status = 'dealer_win';
        } else {
            $this->status = 'player_win';
        }
    }

    /**
     * Gets the player object.
     * @return Player The player in the game.
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * Gets the dealer object.
     * @return Player The dealer in the game.
     */
    public function getDealer(): Player
    {
        return $this->dealer;
    }

    /**
     * Gets the current game status.
     * @return string The game status.
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Gets the deck of cards.
     * @return DeckOfCardsGame The game's deck.
     */
    public function getDeck(): DeckOfCardsGame
    {
        return $this->deck;
    }
}
