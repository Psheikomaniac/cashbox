<?php

namespace App\Entity;

trait EventRecorderTrait
{
    private array $events = [];

    protected function record(object $event): void
    {
        $this->events[] = $event;
    }

    protected function recordEvent(object $event): void
    {
        $this->events[] = $event;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function clearEvents(): void
    {
        $this->events = [];
    }

    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }
}