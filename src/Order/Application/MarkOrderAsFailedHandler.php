<?php

namespace App\Order\Application;

use App\Order\Application\Exception\OrderNotFoundException;
use App\Order\Application\Port\OrderRepository;

/**
 * Le paiement a été refusé : la commande passe en échec. Le stock et le panier ne bougent pas,
 * le client peut réessayer depuis son panier.
 */
final class MarkOrderAsFailedHandler
{
    public function __construct(private readonly OrderRepository $orders)
    {
    }

    public function __invoke(int $orderId): void
    {
        $order = $this->orders->get($orderId) ?? throw OrderNotFoundException::withId($orderId);

        if (!$order->isPendingPayment()) {
            return;
        }

        $order->markPaymentFailed();
        $this->orders->save($order);
    }
}
