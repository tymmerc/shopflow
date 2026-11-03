<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // TODO: remplacer par une relation ManyToOne vers User quand l'entité existera
    #[ORM\Column(nullable: true)]
    private ?int $userId = null;

    // Identifie le panier d'un visiteur anonyme (cookie), en attendant les comptes utilisateurs
    #[ORM\Column(length: 32, unique: true)]
    private string $token;

    /**
     * @var Collection<int, CartCatalog>
     */
    #[ORM\OneToMany(targetEntity: CartCatalog::class, mappedBy: 'cart', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->token = bin2hex(random_bytes(16));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return Collection<int, CartCatalog>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(CartCatalog $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setCart($this);
        }

        return $this;
    }

    public function removeItem(CartCatalog $item): static
    {
        if ($this->items->removeElement($item) && $item->getCart() === $this) {
            $item->setCart(null);
        }

        return $this;
    }
}
