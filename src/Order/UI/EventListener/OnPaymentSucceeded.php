<?php

namespace App\Order\UI\EventListener;

use App\Order\Application\MarkOrderAsPaidHandler;
use App\Payment\Domain\Event\PaymentSucceeded;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Adaptateur primaire : réagit à l'événement du contexte Payment, sans logique propre.
 */
#[AsEventListener(event: PaymentSucceeded::class)]
final class OnPaymentSucceeded
{
    public function __construct(private readonly MarkOrderAsPaidHandler $markOrderAsPaid)
    {
    }

    public function __invoke(PaymentSucceeded $event): void
    {
        ($this->markOrderAsPaid)($event->getOrderId());
    }
}
