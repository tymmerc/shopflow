<?php

namespace App\Order\Application\Exception;

final class OrderNotFoundException extends \RuntimeException
{
    public static function withId(int $orderId): self
    {
        return new self(\sprintf('Aucune commande n° %d.', $orderId));
    }
}
