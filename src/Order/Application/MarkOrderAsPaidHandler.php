<?php

namespace App\Order\Application;

use App\Order\Application\Exception\OrderNotFoundException;
use App\Order\Application\Port\CartCleaner;
use App\Order\Application\Port\OrderRepository;
use App\Order\Application\Port\Stock;
use App\Order\Domain\Event\OrderPaid;
use App\Order\Domain\Exception\InsufficientStockException;
use App\Shared\Application\EventPublisher;

/**
 * Le paiement a réussi : on retire le stock, la commande passe en payée, le panier est vidé.
 */
final class MarkOrderAsPaidHandler
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly Stock $stock,
        private readonly CartCleaner $carts,
        private readonly EventPublisher $events,
    ) {
    }

    public function __invoke(int $orderId): void
    {
        $order = $this->orders->get($orderId) ?? throw OrderNotFoundException::withId($orderId);

        // Événement reçu deux fois (double-clic, webhook renvoyé) : déjà traité, on ignore
        if (!$order->isPendingPayment()) {
            return;
        }

        $quantities = [];
        foreach ($order->getLines() as $line) {
            $quantities[$line->getCatalogId()] = ($quantities[$line->getCatalogId()] ?? 0) + $line->getQuantity();
        }

        try {
            $this->stock->decrement($quantities);
        } catch (InsufficientStockException) {
            // Quelqu'un a acheté les derniers exemplaires pendant que le client payait.
            // Le stock n'a pas bougé (tout ou rien) ; la commande est annulée, le panier gardé.
            $order->cancel();
            $this->orders->save($order);

            return;
        }

        $order->markPaid();
        $this->orders->save($order);

        if (null !== $order->getCartId()) {
            $this->carts->clear($order->getCartId());
        }

        $this->events->publish(new OrderPaid($orderId));
    }
}
