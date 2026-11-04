<?php

namespace App\Tests\Order\Application;

use App\Cart\CartService;
use App\Entity\Cart;
use App\Entity\CartCatalog;
use App\Entity\Catalog;
use App\Order\Application\PlaceOrderHandler;
use App\Order\Application\Port\OrderConfirmationUrl;
use App\Order\Domain\Exception\EmptyCartException;
use App\Order\Domain\Exception\InsufficientStockException;
use App\Order\Domain\OrderStatus;
use App\Order\Infrastructure\InMemory\InMemoryOrderRepository;
use App\Order\Infrastructure\InMemory\InMemoryStock;
use App\Payment\Application\PaymentRequest;
use App\Payment\Application\PaymentSession;
use App\Payment\Application\Port\PaymentGateway;
use App\Payment\Application\StartPaymentHandler;
use App\Payment\Domain\PaymentStatus;
use App\Payment\Infrastructure\InMemory\InMemoryPaymentRepository;
use App\Repository\CartRepository;
use App\Repository\CatalogRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class PlaceOrderHandlerTest extends TestCase
{
    private InMemoryOrderRepository $orders;
    private InMemoryPaymentRepository $payments;
    /** @var list<PaymentRequest> demandes reçues par le faux PSP */
    private array $paymentRequests = [];

    protected function setUp(): void
    {
        $this->orders = new InMemoryOrderRepository();
        $this->payments = new InMemoryPaymentRepository();
    }

    public function testEmptyCartThrowsException(): void
    {
        $this->expectException(EmptyCartException::class);

        $this->handler(new Cart())(1);
    }

    public function testNoCartThrowsException(): void
    {
        $this->expectException(EmptyCartException::class);

        $this->handler(null)(1);
    }

    public function testCreatesAPendingOrderFromCartLines(): void
    {
        $placed = $this->handler($this->cartWithTwoTShirts())(1);
        $order = $placed->getOrder();

        $this->assertSame(1, $order->getUserId());
        $this->assertSame(5, $order->getCartId());
        $this->assertSame(OrderStatus::PENDING_PAYMENT, $order->getStatus());
        $this->assertCount(1, $order->getLines());
        $this->assertSame(7, $order->getLines()[0]->getCatalogId());
        $this->assertSame(1999, $order->getLines()[0]->getUnitPriceCents());
        $this->assertSame(3998, $order->totalCents());
        $this->assertSame($order, $this->orders->get($order->getId()));
    }

    public function testStartsThePaymentOfTheOrderTotal(): void
    {
        $placed = $this->handler($this->cartWithTwoTShirts())(1);
        $orderId = $placed->getOrder()->getId();

        $this->assertSame('https://psp.test/pay/psp_1', $placed->getPaymentUrl());
        $this->assertCount(1, $this->paymentRequests);
        $this->assertSame(3998, $this->paymentRequests[0]->getAmount());
        $this->assertSame("https://shop.test/orders/$orderId", $this->paymentRequests[0]->getReturnUrl());

        $payment = $this->payments->findByProviderReference('psp_1');
        $this->assertSame($orderId, $payment?->getOrderId());
        $this->assertSame(PaymentStatus::PENDING, $payment?->getStatus());
    }

    public function testRefusesWhenTheStockIsNoLongerAvailable(): void
    {
        try {
            $this->handler($this->cartWithTwoTShirts(), stock: 1)(1);
            $this->fail('InsufficientStockException attendue');
        } catch (InsufficientStockException $e) {
            $this->assertSame(7, $e->getCatalogId());
        }

        // Ni commande ni paiement créés
        $this->assertSame([], $this->orders->findByUser(1));
        $this->assertCount(0, $this->paymentRequests);
    }

    private function cartWithTwoTShirts(): Cart
    {
        $catalog = (new Catalog())->setName('T-shirt')->setPrice('19.99')->setStock(10);
        (new \ReflectionProperty(Catalog::class, 'id'))->setValue($catalog, 7);
        $cart = new Cart();
        (new \ReflectionProperty(Cart::class, 'id'))->setValue($cart, 5);
        $cart->addItem((new CartCatalog())->setCatalog($catalog)->setQuantity(2));

        return $cart;
    }

    private function handler(?Cart $cart, int $stock = 10): PlaceOrderHandler
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request(cookies: ['cart' => 'token']));

        $carts = $this->createStub(CartRepository::class);
        $carts->method('findOneBy')->willReturn($cart);

        $cartService = new CartService(
            $requestStack,
            $carts,
            $this->createStub(CatalogRepository::class),
            $this->createStub(EntityManagerInterface::class),
        );

        $gateway = $this->createStub(PaymentGateway::class);
        $gateway->method('initiate')->willReturnCallback(function (PaymentRequest $request): PaymentSession {
            $this->paymentRequests[] = $request;
            $reference = 'psp_'.\count($this->paymentRequests);

            return new PaymentSession($reference, "https://psp.test/pay/$reference");
        });

        $confirmationUrl = $this->createStub(OrderConfirmationUrl::class);
        $confirmationUrl->method('generate')->willReturnCallback(static fn (int $id): string => "https://shop.test/orders/$id");

        return new PlaceOrderHandler(
            $cartService,
            $this->orders,
            new InMemoryStock([7 => $stock]),
            new StartPaymentHandler($gateway, $this->payments),
            $confirmationUrl,
        );
    }
}
