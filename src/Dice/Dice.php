<?php

namespace App\Dice;

class Dice
{
    /** @var int|null */
    protected $value;

    public function __construct()
    {
        $this->value = null;
    }

    public function roll(): int
    {
        $this->value = random_int(1, 6);
        return $this->value;
    }

    public function getValue(): int
    {
        return $this->value ?? 0;
    }

    public function getAsString(): string
    {
        return $this->value === null ? "[null]" : "[$this->value]";
    }
}
