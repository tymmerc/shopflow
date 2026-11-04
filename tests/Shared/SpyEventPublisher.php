<?php

namespace App\Tests\Shared;

use App\Shared\Application\EventPublisher;

/**
 * Garde les événements publiés pour que les tests puissent les vérifier.
 */
final class SpyEventPublisher implements EventPublisher
{
    /** @var list<object> */
    public array $events = [];

    public function publish(object $event): void
    {
        $this->events[] = $event;
    }
}
