<?php

namespace App\Order\Application\Port;

/**
 * Vide le panier d'origine d'une commande payée (port vers le contexte Cart).
 */
interface CartCleaner
{
    public function clear(int $cartId): void;
}
