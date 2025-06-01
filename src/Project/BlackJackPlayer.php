<?php

namespace App\Project;

use App\Entity\Player as PlayerEntity;
use App\Project\BlackJackGraphic;

/**
 * The BlackJackPlayer class.
 */
class BlackJackPlayer
{
    /**
     * @var BlackJackGraphic[][] The player's hands of cards.
     */
    private array $hands = [];

    /**
     * @var string The player's name.
     */
    private string $name;

    /**
     * @var int[] The player's bets for each hand.
     */
    private array $bets = [];

    /**
     * @var PlayerEntity|null The player's Doctrine entity.
     */
    private ?PlayerEntity $entity;

    /**
     * @var int[] The original bets for each hand (for display purposes).
     */
    private array $originalBets = [];

    /**
     * Hand states per hand index.
     * Possible values: 'active', 'stood', 'busted', 'finished'
     * @var string[]
     */
    private array $handStates = [];

    /**
     * Initializes a new player with a name.
     * @param string $name The player's name.
     * @param PlayerEntity|null $entity The player's Doctrine entity.
     */
    public function __construct(string $name, ?PlayerEntity $entity = null)
    {
        $this->name = $name;
        $this->entity = $entity;
        $this->clearHands();
    }

    /**
     * Sets the original bets for display purposes.
     * @param int[] $bets The original bets for each hand.
     */
    public function setOriginalBets(array $bets): void
    {
        $this->originalBets = array_map('intval', $bets);
    }

    /**
     * Gets the original bet for a hand.
     * @param int $handIndex The index of the hand.
     * @return int The original bet amount.
     */
    public function getOriginalBet(int $handIndex = 0): int
    {
        return $this->originalBets[$handIndex] ?? 0;
    }

    /**
     * Gets all original bets.
     * @return int[] The original bets array.
     */
    public function getOriginalBets(): array
    {
        return $this->originalBets;
    }

    /**
     * Gets the player's Doctrine entity.
     * @return PlayerEntity|null The player's Doctrine entity.
     */
    public function getEntity(): ?PlayerEntity
    {
        return $this->entity;
    }

    /**
     * Sets the player's Doctrine entity.
     * @param PlayerEntity $entity The Doctrine entity to set.
     */
    public function setEntity(PlayerEntity $entity): void
    {
        $this->entity = $entity;
    }

    /**
     * Adds a card to the player's hand.
     * @param BlackJackGraphic $card The card to add.
     * @param int $handIndex The index of the hand.
     */
    public function addCard(BlackJackGraphic $card, int $handIndex = 0): void
    {
        if (!isset($this->hands[$handIndex])) {
            $this->initializeNewHand($handIndex);
        }
        $this->hands[$handIndex][] = $card;
    }

    /**
     * Gets the player's hands of cards.
     * @return BlackJackGraphic[][] The array of hands.
     */
    public function getHands(): array
    {
        return $this->hands;
    }

    /**
     * Gets a specific hand of cards.
     * @param int $handIndex The index of the hand.
     * @return BlackJackGraphic[] The cards in the hand.
     */
    public function getHand(int $handIndex = 0): array
    {
        return $this->hands[$handIndex] ?? [];
    }

    /**
     * Gets the player's name.
     * @return string The player's name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the player's balance.
     * @return int The player's balance.
     */
    public function getBalance(): int
    {
        return (int) ($this->entity?->getBalance() ?? 0);
    }

    /**
     * Sets the player's balance. Useful for state restoration.
     * @param int $balance The balance to set.
     */
    public function setBalance(int $balance): void
    {
        if ($this->entity) {
            $this->entity->setBalance($balance);
        }
    }

    /**
     * Places a bet for a hand.
     * @param int $bet The bet amount.
     * @param int $handIndex The index of the hand.
     * @return bool True if the bet was placed, false otherwise.
     */
    public function placeBet(int $bet, int $handIndex = 0): bool
    {
        if ($this->entity && $bet > 0 && $this->entity->getBalance() >= $bet) {
            $this->bets[$handIndex] = ($this->bets[$handIndex] ?? 0) + $bet;
            $this->entity->setBalance($this->entity->getBalance() - $bet);
            $this->originalBets[$handIndex] = $this->bets[$handIndex];
            return true;
        }
        return false;
    }

    /**
     * Sets a bet for a hand (for state restoration).
     * @param int $bet The bet amount.
     * @param int $handIndex The index of the hand.
     */
    public function setBet(int $bet, int $handIndex = 0): void
    {
        $this->bets[$handIndex] = $bet;
        $this->originalBets[$handIndex] = $bet;
    }

    /**
     * Sets all bets (for state restoration).
     * @param int[] $bets An array of bet amounts by hand index.
     */
    public function setAllBets(array $bets): void
    {
        $this->bets = array_map('intval', $bets);
        $this->originalBets = $this->bets;
    }

    /**
     * Awards a win amount to the player's balance.
     * @param int $amount The amount to win.
     * @param int $handIndex The index of the hand.
     */
    public function winBet(int $amount, int $handIndex = 0): void
    {
        if ($this->entity) {
            $oldBalance = $this->entity->getBalance();
            $this->entity->setBalance($oldBalance + $amount);
        }
        $this->bets[$handIndex] = 0;
    }

    /**
     * Gets the bets for all hands.
     * @return int[] The bets for each hand.
     */
    public function getBets(): array
    {
        return $this->bets;
    }

