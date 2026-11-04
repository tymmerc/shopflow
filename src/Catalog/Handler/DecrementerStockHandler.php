<?php

namespace App\Catalog\Handler;

use Command\Domain\Event\CommandPlaced;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DecrementerStockHandler
{
    public function __invoke(CommandPlaced $e): void
    { 
        /* idempotent : ignore si déjà traité */ 
    }
}
