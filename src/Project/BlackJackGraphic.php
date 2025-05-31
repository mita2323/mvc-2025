<?php

namespace App\Project;

class BlackJackGraphic implements \JsonSerializable
{
    private string $suit;
    private string $rank;
    private int $value;

    /**
     * Constructor.
     * @param string $suit Suit of the card ('hearts', 'diamonds', 'clubs', 'spades').
     * @param string $rank Rank of the card ('2'-'10', 'J', 'Q', 'K', 'A').
     */
    public function __construct(string $suit, string $rank)
    {
        $this->suit = $suit;
        $this->rank = $rank;
        $this->value = $this->determineValue($rank);
    }

    /**
     * Determine the numeric value based on the rank.
     */
    private function determineValue(string $rank): int
    {
        if (in_array($rank, ['J', 'Q', 'K'], true)) {
            return 10;
        }
        if ($rank === 'A') {
            return 11;
        }
        return (int)$rank;
    }

    public function getSuit(): string
    {
        return $this->suit;
    }

    public function getRank(): string
    {
        return $this->rank;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * Returns the Unicode symbol for the suit.
     */
    public function getSuitUnicode(): string
    {
        return match ($this->suit) {
            'hearts' => '♥',
            'diamonds' => '♦',
            'clubs' => '♣',
            'spades' => '♠',
            default => '',
        };
    }

    /**
     * Returns a string representation like 'A♥' or '10♠'.
     */
    public function __toString(): string
    {
        return $this->rank . $this->getSuitUnicode();
    }

    /**
     * JSON serialization support.
     */
    public function jsonSerialize(): array
    {
        return [
            'suit' => $this->suit,
            'rank' => $this->rank,
            'value' => $this->value,
            'asString' => $this->__toString(),
        ];
    }
}
