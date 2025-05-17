<?php

namespace App\Card;

use App\Card\CardGraphic;

class CardHand
{
    /** @var CardGraphic[] */
    private array $cards = [];

    public function addCard(CardGraphic $card): void
    {
        $this->cards[] = $card;
    }

    /**
     * @return CardGraphic[]
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
