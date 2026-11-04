<?php

namespace App\Payment\Domain;

final class Payment
{
    private ?int $id = null;
    private PaymentStatus $status = PaymentStatus::PENDING;
    // Connue seulement après l'appel au PSP
    private ?string $providerReference = null;

    private function __construct(
        private readonly int $orderId,
        private readonly int $amount,
    ) {
    }

    /**
     * @param int $amount montant en centimes
     */
    public static function start(int $orderId, int $amount): self
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Le montant d\'un paiement doit être positif.');
        }

        return new self($orderId, $amount);
    }

    // Recharge un paiement déjà enregistré (utilisé par le repository)
    public static function reconstitute(int $id, int $orderId, int $amount, PaymentStatus $status, ?string $providerReference): self
    {
        $payment = new self($orderId, $amount);
        $payment->id = $id;
        $payment->status = $status;
        $payment->providerReference = $providerReference;

        return $payment;
    }

    public function assignId(int $id): void
    {
        if (null !== $this->id) {
            throw new \LogicException('Ce paiement a déjà un id.');
        }
        $this->id = $id;
    }

    public function attachProviderReference(string $providerReference): void
    {
        if (null !== $this->providerReference) {
            throw new \LogicException('Ce paiement a déjà une référence PSP.');
        }
        $this->providerReference = $providerReference;
    }

    public function markSucceeded(): void
    {
        // refuser de modifier le statut si le paiement est different de pending
        if (!$this->isPending()) {
            throw new \LogicException('Cannot mark payment as succeeded unless it is pending.');
        }
        $this->status = PaymentStatus::SUCCEEDED;
    }

    public function markFailed(): void
    {
        // refuser de modifier le statut si le paiement est different de pending
        if (!$this->isPending()) {
            throw new \LogicException('Cannot mark payment as failed unless it is pending.');
        }
        $this->status = PaymentStatus::FAILED;
    }

    // À vérifier avant markSucceeded/markFailed : un webhook reçu deux fois doit être ignoré, pas planter
    public function isPending(): bool
    {
        return PaymentStatus::PENDING === $this->status;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    public function getProviderReference(): ?string
    {
        return $this->providerReference;
    }
}
