<?php

namespace App\Project;

/**
 * The BlackJackDeck class.
 */
class BlackJackDeck
{
    /**
     * @var BlackJackGraphic[] The cards in the deck.
     */
    private array $cards;

    /**
     * Initializes a new deck with 52 playing cards.
     */
    public function __construct()
    {
        $this->cards = [];
        $suits = ['hearts', 'diamonds', 'clubs', 'spades'];
        $cardRanks = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];

        foreach ($suits as $suit) {
            foreach ($cardRanks as $rank) {
                $this->cards[] = new BlackJackGraphic($suit, $rank);
            }
        }
    }

    /**
     * Gets all cards in the deck.
     * @return BlackJackGraphic[] The cards.
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * Shuffles the deck.
     */
    public function shuffle(): void
    {
        shuffle($this->cards);
    }

    /**
     * Draws a card from the deck.
     * @return BlackJackGraphic|null The drawn card or null if deck is empty.
     */
    public function draw(): ?BlackJackGraphic
    {
        $card = array_pop($this->cards) ?: null;
        return $card;
    }

    /**
     * Sets the deck's cards.
     * @param array<int, BlackJackGraphic|array{suit:string, rank:string}> $cards
     */
    public function setCards(array $cards): void
    {
        $this->cards = [];
        foreach ($cards as $cardData) {
            if ($cardData instanceof BlackJackGraphic) {
                $this->cards[] = $cardData;
            } else {
                $this->cards[] = new BlackJackGraphic($cardData['suit'], $cardData['rank']);
            }
        }
    }
}
