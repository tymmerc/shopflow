<?php

namespace App\Payment\Application\Port;

use App\Payment\Application\PaymentRequest;
use App\Payment\Application\PaymentSession;

interface PaymentGateway
{
    // Crée le paiement chez le PSP et renvoie où envoyer le client
    public function initiate(PaymentRequest $request): PaymentSession;
}
