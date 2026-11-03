<?php

namespace App\Entity;

use App\Repository\CommandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandRepository::class)]
class Command
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // TODO: remplacer par une relation ManyToOne vers User quand l'entité existera
    #[ORM\Column]
    private ?int $userId = null;

    /**
     * @var Collection<int, CommandCatalog>
     */
    #[ORM\OneToMany(targetEntity: CommandCatalog::class, mappedBy: 'command', cascade: ['persist'], orphanRemoval: true)]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return Collection<int, CommandCatalog>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(CommandCatalog $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setCommand($this);
        }

        return $this;
    }

    public function removeItem(CommandCatalog $item): static
    {
        if ($this->items->removeElement($item) && $item->getCommand() === $this) {
            $item->setCommand(null);
        }

        return $this;
    }
}
