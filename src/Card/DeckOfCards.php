<?php

namespace App\Card;

use App\Card\Card;

class DeckOfCards
{
    /** @var Card[] */
    private array $cards = [];

    public function __construct()
    {
        $suits = ['hearts', 'diamonds', 'clubs', 'spades'];
        $values = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];

        foreach ($suits as $suit) {
            foreach ($values as $value) {
                $this->cards[] = new Card($suit, $value);
            }
        }
    }

    public function shuffle(): void
    {
        shuffle($this->cards);
    }

    /**
     * @return Card|null
     */
    public function draw(): ?Card
    {
        if (empty($this->cards)) {
            return null;
        }
        return array_shift($this->cards);
    }

    /**
     * @param int $number
     * @return Card[]
     */
    public function drawMany(int $number): array
    {
        return array_splice($this->cards, 0, $number);
    }

    /**
     * @return Card[]
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * @return Card[]
    */
    public function sortedCards(): array
    {
        $sortedCards = $this->cards;
        $suitOrder = ['hearts', 'diamonds', 'clubs', 'spades'];
        $valueOrder = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];

        usort($sortedCards, function ($a, $b) use ($suitOrder, $valueOrder) {
            $suitCompare = array_search($a->getSuit(), $suitOrder) <=> array_search($b->getSuit(), $suitOrder);
            if ($suitCompare !== 0) {
                return $suitCompare;
            }

            return array_search($a->getValue(), $valueOrder) <=> array_search($b->getValue(), $valueOrder);
        });

        return $sortedCards;
    }

    public function count(): int
    {
        return count($this->cards);
    }
}
