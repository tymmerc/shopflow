<?php

namespace App\Order\Domain;

use App\Order\Domain\Exception\EmptyCartException;

final class Order
{
    private ?int $id = null;

    /**
     * @param OrderLine[] $lines
     */
    private function __construct(
        private readonly int $userId,
        private readonly array $lines,
    ) {
    }

    /**
     * @param OrderLine[] $lines
     */
    public static function place(int $userId, array $lines): self
    {
        if ([] === $lines) {
            throw new EmptyCartException();
        }

        return new self($userId, $lines);
    }

    /**
     * @param OrderLine[] $lines
     */
    public static function reconstitute(int $id, int $userId, array $lines): self
    {
        $order = new self($userId, $lines);
        $order->id = $id;

        return $order;
    }

    public function assignId(int $id): void
    {
        if (null !== $this->id) {
            throw new \LogicException('Cette commande a déjà un id.');
        }

        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return OrderLine[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    public function totalCents(): int
    {
        return array_sum(array_map(static fn (OrderLine $line): int => $line->subtotalCents(), $this->lines));
    }
}
