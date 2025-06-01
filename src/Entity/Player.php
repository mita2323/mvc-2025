<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player
{
    /**
     * The unique id of the Player.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // @phpstan-ignore-next-line
    private ?int $id = null;

    /**
     * The name of the Player.
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * The balance amount of the Player.
     */
    #[ORM\Column(type: "float")]
    private ?float $balance = null;

    /**
     * The number of hands the Player has played.
     */
    #[ORM\Column]
    // @phpstan-ignore-next-line
    private ?int $numHands = 0;

    /**
     * The collection of GameSession entities associated with the Player.
     * @var Collection<int, GameSession>
     */
    #[ORM\OneToMany(targetEntity: GameSession::class, mappedBy: 'player', cascade: ["persist", "remove"])]
    private Collection $gameSessions;

    /**
     * Constructor to initialize collections.
     */
    public function __construct()
    {
        $this->gameSessions = new ArrayCollection();
    }

    /**
     * Get the unique id of the Player.
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the Player's name.
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the Player's name.
     * @return  $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the Player's balance.
     * @return  float|null
     */
    public function getBalance(): ?float
    {
        return $this->balance;
    }

    /**
     * Sets the Player's balance.
     * @param float $balance
     * @return $this
     */
    public function setBalance(float $balance): static
    {
        $this->balance = $balance;
        return $this;
    }

    /**
     * Get the number of hands the Player has played.
     * @return int|null
     */
    public function getNumHands(): ?int
    {
        return $this->numHands;
    }

    /**
     * Set the number of hands the Player has played.
     * @param int $numHands
     * @return $this
     */
    public function setNumHands(int $numHands): static
    {
        $this->numHands = $numHands;
        return $this;
    }

    /**
     * Get the GameSessions related to this Player.
     * @return Collection<int, GameSession>
     */
    public function getGameSessions(): Collection
    {
        return $this->gameSessions;
    }

    /**
     * Add a GameSession to the Player.
     * @param GameSession $gameSession
     * @return $this
     */
    public function addGameSession(GameSession $gameSession): static
    {
        if (!$this->gameSessions->contains($gameSession)) {
            $this->gameSessions->add($gameSession);
            $gameSession->setPlayer($this);
        }
        return $this;
    }

    /**
     * Remove a GameSEssion from the Player.
     * @param GameSession $gameSession
     * @return $this
     */
    public function removeGameSession(GameSession $gameSession): static
    {
        if ($this->gameSessions->removeElement($gameSession)) {
            if ($gameSession->getPlayer() === $this) {
                $gameSession->setPlayer(null);
            }
        }
        return $this;
    }
}
