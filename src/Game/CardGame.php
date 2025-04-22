<?php

namespace App\Game;

class CardGame
{
    protected string $value;
    protected string $suit;

    public function __construct(string $suit, string $value)
    {
        $this->suit = $suit;
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getSuit(): string
    {
        return $this->suit;
    }

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
