<?php

namespace App\Order\Application\Port;

use App\Order\Domain\Exception\InsufficientStockException;

interface Stock
{
    public function availableQuantity(int $catalogId): int;

    /**
     * Retire les quantités du stock, tout ou rien : si un seul article manque, aucun stock n'est touché.
     *
     * @param array<int, int> $quantities quantité par id d'article
     *
     * @throws InsufficientStockException
     */
    public function decrement(array $quantities): void;
}
