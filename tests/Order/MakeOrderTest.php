<?php

namespace App\Tests\Order;

use App\Order\Application\MakeOrder;
use App\Order\Application\MakeOrderHandler;
use App\Order\Domain\Exception\EmptyCartException;
use App\Order\Infrastructure\InMemory\InMemoryOrderRepository;
use App\Tests\Order\Fake\FakePaymentGateway;
use PHPUnit\Framework\TestCase;

final class MakeOrderTest extends TestCase
{
    public function testEmptyCartThrowsException(): void
    {
        $handler = new MakeOrderHandler(new InMemoryOrderRepository(), new FakePaymentGateway(accepted: true));
        $this->expectException(EmptyCartException::class);
        $handler(new MakeOrder(userId: 1, cart: []));
    }
}
