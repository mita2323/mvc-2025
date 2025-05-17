<?php

namespace App\Card;

use App\Card\CardGraphic;

/**
 * DeckofCards Class.
 */
class DeckOfCards
{
    /** @var CardGraphic[] */
    private array $cards = [];

    /**
     * Initializes a new deck with 52 cards.
     */
    public function __construct()
    {
        $suits = ['hearts', 'diamonds', 'clubs', 'spades'];
        $values = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];

        foreach ($suits as $suit) {
            foreach ($values as $value) {
                $this->cards[] = new CardGraphic($suit, $value);
            }
        }
    }

    /**
     * Shuffles the deck randomly.
     */
    public function shuffle(): void
    {
        shuffle($this->cards);
    }

    /**
     * Draws  the top card from the deck.
     * @return CardGraphic|null The drawn card, or null is the deck is empty.
     */
    public function draw(): ?CardGraphic
    {
        if (empty($this->cards)) {
            return null;
        }
        return array_shift($this->cards);
    }

    /**
     * Draws multiple cards from the deck.
     * @param int $number The number of cards to draw.
     * @return CardGraphic[] The drawn cards.
     */
    public function drawMany(int $number): array
    {
        return array_splice($this->cards, 0, $number);
    }

    /**
     * Gets all the cards in the deck.
     * @return CardGraphic[] The current cards in the deck.
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * Returns a sorted copy of the deck by suit and value.
     * @return CardGraphic[] The sorted cards.
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

    /**
     * Counts the number of cards in the deck.
     * @return int The number of cards.
     */
    public function count(): int
    {
        return count($this->cards);
    }
}
