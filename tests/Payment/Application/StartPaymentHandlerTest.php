<?php

namespace App\Tests\Payment\Application;

use App\Payment\Application\PaymentRequest;
use App\Payment\Application\PaymentSession;
use App\Payment\Application\Port\PaymentGateway;
use App\Payment\Application\StartPaymentHandler;
use App\Payment\Domain\PaymentStatus;
use App\Payment\Infrastructure\InMemory\InMemoryPaymentRepository;
use PHPUnit\Framework\TestCase;

final class StartPaymentHandlerTest extends TestCase
{
    private InMemoryPaymentRepository $payments;
    private StartPaymentHandler $handler;

    protected function setUp(): void
    {
        $gateway = $this->createStub(PaymentGateway::class);
        $gateway->method('initiate')->willReturn(new PaymentSession('psp_123', 'https://psp.test/pay/psp_123'));

        $this->payments = new InMemoryPaymentRepository();
        $this->handler = new StartPaymentHandler($gateway, $this->payments);
    }

    public function testReturnsTheRedirectUrlFromTheGateway(): void
    {
        $this->assertSame('https://psp.test/pay/psp_123', ($this->handler)(42, 1999, '/orders/42'));
    }

    public function testSavesAPendingPaymentWithTheProviderReference(): void
    {
        ($this->handler)(42, 1999, '/orders/42');

        $payment = $this->payments->findByProviderReference('psp_123');
        $this->assertNotNull($payment);
        $this->assertSame(PaymentStatus::PENDING, $payment->getStatus());
        $this->assertSame(42, $payment->getOrderId());
        $this->assertSame(1999, $payment->getAmount());
    }

    public function testSendsTheAmountAndReturnUrlToTheGateway(): void
    {
        $gateway = $this->createMock(PaymentGateway::class);
        $gateway->expects($this->once())
            ->method('initiate')
            ->with($this->callback(static fn (PaymentRequest $request): bool => 1999 === $request->getAmount() && '/orders/42' === $request->getReturnUrl()))
            ->willReturn(new PaymentSession('psp_123', 'https://psp.test/pay/psp_123'));

        (new StartPaymentHandler($gateway, $this->payments))(42, 1999, '/orders/42');
    }
}
