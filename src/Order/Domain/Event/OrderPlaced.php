<?php

namespace App\Order\Domain\Event;

use DateTimeImmutable;

final class OrderPlaced
{
    /**
     * @param string[] $lines
     */
    public function __construct(
        public readonly string $orderId,
        public readonly array $lines,
        public readonly DateTimeImmutable $placedAt,
    ) {}
}
