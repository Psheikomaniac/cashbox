<?php

namespace App\Domain\Model\Shared;

use App\Domain\Event\DomainEvent;

abstract class AggregateRoot
{
    private array $domainEvents = [];

    /**
     * Records a domain event to be dispatched when the aggregate is saved
     */
    protected function recordEvent(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * Returns all recorded domain events and clears the internal list
     *
     * @return DomainEvent[]
     */
    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    /**
     * Returns all recorded domain events without clearing the internal list
     *
     * @return DomainEvent[]
     */
    public function getRecordedEvents(): array
    {
        return $this->domainEvents;
    }
}
