<?php

namespace App\Order\Domain\Exception;

final class InsufficientStockException extends \DomainException
{
    public function __construct(private readonly int $catalogId)
    {
        parent::__construct(\sprintf('Stock insuffisant pour l\'article %d.', $catalogId));
    }

    public function getCatalogId(): int
    {
        return $this->catalogId;
    }
}
