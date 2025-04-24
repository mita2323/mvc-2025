<?php

namespace App\Game;

use App\Game\CardGame;

class Player
{
    /** @var CardGame[] */
    private array $hand = [];
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function addCard(CardGame $card): void
    {
        $this->hand[] = $card;
    }

    /**
     * @return CardGame[]
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
