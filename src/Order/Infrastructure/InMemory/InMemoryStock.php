<?php

namespace App\Order\Infrastructure\InMemory;

use App\Order\Application\Port\Stock;
use App\Order\Domain\Exception\InsufficientStockException;

final class InMemoryStock implements Stock
{
    /**
     * @param array<int, int> $quantities stock par id d'article
     */
    public function __construct(private array $quantities = [])
    {
    }

    public function availableQuantity(int $catalogId): int
    {
        return $this->quantities[$catalogId] ?? 0;
    }

    public function decrement(array $quantities): void
    {
        // Tout vérifier avant de toucher au stock : tout ou rien, comme l'adaptateur Doctrine
        foreach ($quantities as $catalogId => $quantity) {
            if ($this->availableQuantity($catalogId) < $quantity) {
                throw new InsufficientStockException($catalogId);
            }
        }
        foreach ($quantities as $catalogId => $quantity) {
            $this->quantities[$catalogId] -= $quantity;
        }
    }
}
