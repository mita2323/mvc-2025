<?php

namespace App\Game;

/**
 * CardGameGraphic class.
 */
class CardGameGraphic extends CardGame
{
    /**
     * Returns a string representation of the card with a Unicode suit symbol.
     * @return string The visual card representation.
     */
    public function __toString(): string
    {
        return "{$this->value}{$this->getSuitUnicode()}";
    }

    /**
     * Returns the card's visual string representation.
     * @return string The visual card representation.
     */
    public function getAsString(): string
    {
        return $this->__toString();
    }

    /**
     * Maps the card's suit to its Unicode symbol.
     * @return string The Unicode suit symbol or empty string.
     */
    public function getSuitUnicode(): string
    {
        switch ($this->suit) {
            case 'hearts':
                return '♥';
            case 'diamonds':
                return '♦';
            case 'clubs':
                return '♣';
            case 'spades':
                return '♠';
            default:
                return '';
        }
    }
}
