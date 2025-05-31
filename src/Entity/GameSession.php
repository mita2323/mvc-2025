<?php

namespace App\Entity;

use App\Repository\GameSessionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a GameSession entity.
 */
#[ORM\Entity(repositoryClass: GameSessionRepository::class)]
class GameSession
{
    /**
     * The unique id for GameSession.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The player associated with this game session.
     */
    #[ORM\ManyToOne(inversedBy: 'gameSessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Player $player = null;

    /**
     * The number of hands played by the player in this session.
     */
    #[ORM\Column]
    private ?int $numHands = null;

    /**
     * The bet amount placed per hand in this session.
     */
    #[ORM\Column]
    private ?int $betPerHand = null;

    /**
     * The outcome of the game session.
     */
    #[ORM\Column(length: 50)]
    private ?string $outcome = null;

    /**
     * The timestamp when the game session was created.
     */
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * Constructor to initialize the GameSession.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Gets the unique id of the GameSession.
     * @return ?int The ID of the record, or null.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Gets the player associated with this game session.
     * @return ?Player The Player entity, or null.
     */
    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    /**
     * Sets the player associated with this game session.
     * @param ?Player $player The Player entity to associate.
     * @return static This GameSession instance for method chaining.
     */
    public function setPlayer(?Player $player): static
    {
        $this->player = $player;

        return $this;
    }

    /**
     * Gets the number of hands played in this session.
     * @return ?int The number of hands, or null.
     */
    public function getNumHands(): ?int
    {
        return $this->numHands;
    }

    /**
     * Sets the number of hands played in this session.
     * @param int $numHands The number of hands to set.
     * @return static This GameSession instance for method chaining.
     */
    public function setNumHands(int $numHands): static
    {
        $this->numHands = $numHands;

        return $this;
    }

    /**
     * Gets the bet amount per hand in this session.
     * @return ?int The bet amount per hand, or null.
     */
    public function getBetPerHand(): ?int
    {
        return $this->betPerHand;
    }

    /**
     * Sets the bet amount per hand in this session.
     * @param int $betPerHand The bet amount to set.
     * @return static This GameSession instance for method chaining.
     */
    public function setBetPerHand(int $betPerHand): static
    {
        $this->betPerHand = $betPerHand;

        return $this;
    }

    /**
     * Gets the outcome of the game session.
     * @return ?string The outcome, or null.
     */
    public function getOutcome(): ?string
    {
        return $this->outcome;
    }

    /**
     * Sets the outcome of the game session.
     * @param string $outcome The outcome to set.
     * @return static This GameSession instance for method chaining.
     */
    public function setOutcome(string $outcome): static
    {
        $this->outcome = $outcome;

        return $this;
    }

    /**
     * Gets the timestamp when the game session was created.
     * @return ?\DateTimeInterface The creation timestamp, or null.
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Sets the timestamp when the game session was created.
     * @param \DateTimeInterface $createdAt The creation timestamp to set.
     * @return static This GameSession instance for method chaining.
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
