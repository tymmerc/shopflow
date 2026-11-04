<?php

namespace App\Payment\Application;

final class PaymentSession
{
    private string $providerReference;
    private string $redirectUrl;

    /**
     * @param string $providerReference identifiant du paiement chez le PSP
     * @param string $redirectUrl       page du PSP où envoyer le client pour payer
     */
    public function __construct(string $providerReference, string $redirectUrl)
    {
        $this->providerReference = $providerReference;
        $this->redirectUrl = $redirectUrl;
    }

    public function getProviderReference(): string
    {
        return $this->providerReference;
    }

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }
}
