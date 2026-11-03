<?php

namespace App\Tests\Order\Application;

use App\Cart\CartService;
use App\Entity\Cart;
use App\Entity\CartCatalog;
use App\Entity\Catalog;
use App\Order\Application\PlaceOrderHandler;
use App\Order\Application\Port\PaymentGateway;
use App\Order\Domain\Exception\EmptyCartException;
use App\Order\Infrastructure\InMemory\InMemoryOrderRepository;
use App\Repository\CartRepository;
use App\Repository\CatalogRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class PlaceOrderHandlerTest extends TestCase
{
    private InMemoryOrderRepository $orders;

    protected function setUp(): void
    {
        $this->orders = new InMemoryOrderRepository();
    }

    public function testEmptyCartThrowsException(): void
    {
        $this->expectException(EmptyCartException::class);

        $this->handler(new Cart())(1);
    }

    public function testCreatesOrderFromCartLines(): void
    {
        $catalog = (new Catalog())->setName('T-shirt')->setPrice('19.99')->setStock(10);
        (new \ReflectionProperty(Catalog::class, 'id'))->setValue($catalog, 7);
        $cart = new Cart();
        $cart->addItem((new CartCatalog())->setCatalog($catalog)->setQuantity(2));

        $order = $this->handler($cart)(1);

        $this->assertSame(1, $order->getUserId());
        $this->assertCount(1, $order->getLines());
        $this->assertSame(7, $order->getLines()[0]->getCatalogId());
        $this->assertSame(1999, $order->getLines()[0]->getUnitPriceCents());
        $this->assertSame(3998, $order->totalCents());
        $this->assertSame($order, $this->orders->get($order->getId()));
    }

    private function handler(?Cart $cart): PlaceOrderHandler
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

        return new PlaceOrderHandler(
            $cartService,
            $this->orders,
            $this->createStub(PaymentGateway::class),
        );
    }
}
