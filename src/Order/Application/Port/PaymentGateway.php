<?php

namespace App\Order\Application\Port;


interface PaymentGateway
{
    public function charge(
    OrderId $id, Amount $amount
    ): PaymentResult;
}
