<?php

namespace App\EventSubscriber;

use App\Event\PenaltyCreatedEvent;
use App\Event\PenaltyPaidEvent;
use App\Message\PenaltyCreatedMessage;
use App\Message\PenaltyPaidMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PenaltyEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PenaltyCreatedEvent::class => 'onPenaltyCreated',
            PenaltyPaidEvent::class => 'onPenaltyPaid',
        ];
    }

    public function onPenaltyCreated(PenaltyCreatedEvent $event): void
    {
        $this->messageBus->dispatch(new PenaltyCreatedMessage(
            $event->penaltyId->toString(),
            $event->userId->toString(),
            $event->teamId->toString(),
            $event->reason,
            $event->amount->getAmount(),
            $event->amount->getCurrency()->value
        ));
    }

    public function onPenaltyPaid(PenaltyPaidEvent $event): void
    {
        $this->messageBus->dispatch(new PenaltyPaidMessage(
            $event->penaltyId->toString(),
            $event->paidAt->format('c')
        ));
    }
}
