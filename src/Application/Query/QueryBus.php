<?php

namespace App\Application\Query;

use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class QueryBus
{
    public function __construct(
        private readonly MessageBusInterface $queryBus
    ) {}

    /**
     * Dispatches a query to the query bus and returns the result
     *
     * @template T
     * @param object $query The query to dispatch
     * @return T The result of the query handler
     */
    public function dispatch(object $query): mixed
    {
        $envelope = $this->queryBus->dispatch($query);
        $handledStamp = $envelope->last(HandledStamp::class);

        return $handledStamp?->getResult();
    }
}
