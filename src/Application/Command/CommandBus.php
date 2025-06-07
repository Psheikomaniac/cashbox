<?php

namespace App\Application\Command;

use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class CommandBus
{
    public function __construct(
        private readonly MessageBusInterface $commandBus
    ) {}

    /**
     * Dispatches a command to the command bus and returns the result
     *
     * @template T
     * @param object $command The command to dispatch
     * @return T The result of the command handler
     */
    public function dispatch(object $command): mixed
    {
        $envelope = $this->commandBus->dispatch($command);
        $handledStamp = $envelope->last(HandledStamp::class);

        return $handledStamp?->getResult();
    }
}
