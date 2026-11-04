<?php

namespace App\Payment\Infrastructure\InMemory;

use App\Payment\Application\Port\PaymentRepository;
use App\Payment\Domain\Payment;

final class InMemoryPaymentRepository implements PaymentRepository
{
    /** @var array<string, Payment> indexé par référence PSP */
    private array $payments = [];
    private int $nextId = 1;

    public function save(Payment $payment): void
    {
        $reference = $payment->getProviderReference()
            ?? throw new \LogicException('Un paiement doit avoir sa référence PSP avant d\'être enregistré.');

        if (null === $payment->getId()) {
            $payment->assignId($this->nextId++);
        }
        $this->payments[$reference] = $payment;
    }

    public function findByProviderReference(string $providerReference): ?Payment
    {
        return $this->payments[$providerReference] ?? null;
    }
}
