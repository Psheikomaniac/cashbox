<?php

namespace App\Service;

use Symfony\Component\Messenger\MessageBusInterface;

class MessageBusService
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
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
