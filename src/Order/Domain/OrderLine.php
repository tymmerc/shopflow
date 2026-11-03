<?php

namespace App\Order\Domain;

final class OrderLine
{
    public function __construct(
        private readonly int $catalogId,
        private readonly int $unitPriceCents,
        private readonly int $quantity,
    ) {
        if ($quantity < 1) {
            throw new \DomainException('La quantité doit être au moins de 1.');
        }
        if ($unitPriceCents < 0) {
            throw new \DomainException('Le prix ne peut pas être négatif.');
        }
    }

    public function getCatalogId(): int
    {
        return $this->catalogId;
    }

    public function getUnitPriceCents(): int
    {
        return $this->unitPriceCents;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function subtotalCents(): int
    {
        return $this->unitPriceCents * $this->quantity;
    }
}
