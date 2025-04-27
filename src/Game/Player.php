<?php

namespace App\Game;

use App\Game\CardGameGraphic;

class Player
{
    /** @var CardGameGraphic[] */
    private array $hand = [];
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function addCard(CardGameGraphic $card): void
    {
        $this->hand[] = $card;
    }

    /**
     * @return CardGameGraphic[]
     */
    public function getHand(): array
    {
        return $this->hand;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getScore(): int
    {
        $score = 0;
        $aces = 0;

        foreach ($this->hand as $card) {
            $value = $card->getNumValue();
            if ($card->getValue() === 'A') {
                $aces++;
            } else {
                $score += $value;
            }
        }

        for ($i = 0; $i < $aces; $i++) {
            if ($score + 11 <= 21) {
                $score += 11;
            } else {
                $score += 1;
            }
        }

        return $score;
    }

    public function clearHand(): void
    {
        $this->hand = [];
    }
}
