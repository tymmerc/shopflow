<?php

namespace App\Shared\Infrastructure;

use App\Shared\Application\EventPublisher;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Publie via l'EventDispatcher de Symfony : l'événement est identifié par sa classe,
 * donc un listener s'abonne avec #[AsEventListener(event: PaymentSucceeded::class)].
 * Les listeners sont exécutés tout de suite, dans la même requête.
 */
final class SymfonyEventPublisher implements EventPublisher
{
    public function __construct(private readonly EventDispatcherInterface $dispatcher)
    {
    }

    public function publish(object $event): void
    {
        $this->dispatcher->dispatch($event);
    }
}
