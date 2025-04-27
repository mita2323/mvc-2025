<?php

namespace App\Game;

class CardGameGraphic extends CardGame
{
    public function __toString(): string
    {
        return "{$this->value}{$this->getSuitUnicode()}";
    }

    public function getAsString(): string
    {
        return $this->__toString();
    }

    protected function getSuitUnicode(): string
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
