<?php

namespace App\Tests\Order\Application;

use App\Order\Application\Exception\OrderNotFoundException;
use App\Order\Application\MarkOrderAsFailedHandler;
use App\Order\Domain\Order;
use App\Order\Domain\OrderLine;
use App\Order\Domain\OrderStatus;
use App\Order\Infrastructure\InMemory\InMemoryOrderRepository;
use PHPUnit\Framework\TestCase;

final class MarkOrderAsFailedHandlerTest extends TestCase
{
    private InMemoryOrderRepository $orders;
    private MarkOrderAsFailedHandler $handler;

    protected function setUp(): void
    {
        $this->orders = new InMemoryOrderRepository();
        $this->handler = new MarkOrderAsFailedHandler($this->orders);
    }

    public function testMarksThePaymentAsFailed(): void
    {
        $order = $this->pendingOrder();
        ($this->handler)($order->getId());

        $this->assertSame(OrderStatus::PAYMENT_FAILED, $this->orders->get($order->getId())?->getStatus());
    }

    public function testDoesNotTouchAnOrderAlreadyPaid(): void
    {
        $order = $this->pendingOrder();
        $order->markPaid();
        $this->orders->save($order);

        ($this->handler)($order->getId());

        $this->assertSame(OrderStatus::PAID, $this->orders->get($order->getId())?->getStatus());
    }

    public function testUnknownOrderThrows(): void
    {
        $this->expectException(OrderNotFoundException::class);

        ($this->handler)(999);
    }

    private function pendingOrder(): Order
    {
        $order = Order::place(1, 5, [new OrderLine(7, 1999, 1)]);
        $this->orders->save($order);

        return $order;
    }
}
