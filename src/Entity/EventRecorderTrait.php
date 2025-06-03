<?php

namespace App\Entity;

trait EventRecorderTrait
{
    private array $events = [];

    protected function record(object $event): void
    {
        $this->events[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }
}