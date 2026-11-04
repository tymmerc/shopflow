<?php

namespace App\Tests\Order\Application;

use App\Order\Application\Exception\OrderNotFoundException;
use App\Order\Application\MarkOrderAsPaidHandler;
use App\Order\Domain\Event\OrderPaid;
use App\Order\Domain\Order;
use App\Order\Domain\OrderLine;
use App\Order\Domain\OrderStatus;
use App\Order\Infrastructure\InMemory\InMemoryOrderRepository;
use App\Order\Infrastructure\InMemory\InMemoryStock;
use App\Tests\Order\RecordingCartCleaner;
use App\Tests\Shared\SpyEventPublisher;
use PHPUnit\Framework\TestCase;

final class MarkOrderAsPaidHandlerTest extends TestCase
{
    private InMemoryOrderRepository $orders;
    private RecordingCartCleaner $carts;
    private SpyEventPublisher $events;
    private int $orderId;

    protected function setUp(): void
    {
        $this->orders = new InMemoryOrderRepository();
        $this->carts = new RecordingCartCleaner();
        $this->events = new SpyEventPublisher();

        $order = Order::place(1, 5, [new OrderLine(7, 1999, 2), new OrderLine(8, 500, 1)]);
        $this->orders->save($order);
        $this->orderId = $order->getId();
    }

    public function testPaysTheOrderDecrementsStockAndClearsTheCart(): void
    {
        $stock = new InMemoryStock([7 => 10, 8 => 3]);
        $this->handler($stock)($this->orderId);

        $this->assertSame(OrderStatus::PAID, $this->orders->get($this->orderId)?->getStatus());
        $this->assertSame([8, 2], [$stock->availableQuantity(7), $stock->availableQuantity(8)]);
        $this->assertSame([5], $this->carts->cleared);
        $this->assertCount(1, $this->events->events);
        $this->assertInstanceOf(OrderPaid::class, $this->events->events[0]);
        $this->assertSame($this->orderId, $this->events->events[0]->getOrderId());
    }

    public function testASecondConfirmationIsIgnored(): void
    {
        $stock = new InMemoryStock([7 => 10, 8 => 3]);
        $handler = $this->handler($stock);
        $handler($this->orderId);
        $handler($this->orderId);

        // Stock retiré une seule fois, un seul événement
        $this->assertSame(8, $stock->availableQuantity(7));
        $this->assertCount(1, $this->events->events);
        $this->assertSame([5], $this->carts->cleared);
    }

    public function testCancelsTheOrderWhenStockRanOutDuringPayment(): void
    {
        $stock = new InMemoryStock([7 => 10, 8 => 0]);
        $this->handler($stock)($this->orderId);

        $this->assertSame(OrderStatus::CANCELLED, $this->orders->get($this->orderId)?->getStatus());
        $this->assertSame(10, $stock->availableQuantity(7));
        $this->assertSame([], $this->carts->cleared);
        $this->assertSame([], $this->events->events);
    }

    public function testUnknownOrderThrows(): void
    {
        $this->expectException(OrderNotFoundException::class);

        $this->handler(new InMemoryStock())(999);
    }

    private function handler(InMemoryStock $stock): MarkOrderAsPaidHandler
    {
        return new MarkOrderAsPaidHandler($this->orders, $stock, $this->carts, $this->events);
    }
}
