<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $balance = null;

    /**
     * @var Collection<int, GameSession>
     */
    #[ORM\OneToMany(targetEntity: GameSession::class, mappedBy: 'player')]
    private Collection $numHands;

    public function __construct()
    {
        $this->numHands = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @return Collection<int, GameSession>
     */
    public function getNumHands(): Collection
    {
        return $this->numHands;
    }

    public function addNumHand(GameSession $numHand): static
    {
        if (!$this->numHands->contains($numHand)) {
            $this->numHands->add($numHand);
            $numHand->setPlayer($this);
        }

        return $this;
    }

    public function removeNumHand(GameSession $numHand): static
    {
        if ($this->numHands->removeElement($numHand)) {
            if ($numHand->getPlayer() === $this) {
                $numHand->setPlayer(null);
            }
        }

        return $this;
    }
}
