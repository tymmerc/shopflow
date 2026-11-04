<?php

namespace App\Tests\Payment\Infrastructure;

use App\Payment\Application\PaymentRequest;
use App\Payment\Infrastructure\FakePaymentGateway;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class FakePaymentGatewayTest extends TestCase
{
    public function testSendsTheCustomerToTheFakePaymentPage(): void
    {
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturnCallback(
            static fn (string $route, array $parameters): string => "/$route/{$parameters['reference']}?return={$parameters['return']}",
        );

        $session = (new FakePaymentGateway($urlGenerator))->initiate(new PaymentRequest(1999, '/orders/1'));

        $this->assertStringStartsWith('fake_', $session->getProviderReference());
        $this->assertSame('/payment_fake_show/'.$session->getProviderReference().'?return=/orders/1', $session->getRedirectUrl());
    }

    public function testEachPaymentGetsItsOwnReference(): void
    {
        $gateway = new FakePaymentGateway($this->createStub(UrlGeneratorInterface::class));
        $request = new PaymentRequest(1999, '/orders/1');

        $this->assertNotSame(
            $gateway->initiate($request)->getProviderReference(),
            $gateway->initiate($request)->getProviderReference(),
        );
    }
}
