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
        return "{$this->value} of {$this->suit}";
    }

    public function getAsString(): string
    {
        return $this->__toString();
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
