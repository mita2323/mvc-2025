<?php

namespace App\Card;

/**
 * CardGraphic class
 */
class CardGraphic extends Card
{
    /**
     * Gets a string representation of the card with Unicode suit symbol.
     *
     * @return string The card as a string.
     */
    public function getAsString(): string
    {
        return "{$this->value}{$this->getSuitUnicode()}";
    }

    /**
     * Converts the card to a string.
     *
     * @return string The card as a string.
     */
    public function __toString(): string
    {
        return $this->getAsString();
    }

    /**
     * Gets the Unicode symbol for the card's suit.
     *
     * @return string The Unicode suit symbol.
     */
    protected function getSuitUnicode(): string
    {
        switch ($this->getRawSuit()) {
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
