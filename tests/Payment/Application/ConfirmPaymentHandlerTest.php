<?php

namespace App\Tests\Payment\Application;

use App\Payment\Application\ConfirmPaymentHandler;
use App\Payment\Application\Exception\PaymentNotFoundException;
use App\Payment\Domain\Event\PaymentFailed;
use App\Payment\Domain\Event\PaymentSucceeded;
use App\Payment\Domain\Payment;
use App\Payment\Domain\PaymentStatus;
use App\Payment\Infrastructure\InMemory\InMemoryPaymentRepository;
use App\Tests\Shared\SpyEventPublisher;
use PHPUnit\Framework\TestCase;

final class ConfirmPaymentHandlerTest extends TestCase
{
    private InMemoryPaymentRepository $payments;
    private SpyEventPublisher $events;
    private ConfirmPaymentHandler $handler;

    protected function setUp(): void
    {
        $this->payments = new InMemoryPaymentRepository();
        $this->events = new SpyEventPublisher();
        $this->handler = new ConfirmPaymentHandler($this->payments, $this->events);

        $payment = Payment::start(42, 1999);
        $payment->attachProviderReference('fake_123');
        $this->payments->save($payment);
    }

    public function testSuccessMarksThePaymentAndPublishesPaymentSucceeded(): void
    {
        ($this->handler)('fake_123', true);

        $this->assertSame(PaymentStatus::SUCCEEDED, $this->payments->findByProviderReference('fake_123')?->getStatus());
        $this->assertCount(1, $this->events->events);
        $this->assertInstanceOf(PaymentSucceeded::class, $this->events->events[0]);
        $this->assertSame(42, $this->events->events[0]->getOrderId());
    }

    public function testFailureMarksThePaymentAndPublishesPaymentFailed(): void
    {
        ($this->handler)('fake_123', false);

        $this->assertSame(PaymentStatus::FAILED, $this->payments->findByProviderReference('fake_123')?->getStatus());
        $this->assertCount(1, $this->events->events);
        $this->assertInstanceOf(PaymentFailed::class, $this->events->events[0]);
        $this->assertSame(42, $this->events->events[0]->getOrderId());
    }

    public function testSecondConfirmationIsIgnored(): void
    {
        ($this->handler)('fake_123', true);
        ($this->handler)('fake_123', false);

        $this->assertSame(PaymentStatus::SUCCEEDED, $this->payments->findByProviderReference('fake_123')?->getStatus());
        $this->assertCount(1, $this->events->events);
    }

    public function testUnknownReferenceThrows(): void
    {
        $this->expectException(PaymentNotFoundException::class);

        ($this->handler)('fake_unknown', true);
    }
}
