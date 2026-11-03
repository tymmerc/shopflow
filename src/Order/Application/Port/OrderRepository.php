<?php

namespace App\Order\Application\Port;

use App\Order\Domain\Order;

interface OrderRepository
{
    public function save(Order $order): void;

    public function get(int $id): ?Order;

    /**
     * @return Order[]
     */
    public function findByUser(int $userId): array;
}
