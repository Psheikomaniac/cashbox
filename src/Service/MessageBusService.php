<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\AsPublic;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsPublic]
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

    /**
     * Dispatches a message to the message bus
     *
     * @param object $message The message to dispatch
     * @return \Symfony\Component\Messenger\Envelope The envelope containing the dispatched message
     */
    public function dispatch(object $message): \Symfony\Component\Messenger\Envelope
    {
        return $this->messageBus->dispatch(message: $message);
    }
}
