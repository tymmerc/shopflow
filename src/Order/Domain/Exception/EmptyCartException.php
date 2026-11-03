<?php

namespace App\Order\Domain\Exception;

final class EmptyCartException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Impossible de passer commande : le panier est vide.');
    }
}
