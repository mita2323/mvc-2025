<?php

namespace App\Card;

/**
 * Card class.
 */
class Card
{
    private const SUITS = ['hearts', 'diamonds', 'clubs', 'spades'];
    private const VALUES = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];

    /**
     * The suit of the card.
     * @var string
     */
    private string $suit;

    /**
     * The value of the card.
     * @var string
     */
    protected string $value;

    /**
     * Creates a new Card instance.
     * @param string $suit The suit of the card.
     * @param string $value The value of the card.
     * @throws \InvalidArgumentException If suit or value is invalid.
     */
    public function __construct(string $suit, string $value)
    {
        if (!in_array($suit, self::SUITS, true)) {
            throw new \InvalidArgumentException("Invalid suit: $suit");
        }
        if (!in_array($value, self::VALUES, true)) {
            throw new \InvalidArgumentException("Invalid value: $value");
        }
        $this->suit = $suit;
        $this->value = $value;
    }

    /**
     * Gets the value of the card.
     * @return string The card's value.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Gets the suit of the card.
     * @return string The card's suit.
     */
    public function getSuit(): string
    {
        return $this->suit;
    }

    /**
     * Gets a string representation of the card.
     * @return string The card as a string.
     */
    public function getAsString(): string
    {
        return "$this->value of $this->suit";
    }

    /**
     * Gets the raw suit value for use by subclasses.
     *
     * @return string The raw suit value.
     */
    protected function getRawSuit(): string
    {
        return $this->suit;
    }
}
