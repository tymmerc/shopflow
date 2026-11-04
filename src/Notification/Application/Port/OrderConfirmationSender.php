<?php

namespace App\Notification\Application\Port;

use App\Order\Domain\Order;

interface OrderConfirmationSender
{
    public function send(Order $order, string $recipient): void;
}
