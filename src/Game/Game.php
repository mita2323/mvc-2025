<?php

namespace App\Game;

use App\Game\DeckOfCardsGame;
use App\Game\Player;

class Game
{
    private DeckOfCardsGame $deck;
    private Player $player;
    private Player $dealer;
    private string $status;

    public function __construct()
    {
        $this->deck = new DeckOfCardsGame();
        $this->deck->shuffle();
        $this->player = new Player('Player');
        $this->dealer = new Player('Dealer');
        $this->status = 'not_started';
    }

    public function startGame(): void
    {
        $this->deck = new DeckOfCardsGame();
        $this->deck->shuffle();
        $this->player->clearHand();
        $this->dealer->clearHand();
        $this->status = 'ongoing';
    }

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

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getDealer(): Player
    {
        return $this->dealer;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDeck(): DeckOfCardsGame
    {
        return $this->deck;
    }
}
