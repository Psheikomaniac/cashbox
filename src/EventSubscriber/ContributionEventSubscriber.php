<?php

namespace App\EventSubscriber;

use App\Event\ContributionCreatedEvent;
use App\Event\ContributionPaidEvent;
use App\Message\ContributionCreatedMessage;
use App\Message\ContributionPaidMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ContributionEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ContributionCreatedEvent::class => 'onContributionCreated',
            ContributionPaidEvent::class => 'onContributionPaid',
        ];
    }

    public function onContributionCreated(ContributionCreatedEvent $event): void
    {
        $contribution = $event->contribution;

        $this->messageBus->dispatch(new ContributionCreatedMessage(
            $contribution->getId()->toString(),
            $contribution->getTeamUser()->getId()->toString(),
            $contribution->getType()->getId()->toString(),
            $contribution->getDescription(),
            $contribution->getAmount()->getCents(),
            $contribution->getAmount()->getCurrency()->value,
            $contribution->getDueDate()->format('Y-m-d')
        ));
    }

    public function onContributionPaid(ContributionPaidEvent $event): void
    {
        $contribution = $event->contribution;

        $this->messageBus->dispatch(new ContributionPaidMessage(
            $contribution->getId()->toString(),
            $contribution->getPaidAt()->format('c')
        ));
    }
}
