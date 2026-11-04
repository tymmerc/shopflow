<?php

namespace App\Order\Application\Port;

/**
 * URL absolue de la page de confirmation d'une commande, où le PSP renvoie le client après le paiement.
 * Un port, parce que générer une URL dépend du routeur Symfony.
 */
interface OrderConfirmationUrl
{
    public function generate(int $orderId): string;
}
