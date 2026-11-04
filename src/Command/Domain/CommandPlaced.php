<?php

namespace App\Command\Domain\Event;

use DateTimeImmutable;

final class CommandPlaced
{
    /**
     * @param string[] $lines
     */
    public function __construct(
        public readonly string $commandId,
        public readonly array $lines,
        public readonly DateTimeImmutable $placedAt,
    ) {}
}