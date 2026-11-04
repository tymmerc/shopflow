<?php

namespace App\Order\Domain;

enum OrderStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case PAID = 'paid';
    case PAYMENT_FAILED = 'payment_failed';
    // Payée, mais le stock a été épuisé pendant le paiement : à rembourser
    case CANCELLED = 'cancelled';
}
