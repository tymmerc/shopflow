<?php

namespace App\Order\UI\EventListener;

use App\Order\Application\MarkOrderAsFailedHandler;
use App\Payment\Domain\Event\PaymentFailed;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Adaptateur primaire : réagit à l'événement du contexte Payment, sans logique propre.
 */
#[AsEventListener(event: PaymentFailed::class)]
final class OnPaymentFailed
{
    public function __construct(private readonly MarkOrderAsFailedHandler $markOrderAsFailed)
    {
    }

    public function __invoke(PaymentFailed $event): void
    {
        ($this->markOrderAsFailed)($event->getOrderId());
    }
}
