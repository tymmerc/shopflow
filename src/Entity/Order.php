<?php

namespace App\Entity;

use App\Order\Domain\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // TODO: remplacer par une relation ManyToOne vers User quand l'entité existera
    #[ORM\Column]
    private ?int $userId = null;

    // Simple id, pas de relation : un panier peut être supprimé sans toucher aux commandes
    #[ORM\Column(nullable: true)]
    private ?int $cartId = null;

    #[ORM\Column(length: 20, enumType: OrderStatus::class)]
    private ?OrderStatus $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $placedAt = null;

    /**
     * @var Collection<int, OrderCatalog>
     */
    #[ORM\OneToMany(targetEntity: OrderCatalog::class, mappedBy: 'order', cascade: ['persist'], orphanRemoval: true)]
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

    public function getCartId(): ?int
    {
        return $this->cartId;
    }

    public function setCartId(?int $cartId): static
    {
        $this->cartId = $cartId;

        return $this;
    }

    public function getStatus(): ?OrderStatus
    {
        return $this->status;
    }

    public function setStatus(OrderStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPlacedAt(): ?\DateTimeImmutable
    {
        return $this->placedAt;
    }

    public function setPlacedAt(\DateTimeImmutable $placedAt): static
    {
        $this->placedAt = $placedAt;

        return $this;
    }

    /**
     * @return Collection<int, OrderCatalog>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderCatalog $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }

        return $this;
    }

    public function removeItem(OrderCatalog $item): static
    {
        if ($this->items->removeElement($item) && $item->getOrder() === $this) {
            $item->setOrder(null);
        }

        return $this;
    }
}
