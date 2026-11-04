<?php

namespace App\Payment\Application;

use App\Payment\Application\Exception\PaymentNotFoundException;
use App\Payment\Application\Port\PaymentRepository;
use App\Payment\Domain\Event\PaymentFailed;
use App\Payment\Domain\Event\PaymentSucceeded;
use App\Shared\Application\EventPublisher;

/**
 * Enregistre la réponse du PSP. Appelé par la page de paiement factice, et plus tard par le webhook du vrai PSP.
 */
final class ConfirmPaymentHandler
{
    public function __construct(
        private readonly PaymentRepository $payments,
        private readonly EventPublisher $events,
    ) {
    }

    public function __invoke(string $providerReference, bool $succeeded): void
    {
        $payment = $this->payments->findByProviderReference($providerReference)
            ?? throw PaymentNotFoundException::withProviderReference($providerReference);

        // Déjà traité (double-clic, webhook renvoyé par le PSP) : on ignore au lieu de planter
        if (!$payment->isPending()) {
            return;
        }

        if ($succeeded) {
            $payment->markSucceeded();
        } else {
            $payment->markFailed();
        }
        $this->payments->save($payment);

        // Publié après l'enregistrement : les listeners trouvent le paiement déjà à jour
        $this->events->publish($succeeded
            ? new PaymentSucceeded($payment->getOrderId())
            : new PaymentFailed($payment->getOrderId()));
    }
}
