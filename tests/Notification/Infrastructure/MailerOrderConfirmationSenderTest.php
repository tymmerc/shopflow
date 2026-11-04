<?php

namespace App\Tests\Notification\Infrastructure;

use App\Entity\Catalog;
use App\Notification\Infrastructure\MailerOrderConfirmationSender;
use App\Order\Domain\Order;
use App\Order\Domain\OrderLine;
use App\Order\Domain\OrderStatus;
use App\Repository\CatalogRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class MailerOrderConfirmationSenderTest extends TestCase
{
    public function testSendsATemplatedEmailWithOnlySimpleValuesInItsContext(): void
    {
        $mug = (new Catalog())->setName('Mug céramique');
        (new \ReflectionProperty(Catalog::class, 'id'))->setValue($mug, 2);
        $catalogs = $this->createStub(CatalogRepository::class);
        $catalogs->method('findBy')->willReturn([$mug]);

        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('https://shop.test/orders/12');

        $sent = null;
        $mailer = $this->createStub(MailerInterface::class);
        $mailer->method('send')->willReturnCallback(static function (RawMessage $message) use (&$sent): void {
            $sent = $message;
        });

        $order = Order::reconstitute(12, 1, 5, [new OrderLine(2, 1250, 2)], OrderStatus::PAID, new \DateTimeImmutable('2026-11-04 10:30'));
        (new MailerOrderConfirmationSender($mailer, $catalogs, $urlGenerator))->send($order, 'client@shopflow.test');

        $this->assertInstanceOf(TemplatedEmail::class, $sent);
        $this->assertSame('client@shopflow.test', $sent->getTo()[0]->getAddress());
        $this->assertSame('Votre commande n° 12 est confirmée', $sent->getSubject());
        $this->assertSame('email/order_confirmation.html.twig', $sent->getHtmlTemplate());

        $context = $sent->getContext();
        $this->assertSame([['name' => 'Mug céramique', 'quantity' => 2, 'unitPrice' => 12.5, 'subtotal' => 25.0]], $context['lines']);
        $this->assertSame(25.0, $context['total']);
        $this->assertSame('https://shop.test/orders/12', $context['orderUrl']);
        // Doit pouvoir partir en file Messenger
        $this->assertEquals($context, unserialize(serialize($context)));
    }
}
