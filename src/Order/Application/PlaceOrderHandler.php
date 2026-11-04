<?php

namespace App\Order\Application;

use App\Cart\CartService;
use App\Order\Application\Port\OrderConfirmationUrl;
use App\Order\Application\Port\OrderRepository;
use App\Order\Application\Port\Stock;
use App\Order\Domain\Exception\EmptyCartException;
use App\Order\Domain\Exception\InsufficientStockException;
use App\Order\Domain\Order;
use App\Order\Domain\OrderLine;
use App\Payment\Application\StartPaymentHandler;

/**
 * « Passer commande » : crée la commande en attente de paiement à partir du panier,
 * puis démarre son paiement. Le stock n'est retiré qu'une fois le paiement réussi (étape 9).
 */
final class PlaceOrderHandler
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly OrderRepository $orders,
        private readonly Stock $stock,
        private readonly StartPaymentHandler $startPayment,
        private readonly OrderConfirmationUrl $confirmationUrl,
    ) {
    }

    /**
     * @throws EmptyCartException
     * @throws InsufficientStockException
     */
    public function __invoke(int $userId): PlacedOrder
    {
        $cart = $this->cartService->current();
        if (null === $cart || $cart->getItems()->isEmpty()) {
            throw new EmptyCartException();
        }

        $lines = [];
        foreach ($cart->getItems() as $cartItem) {
            $catalogId = $cartItem->getCatalog()->getId();
            // Le stock a pu baisser depuis l'ajout au panier
            if ($this->stock->availableQuantity($catalogId) < $cartItem->getQuantity()) {
                throw new InsufficientStockException($catalogId);
            }
            $lines[] = new OrderLine(
                $catalogId,
                (int) round((float) $cartItem->getCatalog()->getPrice() * 100),
                $cartItem->getQuantity(),
            );
        }

        $order = Order::place(
            $userId,
            $cart->getId() ?? throw new \LogicException('Le panier doit être enregistré avant de passer commande.'),
            $lines,
        );
        $this->orders->save($order);

        $orderId = $order->getId() ?? throw new \LogicException('La commande doit avoir un id après son enregistrement.');
        $paymentUrl = ($this->startPayment)($orderId, $order->totalCents(), $this->confirmationUrl->generate($orderId));

        return new PlacedOrder($order, $paymentUrl);
    }
}
