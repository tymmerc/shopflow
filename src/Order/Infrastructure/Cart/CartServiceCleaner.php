<?php

namespace App\Order\Infrastructure\Cart;

use App\Cart\CartService;
use App\Order\Application\Port\CartCleaner;

final class CartServiceCleaner implements CartCleaner
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function clear(int $cartId): void
    {
        $this->cartService->clearById($cartId);
    }
}
