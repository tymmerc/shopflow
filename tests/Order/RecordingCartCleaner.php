<?php

namespace App\Tests\Order;

use App\Order\Application\Port\CartCleaner;

/**
 * Note les paniers vidés pour que les tests puissent les vérifier.
 */
final class RecordingCartCleaner implements CartCleaner
{
    /** @var list<int> */
    public array $cleared = [];

    public function clear(int $cartId): void
    {
        $this->cleared[] = $cartId;
    }
}
