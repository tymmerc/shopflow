<?php

namespace App\Order\Infrastructure\InMemory;

use App\Order\Application\Port\OrderRepository;
use App\Order\Domain\Order;

final class InMemoryOrderRepository implements OrderRepository
{
    /** @var array<int, Order> */
    private array $orders = [];

    public function save(Order $order): void
    {
        if (null === $order->getId()) {
            $order->assignId(\count($this->orders) + 1);
        }

        $this->orders[$order->getId()] = $order;
    }

    public function get(int $id): ?Order
    {
        return $this->orders[$id] ?? null;
    }

    public function findByUser(int $userId): array
    {
        return array_values(array_filter(
            $this->orders,
            static fn (Order $order): bool => $order->getUserId() === $userId,
        ));
    }
}
