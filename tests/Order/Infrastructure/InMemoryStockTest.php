<?php

namespace App\Tests\Order\Infrastructure;

use App\Order\Domain\Exception\InsufficientStockException;
use App\Order\Infrastructure\InMemory\InMemoryStock;
use PHPUnit\Framework\TestCase;

final class InMemoryStockTest extends TestCase
{
    public function testDecrementsEveryArticle(): void
    {
        $stock = new InMemoryStock([7 => 10, 8 => 3]);
        $stock->decrement([7 => 2, 8 => 3]);

        $this->assertSame(8, $stock->availableQuantity(7));
        $this->assertSame(0, $stock->availableQuantity(8));
    }

    public function testNothingIsDecrementedWhenOneArticleIsMissing(): void
    {
        $stock = new InMemoryStock([7 => 10, 8 => 1]);

        try {
            $stock->decrement([7 => 2, 8 => 3]);
            $this->fail('InsufficientStockException attendue');
        } catch (InsufficientStockException $e) {
            $this->assertSame(8, $e->getCatalogId());
        }

        $this->assertSame(10, $stock->availableQuantity(7));
        $this->assertSame(1, $stock->availableQuantity(8));
    }
}
