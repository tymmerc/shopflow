<?php

namespace App\Shared\Application;

interface EventPublisher
{
    public function publish(object $event): void;
}
