<?php

namespace App\Service;

use Symfony\Component\Messenger\MessageBusInterface;

class MessageBusService
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    public function getMessageBus(): MessageBusInterface
    {
        return $this->messageBus;
    }

    public function dispatch(object $message): void
    {
        $this->messageBus->dispatch($message);
    }
}
