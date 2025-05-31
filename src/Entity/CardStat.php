<?php

namespace App\Entity;

use App\Repository\CardStatRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a CardStat entity.
 */
#[ORM\Entity(repositoryClass: CardStatRepository::class)]
class CardStat
{
    /**
     * The unique id for CardStat.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The value of the card.
     */
    #[ORM\Column(length: 10)]
    private ?string $cardValue = null;

    /**
     * The number of times a card value has been drawn.
     */
    #[ORM\Column]
    private ?int $count = null;

    /**
     * Gets the unique id of the CardStat record.
     * @return ?int The ID of the record, or null.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Gets the card value.
     * @return ?string The card value, or null.
     */
    public function getCardValue(): ?string
    {
        return $this->cardValue;
    }

    /**
     * Sets the card value.
     * @param string $cardValue The card value to set.
     * @return static This CardStat instance for method chaining.
     */
    public function setCardValue(string $cardValue): static
    {
        $this->cardValue = $cardValue;

        return $this;
    }

    /**
     * Gets the number of times a card value has been drawn.
     * @return ?int The count of draws, or null.
     */
    public function getCount(): ?int
    {
        return $this->count;
    }

    /**
     * Sets the number of times a card value has been drawn.
     * @param int $count The count to set.
     * @return static This CardStat instance for method chaining.
     */
    public function setCount(int $count): static
    {
        $this->count = $count;

        return $this;
    }
}
