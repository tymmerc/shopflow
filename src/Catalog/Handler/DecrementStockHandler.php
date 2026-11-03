<?php

namespace Catalogue\Handler;

use Command\Domain\Event\CommandePassee;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DecrementerStockHandler
{
    public function __invoke(CommandePassee $e): void
    { 
        /* idempotent : ignore si déjà traité */ 
    }
}