    /**
     * Gets the bet for a hand.
     * @param int $handIndex The index of the hand.
     * @return int The bet amount.
     */
    public function getBet(int $handIndex = 0): int
    {
        return $this->bets[$handIndex] ?? 0;
    }

    /**
     * Calculates the player's score for a hand.
     * @param int $handIndex The index of the hand.
     * @return int The player's score.
     */
    public function getScore(int $handIndex = 0): int
    {
        $hand = $this->getHand($handIndex);
        $score = 0;
        $numAces = 0;

        foreach ($hand as $card) {
            $value = $this->getCardValue($card->getValue());
            if ($value === 11) {
                $numAces++;
            }
            $score += $value;
        }

        while ($score > 21 && $numAces > 0) {
            $score -= 10;
            $numAces--;
        }

        return $score;
    }

    /**
     * Converts card value to integer score.
     * @param string|int $cardValue The card value (e.g., 'A', 'K', '10').
     * @return int The integer score for the card.
     */
    private function getCardValue(string|int $cardValue): int
    {
        if (in_array($cardValue, ['J', 'Q', 'K'], true)) {
            return 10;
        }
        if ($cardValue === 'A') {
            return 11;
        }
        return (int)$cardValue;
    }

    /**
     * Clears the player's hands of all cards and resets states.
     */
    public function clearHands(): void
    {
        $this->hands = [];
        $this->bets = [];
        $this->handStates = [];
        $this->originalBets = [];
    }

    /**
     * Initializes a new hand with an 'active' state.
     * @param int $handIndex The index of the hand to initialize.
     */
    public function initializeNewHand(int $handIndex): void
    {
        $this->hands[$handIndex] = [];
        $this->bets[$handIndex] = 0;
        $this->handStates[$handIndex] = 'active';
        $this->originalBets[$handIndex] = 0;
    }

    /**
     * Splits a hand into two hands.
     * @param int $handIndex The index of the hand to split.
     * @return bool True if the hand was split, false otherwise.
     */
    public function splitHand(int $handIndex): bool
    {
        if (
            !isset($this->hands[$handIndex]) ||
            count($this->hands[$handIndex]) !== 2 ||
            $this->hands[$handIndex][0]->getRank() !== $this->hands[$handIndex][1]->getRank() ||
            $this->getHandState($handIndex) !== 'active'
        ) {
            return false;
        }

        $currentBet = $this->bets[$handIndex];

        if ($this->entity && $this->entity->getBalance() < $currentBet) {
            return false;
        }

        $currentBet = $this->bets[$handIndex];
        $newHandIndex = count($this->hands);

        $this->hands[$newHandIndex] = [$this->hands[$handIndex][1]];
        $this->hands[$handIndex] = [$this->hands[$handIndex][0]];

        $this->bets[$newHandIndex] = $currentBet;
        $this->originalBets[$newHandIndex] = $currentBet;

        $this->handStates[$handIndex] = 'active';
        $this->handStates[$newHandIndex] = 'active';

        if ($this->entity) {
            $this->entity->setBalance($this->entity->getBalance() - $currentBet);
        }

        return true;
    }

    /**
     * Checks if a hand is a Blackjack.
     * @param int $handIndex The index of the hand.
     * @return bool True if the hand is a Blackjack, false otherwise.
     */
    public function isBlackjack(int $handIndex = 0): bool
    {
        return count($this->hands[$handIndex] ?? []) === 2 && $this->getScore($handIndex) === 21;
    }

    /**
     * Get the state of a hand.
     * @param int $handIndex The index of the hand.
     * @return string The hand state.
     */
    public function getHandState(int $handIndex): string
    {
        return $this->handStates[$handIndex] ?? 'finished';
    }

    /**
     * Set the state of a hand.
     * @param int $handIndex The index of the hand.
     * @param string $state The state to set.
     */
    public function setHandState(int $handIndex, string $state): void
    {
        $validStates = ['active', 'stood', 'busted', 'finished'];
        if (in_array($state, $validStates, true)) {
            $this->handStates[$handIndex] = $state;
        } else {
            throw new \InvalidArgumentException("Invalid hand state: $state");
        }
    }

    /**
     * Mark a hand as stood.
     * @param int $handIndex The index of the hand.
     */
    public function stand(int $handIndex): void
    {
        $this->setHandState($handIndex, 'stood');
    }

    /**
     * Mark a hand as busted.
     * @param int $handIndex The index of the hand.
     */
    public function bust(int $handIndex): void
    {
        $this->setHandState($handIndex, 'busted');
    }

    /**
     * Check if the hand is still active (can take actions).
     * @param int $handIndex The index of the hand.
     * @return bool True if the hand is active, false otherwise.
     */
    public function isHandActive(int $handIndex): bool
    {
        return ($this->handStates[$handIndex] ?? 'finished') === 'active';
    }

    /**
     * Sets the player's hands for state restoration.
     * @param BlackJackGraphic[][] $hands The hands to set.
     */
    public function setHands(array $hands): void
    {
        $this->hands = $hands;
    }

    /**
     * Sets all hand states for state restoration.
     * @param string[] $handStates An associative array of hand states by hand index.
     */
    public function setAllHandStates(array $handStates): void
    {
        $this->handStates = $handStates;
    }
}
