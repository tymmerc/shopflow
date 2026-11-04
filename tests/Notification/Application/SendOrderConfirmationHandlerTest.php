<?php

namespace App\Tests\Notification\Application;

use App\Notification\Application\Port\OrderConfirmationSender;
use App\Notification\Application\SendOrderConfirmationHandler;
use App\Order\Application\Exception\OrderNotFoundException;
use App\Order\Domain\Order;
use App\Order\Domain\OrderLine;
use App\Order\Infrastructure\InMemory\InMemoryOrderRepository;
use PHPUnit\Framework\TestCase;

final class SendOrderConfirmationHandlerTest extends TestCase
{
    private InMemoryOrderRepository $orders;
    /** @var list<array{Order, string}> */
    private array $sent = [];
    private SendOrderConfirmationHandler $handler;

    protected function setUp(): void
    {
        $this->orders = new InMemoryOrderRepository();
        $sender = $this->createStub(OrderConfirmationSender::class);
        $sender->method('send')->willReturnCallback(function (Order $order, string $recipient): void {
            $this->sent[] = [$order, $recipient];
        });
        $this->handler = new SendOrderConfirmationHandler($this->orders, $sender, 'client@shopflow.test');
    }

    public function testSendsTheConfirmationOfAPaidOrder(): void
    {
        $order = $this->order();
        $order->markPaid();
        $this->orders->save($order);

        ($this->handler)($order->getId());

        $this->assertCount(1, $this->sent);
        $this->assertSame($order, $this->sent[0][0]);
        $this->assertSame('client@shopflow.test', $this->sent[0][1]);
    }

    public function testSendsNothingForAnOrderThatIsNotPaid(): void
    {
        $order = $this->order();
        $order->cancel();
        $this->orders->save($order);

        ($this->handler)($order->getId());

        $this->assertSame([], $this->sent);
    }

    public function testUnknownOrderThrows(): void
    {
        $this->expectException(OrderNotFoundException::class);

        ($this->handler)(999);
    }

    private function order(): Order
    {
        $order = Order::place(1, 5, [new OrderLine(7, 1999, 2)]);
        $this->orders->save($order);

        return $order;
    }
}
