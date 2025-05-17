<?php

namespace App\Game;

use App\Game\CardGameGraphic;

/**
 * Player class.
 */
class Player
{
    /** 
     * @var CardGameGraphic[] The player's hand of cards.
     */
    private array $hand = [];
    /** 
     * @var string The player's name.
     */
    private string $name;

    /**
     * Initializes a new player with a name.
     * @param string $name The player's name.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Adds a card to the player's hand.
     * @param CardGameGraphic $card The card to add.
     */
    public function addCard(CardGameGraphic $card): void
    {
        $this->hand[] = $card;
    }

    /**
     * Gets the player's hand of cards.
     * @return CardGameGraphic[] The array of cards in the hand.
     */
    public function getHand(): array
    {
        return $this->hand;
    }

    /**
     * Gets the player's name.
     * @return string The player's name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Calculates the players score.
     * @return int The player's score.
     */
    public function getScore(): int
    {
        $score = 0;
        $aces = 0;

        foreach ($this->hand as $card) {
            $value = $card->getNumValue();
            if ($card->getValue() === 'A') {
                $aces++;
            } else {
                $score += $value;
            }
        }

        for ($i = 0; $i < $aces; $i++) {
            if ($score + 11 <= 21) {
                $score += 11;
            } else {
                $score += 1;
            }
        }

        return $score;
    }

    /**
     * Clears the player's hand of all cards.
     */
    public function clearHand(): void
    {
        $this->hand = [];
    }
}
