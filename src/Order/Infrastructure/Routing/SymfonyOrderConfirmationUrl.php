<?php

namespace App\Order\Infrastructure\Routing;

use App\Order\Application\Port\OrderConfirmationUrl;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SymfonyOrderConfirmationUrl implements OrderConfirmationUrl
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function generate(int $orderId): string
    {
        // Absolue : un vrai PSP (Stripe…) renvoie le client depuis son propre domaine
        return $this->urlGenerator->generate('app_order_show', ['id' => $orderId], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
