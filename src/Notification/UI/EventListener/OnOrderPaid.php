<?php

namespace App\Notification\UI\EventListener;

use App\Notification\Application\SendOrderConfirmationHandler;
use App\Order\Domain\Event\OrderPaid;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Adaptateur primaire : réagit à l'événement du contexte Order, sans logique propre.
 */
#[AsEventListener(event: OrderPaid::class)]
final class OnOrderPaid
{
    public function __construct(private readonly SendOrderConfirmationHandler $sendOrderConfirmation)
    {
    }

    public function __invoke(OrderPaid $event): void
    {
        ($this->sendOrderConfirmation)($event->getOrderId());
    }
}
