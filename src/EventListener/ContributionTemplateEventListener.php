<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Event\ContributionTemplateAppliedEvent;
use App\Event\ContributionTemplateCreatedEvent;
use App\Message\NotificationMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class ContributionTemplateEventListener
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {}

    #[AsEventListener]
    public function onContributionTemplateCreated(ContributionTemplateCreatedEvent $event): void
    {
        $template = $event->contributionTemplate;
        
        // Notify team administrators about new template
        $message = sprintf(
            'New contribution template "%s" has been created for team "%s". Amount: %s',
            $template->getName(),
            $template->getTeam()->getName(),
            $template->getAmount()->format()
        );

        // Note: In a real implementation, you'd need to find team administrators
        // For now, we'll create a generic notification
        $notificationMessage = new NotificationMessage(
            userId: '', // Would need to be populated with team admin IDs
            type: 'template_created',
            title: 'Contribution Template Created',
            message: $message,
            metadata: [
                'template_id' => $template->getId()->toString(),
                'team_id' => $template->getTeam()->getId()->toString(),
                'amount' => $template->getAmount()->getCents(),
                'currency' => $template->getAmount()->getCurrency()->value,
            ]
        );

        // $this->messageBus->dispatch($notificationMessage);
    }

    #[AsEventListener]
    public function onContributionTemplateApplied(ContributionTemplateAppliedEvent $event): void
    {
        $template = $event->contributionTemplate;
        $appliedCount = $event->appliedCount;
        
        $message = sprintf(
            'Contribution template "%s" has been applied to %d team members. Total contributions created.',
            $template->getName(),
            $appliedCount
        );

        // Create audit log or notification for template application
        $notificationMessage = new NotificationMessage(
            userId: '', // Would need to be populated with the user who applied the template
            type: 'template_applied',
            title: 'Template Applied Successfully',
            message: $message,
            metadata: [
                'template_id' => $template->getId()->toString(),
                'applied_count' => $appliedCount,
                'team_id' => $template->getTeam()->getId()->toString(),
            ]
        );

        // $this->messageBus->dispatch($notificationMessage);
    }
}