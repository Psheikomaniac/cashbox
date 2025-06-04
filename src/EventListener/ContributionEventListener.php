<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Event\ContributionCreatedEvent;
use App\Event\ContributionPaidEvent;
use App\Message\NotificationMessage;
use App\Service\NotificationService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class ContributionEventListener
{
    public function __construct(
        private NotificationService $notificationService,
        private MessageBusInterface $messageBus,
    ) {}

    #[AsEventListener]
    public function onContributionCreated(ContributionCreatedEvent $event): void
    {
        $contribution = $event->contribution;
        
        // Create notification for the team user
        $message = sprintf(
            'New contribution "%s" has been created. Due date: %s. Amount: %s',
            $contribution->getDescription(),
            $contribution->getDueDate()->format('Y-m-d'),
            $contribution->getAmount()->format()
        );

        $notificationMessage = new NotificationMessage(
            userId: $contribution->getTeamUser()->getUser()->getId()->toString(),
            type: 'contribution_created',
            title: 'New Contribution Created',
            message: $message,
            metadata: [
                'contribution_id' => $contribution->getId()->toString(),
                'due_date' => $contribution->getDueDate()->format('Y-m-d'),
                'amount' => $contribution->getAmount()->getCents(),
                'currency' => $contribution->getAmount()->getCurrency()->value,
            ]
        );

        $this->messageBus->dispatch($notificationMessage);
    }

    #[AsEventListener]
    public function onContributionPaid(ContributionPaidEvent $event): void
    {
        $contribution = $event->contribution;
        
        // Create notification for successful payment
        $message = sprintf(
            'Contribution "%s" has been marked as paid. Amount: %s',
            $contribution->getDescription(),
            $contribution->getAmount()->format()
        );

        $notificationMessage = new NotificationMessage(
            userId: $contribution->getTeamUser()->getUser()->getId()->toString(),
            type: 'contribution_paid',
            title: 'Contribution Paid',
            message: $message,
            metadata: [
                'contribution_id' => $contribution->getId()->toString(),
                'paid_at' => $contribution->getPaidAt()?->format('Y-m-d H:i:s'),
                'amount' => $contribution->getAmount()->getCents(),
                'currency' => $contribution->getAmount()->getCurrency()->value,
            ]
        );

        $this->messageBus->dispatch($notificationMessage);
    }
}