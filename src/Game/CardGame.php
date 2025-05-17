<?php

namespace App\Game;

/**
 * CardGame class.
 */
class CardGame
{
    /**
      * @var string The cards value.
     */
    protected string $value;
    /**
     * @var string The card's suit.
     */
    protected string $suit;

    /**
     * Initializes a new card with suit and value.
     * @param string $suit The card's suit.
     * @param string $value The card's value.
     */
    public function __construct(string $suit, string $value)
    {
        $this->suit = $suit;
        $this->value = $value;
    }

    /**
     * Gets the card's value.
     * @return string The card's value.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Gets the card's suit.
     * @returns string The card's suit.
     */
    public function getSuit(): string
    {
        return $this->suit;
    }

    /**
     * Returns a string representation of the card.
     * @return string The card.
     */
    public function __toString(): string
    {
        return "{$this->value} of {$this->suit}";
    }

    /**
     * Returns the card's string representation.
     * @return string The card.
     */
    public function getAsString(): string
    {
        return $this->__toString();
    }

    /**
     * Gets the card's numeric value.
     * @return int The numeric value of the card.
     */
    public function getNumValue(): int
    {
        if (in_array($this->value, ['J', 'Q', 'K'])) {
            return 10;
        }
        if ($this->value === 'A') {
            return 11;
        }
        return (int)$this->value;
    }
}
