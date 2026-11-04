<?php

namespace App\Payment\Application;

use App\Payment\Application\Port\PaymentGateway;
use App\Payment\Application\Port\PaymentRepository;
use App\Payment\Domain\Payment;

/**
 * Démarre le paiement d'une commande et renvoie l'URL où envoyer le client pour payer.
 */
final class StartPaymentHandler
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly PaymentRepository $payments,
    ) {
    }

    /**
     * @param int    $amount    montant en centimes
     * @param string $returnUrl où le PSP renvoie le client après le paiement
     */
    public function __invoke(int $orderId, int $amount, string $returnUrl): string
    {
        $payment = Payment::start($orderId, $amount);
        $session = $this->gateway->initiate(new PaymentRequest($amount, $returnUrl));
        $payment->attachProviderReference($session->getProviderReference());

        // Enregistré après l'appel au PSP : s'il échoue, aucun paiement fantôme sans référence en base
        $this->payments->save($payment);

        return $session->getRedirectUrl();
    }
}
