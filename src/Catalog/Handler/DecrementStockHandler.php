<?php

namespace App\Catalog\Handler;

use App\Order\Domain\Event\OrderPlaced;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DecrementStockHandler
{
    public function __invoke(OrderPlaced $event): void
    {
        // TODO : décrémenter le stock, idempotent (ignorer si déjà traité)
    }
}
