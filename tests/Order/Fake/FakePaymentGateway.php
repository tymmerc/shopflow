<?php

namespace App\Tests\Order\Fake;

use App\Order\Application\Port\Amount;
use App\Order\Application\Port\OrderId;
use App\Order\Application\Port\PaymentGateway;
use App\Order\Application\Port\PaymentResult;

final class FakePaymentGateway implements PaymentGateway
{
    public function __construct(
        private readonly bool $accepted,
    ) {
    }

    public function charge(OrderId $id, Amount $amount): PaymentResult
    {
        return new PaymentResult($this->accepted);
    }
}
