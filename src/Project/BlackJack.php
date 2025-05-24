<?php

namespace App\Project;

use App\Project\BlackJackDeck;
use App\Project\BlackJackPlayer;

/**
 * BlackJack class.
 */
class BlackJack
{
    /**
     * @var BlackJackDeck The deck of cards for the game.
     */
    private BlackJackDeck $deck;
    /**
     * @var BlackJackPlayer The player in the game.
     */
    private BlackJackPlayer $player;
    /**
     * @var BlackJackPlayer the dealer in the game.
     */
    private BlackJackPlayer $dealer;
    /**
     * @var string The game status.
     */
    private string $status;

    /**
     * Initializes a new card game.
     */
    public function __construct()
    {
        $this->deck = new BlackJackDeck();
        $this->deck->shuffle();
        $this->player = new BlackJackPlayer('Player');
        $this->dealer = new BlackJackPlayer('Dealer');
        $this->status = 'not_started';
    }

    /**
     * Starts a new card game.
     */
    public function startGame(): void
    {
        $this->deck = new BlackJackDeck();
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
     * @return BlackJackPlayer The player in the game.
     */
    public function getPlayer(): BlackJackPlayer
    {
        return $this->player;
    }

    /**
     * Gets the dealer object.
     * @return BlackJackPlayer The dealer in the game.
     */
    public function getDealer(): BlackJackPlayer
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
     * @return BlackJackDeck The game's deck.
     */
    public function getDeck(): BlackJackDeck
    {
        return $this->deck;
    }
}
