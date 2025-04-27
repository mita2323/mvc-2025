<?php

namespace App\Card;

use App\Card\CardGraphic;

class CardHand
{
    /** @var Card[] */
    private array $cards = [];

    public function addCard(CardGraphic $card): void
    {
        $this->cards[] = $card;
    }

    /**
     * @return Card[]
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    public function clear(): void
    {
        $this->cards = [];
    }
}
