<?php

namespace App\Tests\Order\Domain;

use App\Order\Domain\OrderLine;
use PHPUnit\Framework\TestCase;

final class OrderLineTest extends TestCase
{
    public function testSubtotalIsUnitPriceTimesQuantity(): void
    {
        $line = new OrderLine(7, 1999, 3);

        $this->assertSame(5997, $line->subtotalCents());
    }

    public function testQuantityMustBeAtLeastOne(): void
    {
        $this->expectException(\DomainException::class);

        new OrderLine(7, 1999, 0);
    }

    public function testPriceCannotBeNegative(): void
    {
        $this->expectException(\DomainException::class);

        new OrderLine(7, -1, 1);
    }
}
