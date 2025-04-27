<?php

namespace App\Card;

class Card
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

    public function getAsString(): string
    {
        return "{$this->value} of {$this->suit}";
    }
}
