<?php

namespace App\Order\Application;

use App\Order\Domain\OrderLine;

final class MakeOrder
{
    /**
     * @param OrderLine[] $cart
     */
    public function __construct(
        public readonly int $userId,
        public readonly array $cart,
    ) {
    }
}
