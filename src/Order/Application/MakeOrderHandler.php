<?php

namespace App\Order\Application;

use App\Order\Application\Port\OrderRepository;
use App\Order\Application\Port\PaymentGateway;
use App\Order\Domain\Order;

final class MakeOrderHandler
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly PaymentGateway $payment,
    ) {
    }

    public function __invoke(MakeOrder $command): Order
    {
        $order = Order::place($command->userId, $command->cart);
        $this->orders->save($order);

        return $order;
    }
}
