<?php

namespace App\Order\Domain;

use App\Order\Domain\Exception\EmptyCartException;

final class Order
{
    private ?int $id = null;

    /**
     * @param OrderLine[] $lines
     */
    private function __construct(
        private readonly int $userId,
        // Panier d'origine, vidé au paiement. Gardé par id : la confirmation du PSP (webhook)
        // arrive sans le cookie du client, on ne peut pas retrouver son panier autrement.
        private readonly ?int $cartId,
        private readonly array $lines,
        private OrderStatus $status,
        private readonly \DateTimeImmutable $placedAt,
    ) {
    }

    /**
     * Les prix des lignes sont figés : un changement de prix au catalogue ne modifie pas la commande.
     *
     * @param OrderLine[] $lines
     */
    public static function place(int $userId, int $cartId, array $lines): self
    {
        if ([] === $lines) {
            throw new EmptyCartException();
        }

        return new self($userId, $cartId, $lines, OrderStatus::PENDING_PAYMENT, new \DateTimeImmutable());
    }

    /**
     * Recharge une commande déjà enregistrée (utilisé par les repositories).
     *
     * @param OrderLine[] $lines
     */
    public static function reconstitute(
        int $id,
        int $userId,
        ?int $cartId,
        array $lines,
        OrderStatus $status,
        \DateTimeImmutable $placedAt,
    ): self {
        $order = new self($userId, $cartId, $lines, $status, $placedAt);
        $order->id = $id;

        return $order;
    }

    public function assignId(int $id): void
    {
        if (null !== $this->id) {
            throw new \LogicException('Cette commande a déjà un id.');
        }

        $this->id = $id;
    }

    public function markPaid(): void
    {
        if (!$this->isPendingPayment()) {
            throw new \LogicException(\sprintf('Seule une commande en attente de paiement peut être payée (statut : %s).', $this->status->value));
        }
        $this->status = OrderStatus::PAID;
    }

    public function markPaymentFailed(): void
    {
        if (!$this->isPendingPayment()) {
            throw new \LogicException(\sprintf('Seule une commande en attente de paiement peut échouer (statut : %s).', $this->status->value));
        }
        $this->status = OrderStatus::PAYMENT_FAILED;
    }

    /**
     * Paiement accepté mais impossible de livrer (stock épuisé entre la commande et le paiement).
     * TODO: déclencher le remboursement via le PSP quand un vrai prestataire sera branché.
     */
    public function cancel(): void
    {
        if (!$this->isPendingPayment()) {
            throw new \LogicException(\sprintf('Seule une commande en attente de paiement peut être annulée (statut : %s).', $this->status->value));
        }
        $this->status = OrderStatus::CANCELLED;
    }

    public function isPaid(): bool
    {
        return OrderStatus::PAID === $this->status;
    }

    // À vérifier avant markPaid/markPaymentFailed/cancel : un événement de paiement reçu deux fois doit être ignoré
    public function isPendingPayment(): bool
    {
        return OrderStatus::PENDING_PAYMENT === $this->status;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getCartId(): ?int
    {
        return $this->cartId;
    }

    /**
     * @return OrderLine[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function getPlacedAt(): \DateTimeImmutable
    {
        return $this->placedAt;
    }

    public function totalCents(): int
    {
        return array_sum(array_map(static fn (OrderLine $line): int => $line->subtotalCents(), $this->lines));
    }
}
