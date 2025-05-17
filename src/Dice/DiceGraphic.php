<?php

namespace App\Dice;

class DiceGraphic extends Dice
{
    /** @var string[] */
    private $representation = [
        '⚀',
        '⚁',
        '⚂',
        '⚃',
        '⚄',
        '⚅',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function getAsString(): string
    {
        if ($this->value === null || $this->value < 1 || $this->value > 6) {
            throw new \OutOfRangeException("Dice value must be between 1 and 6");
        }
        return $this->representation[$this->value - 1];
    }
}
