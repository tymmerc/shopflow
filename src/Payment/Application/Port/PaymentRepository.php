<?php

namespace App\Payment\Application\Port;

use App\Payment\Domain\Payment;

interface PaymentRepository
{
    public function save(Payment $payment): void;

    public function findByProviderReference(string $providerReference): ?Payment;
}
