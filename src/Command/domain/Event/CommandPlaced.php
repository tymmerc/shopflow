<?php

namespace Command\Domain\Event;

use DateTimeImmutable;

final class CommandPlaced
{
    /**
     * @param string[] $lignes
     */
    public function __construct(
        public readonly string $commandeId,
        public readonly array $lignes,
        public readonly DateTimeImmutable $passeeLe,
    ) {}
}