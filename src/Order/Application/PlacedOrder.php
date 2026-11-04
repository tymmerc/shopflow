<?php

namespace App\Order\Application;

use App\Order\Domain\Order;

/**
 * Résultat de PlaceOrderHandler : la commande créée et la page du PSP où envoyer le client.
 */
final class PlacedOrder
{
    public function __construct(
        private readonly Order $order,
        private readonly string $paymentUrl,
    ) {
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getPaymentUrl(): string
    {
        return $this->paymentUrl;
    }
}
