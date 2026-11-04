<?php

namespace App\Payment\Application;

final class PaymentRequest
{
    private int $amount;
    private string $returnUrl;

    /**
     * @param int    $amount    montant en centimes
     * @param string $returnUrl où le PSP renvoie le client après le paiement
     */
    public function __construct(int $amount, string $returnUrl)
    {
        $this->amount = $amount;
        $this->returnUrl = $returnUrl;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }
}
