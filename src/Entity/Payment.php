<?php

namespace App\Entity;

use App\Payment\Domain\PaymentStatus;
use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;

// Modèle de persistance du paiement ; le modèle métier est App\Payment\Domain\Payment
#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Simple id, pas de relation : le contexte Payment ne dépend pas du contexte Order
    #[ORM\Column]
    private ?int $orderId = null;

    // En centimes
    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column(length: 20, enumType: PaymentStatus::class)]
    private ?PaymentStatus $status = null;

    #[ORM\Column(length: 64, unique: true)]
    private ?string $providerReference = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): static
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getStatus(): ?PaymentStatus
    {
        return $this->status;
    }

    public function setStatus(PaymentStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getProviderReference(): ?string
    {
        return $this->providerReference;
    }

    public function setProviderReference(string $providerReference): static
    {
        $this->providerReference = $providerReference;

        return $this;
    }
}
