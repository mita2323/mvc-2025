<?php

namespace App\Card;

class CardGraphic extends Card
{
    public function getAsString(): string
    {
        return "{$this->value}{$this->getSuitUnicode()}";
    }

    public function __toString(): string
    {
        return $this->getAsString();
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
