<?php

namespace App\Notification\Application;

use App\Notification\Application\Port\OrderConfirmationSender;
use App\Order\Application\Exception\OrderNotFoundException;
use App\Order\Application\Port\OrderRepository;

/**
 * Envoie l'e-mail de confirmation d'une commande payée (EF-10).
 */
final class SendOrderConfirmationHandler
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderConfirmationSender $sender,
        // TODO: remplacer par l'e-mail du client connecté quand l'authentification existera
        private readonly string $recipient,
    ) {
    }

    public function __invoke(int $orderId): void
    {
        $order = $this->orders->get($orderId) ?? throw OrderNotFoundException::withId($orderId);

        // On ne confirme qu'une commande réellement payée (pas une commande annulée entre-temps)
        if (!$order->isPaid()) {
            return;
        }

        $this->sender->send($order, $this->recipient);
    }
}
