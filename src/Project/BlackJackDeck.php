<?php

namespace App\Project;

use App\Project\BlackJackGraphic;

class BlackJackDeck
{
    /** @var BlackJackGraphic[] */
    private array $cards = [];

    public function __construct()
    {
        $suits = ['hearts', 'diamonds', 'clubs', 'spades'];
        $values = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];

        foreach ($suits as $suit) {
            foreach ($values as $value) {
                $this->cards[] = new BlackJackGraphic($suit, $value);
            }
        }
    }

    public function shuffle(): void
    {
        shuffle($this->cards);
    }

    /**
     * @return BlackJackGraphic|null
     */
    public function draw(): ?BlackJackGraphic
    {
        if (empty($this->cards)) {
            return null;
        }
        return array_shift($this->cards);
    }

    /**
     * @return BlackJackGraphic[]
     */
    public function drawMany(int $number): array
    {
        return array_splice($this->cards, 0, $number);
    }

    /**
     * @return BlackJackGraphic[]
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * @return BlackJackGraphic[]
     */
    public function sortedCards(): array
    {
        $sortedCards = $this->cards;
        $suitOrder = ['hearts', 'diamonds', 'clubs', 'spades'];
        $valueOrder = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];

        usort($sortedCards, function ($card1, $card2) use ($suitOrder, $valueOrder) {
            $suitCompare = array_search($card1->getSuit(), $suitOrder) <=> array_search($card2->getSuit(), $suitOrder);
            if ($suitCompare !== 0) {
                return $suitCompare;
            }

            return array_search($card1->getValue(), $valueOrder) <=> array_search($card2->getValue(), $valueOrder);
        });

        return $sortedCards;
    }

    public function count(): int
    {
        return count($this->cards);
    }
}
