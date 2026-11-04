<?php

namespace App\Payment\Application\Exception;

final class PaymentNotFoundException extends \RuntimeException
{
    public static function withProviderReference(string $providerReference): self
    {
        return new self(\sprintf('Aucun paiement avec la référence PSP « %s ».', $providerReference));
    }
}
