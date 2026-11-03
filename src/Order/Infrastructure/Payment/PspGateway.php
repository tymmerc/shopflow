<?php

namespace App\Order\Infrastructure\Payment;

use App\Order\Application\Port\OrderId;
use App\Order\Application\Port\Amount;
use App\Order\Application\Port\PaymentGateway;
use App\Order\Application\Port\PaymentResult;

/**
 * Paiement factice en attendant le vrai prestataire (ENF-04).
 */
final class PspGateway implements PaymentGateway
{
    public function charge(OrderId $id, Amount $amount): PaymentResult
    {
        // TODO: appeler l'API du prestataire (ex : Stripe), avec la clé dans .env.local, jamais en dur.
        throw new \LogicException('Paiement pas encore implémenté.');
    }
}
