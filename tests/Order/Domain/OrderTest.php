<?php

namespace App\Tests\Order\Domain;

use App\Order\Domain\Exception\EmptyCartException;
use App\Order\Domain\Order;
use App\Order\Domain\OrderLine;
use App\Order\Domain\OrderStatus;
use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    public function testANewOrderWaitsForPayment(): void
    {
        $order = Order::place(1, 5, [new OrderLine(7, 1999, 2), new OrderLine(8, 500, 1)]);

        $this->assertSame(OrderStatus::PENDING_PAYMENT, $order->getStatus());
        $this->assertTrue($order->isPendingPayment());
        $this->assertSame(5, $order->getCartId());
        $this->assertSame(4498, $order->totalCents());
        $this->assertEqualsWithDelta(time(), $order->getPlacedAt()->getTimestamp(), 5);
    }

    public function testAnOrderNeedsAtLeastOneLine(): void
    {
        $this->expectException(EmptyCartException::class);

        Order::place(1, 5, []);
    }

    public function testMarkPaid(): void
    {
        $order = Order::place(1, 5, [new OrderLine(7, 1999, 1)]);
        $order->markPaid();

        $this->assertSame(OrderStatus::PAID, $order->getStatus());
        $this->assertFalse($order->isPendingPayment());
    }

    public function testMarkPaymentFailed(): void
    {
        $order = Order::place(1, 5, [new OrderLine(7, 1999, 1)]);
        $order->markPaymentFailed();

        $this->assertSame(OrderStatus::PAYMENT_FAILED, $order->getStatus());
    }

    public function testCancel(): void
    {
        $order = Order::place(1, 5, [new OrderLine(7, 1999, 1)]);
        $order->cancel();

        $this->assertSame(OrderStatus::CANCELLED, $order->getStatus());
        $this->assertFalse($order->isPendingPayment());
    }

    public function testAPaidOrderCannotFailAfterwards(): void
    {
        $order = Order::place(1, 5, [new OrderLine(7, 1999, 1)]);
        $order->markPaid();

        $this->expectException(\LogicException::class);
        $order->markPaymentFailed();
    }

    public function testAnOrderCannotBePaidTwice(): void
    {
        $order = Order::place(1, 5, [new OrderLine(7, 1999, 1)]);
        $order->markPaid();

        $this->expectException(\LogicException::class);
        $order->markPaid();
    }
}
