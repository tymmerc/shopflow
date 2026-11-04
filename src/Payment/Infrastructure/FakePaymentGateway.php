<?php

namespace App\Payment\Infrastructure;

use App\Payment\Application\PaymentRequest;
use App\Payment\Application\PaymentSession;
use App\Payment\Application\Port\PaymentGateway;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Faux PSP pour le dev et les tests : le client est envoyé sur notre propre page
 * /payment/fake/{reference}?return=…, qui imite la page de paiement d'un vrai prestataire.
 * Un futur StripePaymentGateway renverra à la place l'URL de Stripe Checkout.
 */
final class FakePaymentGateway implements PaymentGateway
{
    // Nom de la route de la page factice (Payment/UI, étape 6)
    public const ROUTE = 'payment_fake_show';

    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function initiate(PaymentRequest $request): PaymentSession
    {
        $reference = 'fake_'.bin2hex(random_bytes(12));

        return new PaymentSession(
            $reference,
            // Un vrai PSP garde l'URL de retour de son côté ; le faux la transporte dans l'URL de sa page
            $this->urlGenerator->generate(self::ROUTE, ['reference' => $reference, 'return' => $request->getReturnUrl()]),
        );
    }
}
