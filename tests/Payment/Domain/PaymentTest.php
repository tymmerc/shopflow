<?php

namespace App\Tests\Payment\Domain;

use App\Payment\Domain\Payment;
use App\Payment\Domain\PaymentStatus;
use PHPUnit\Framework\TestCase;

final class PaymentTest extends TestCase
{
    public function testAPaymentMustHaveNoZeroAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Payment::start(1, 0);
    }

    public function testAPaymentMustHaveAPositiveAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Payment::start(1, -1);
    }

    public function testASucceededPaymentCannotFailAfterwards(): void
    {
        $payment = Payment::start(1, 4498);
        $payment->markSucceeded();

        $this->assertSame(PaymentStatus::SUCCEEDED, $payment->getStatus());

        $this->expectException(\LogicException::class);
        $payment->markFailed();
    }
}
