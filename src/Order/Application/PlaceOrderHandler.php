<?php

namespace App\Order\Application;

use App\Cart\CartService;
use App\Order\Application\Port\OrderRepository;
use App\Order\Application\Port\PaymentGateway;
use App\Order\Domain\OrderLine;
use App\Order\Domain\Order;
use App\Order\Domain\Exception\EmptyCartException;

final class PlaceOrderHandler
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly OrderRepository $orders,
        private readonly PaymentGateway $payment,
    ) {
    }

    public function __invoke(int $userId): Order
    {
        $cart = $this->cartService->current();
        if (null === $cart) {
            throw new EmptyCartException();
        }

        $lines = [];
        foreach ($cart->getItems() as $cartItem) {
            $lines[] = new OrderLine(
                $cartItem->getCatalog()->getId(),
                (int) round((float) $cartItem->getCatalog()->getPrice() * 100),
                $cartItem->getQuantity(),
            );
        }

        $order = Order::place($userId, $lines);
        $this->orders->save($order);

        return $order;
    }
}
