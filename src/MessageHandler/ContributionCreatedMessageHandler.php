<?php

namespace App\MessageHandler;

use App\Message\ContributionCreatedMessage;
use App\Repository\ContributionRepository;
use App\Service\NotificationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ContributionCreatedMessageHandler
{
    public function __construct(
        private readonly ContributionRepository $contributionRepository,
        private readonly NotificationService $notificationService,
        private readonly LoggerInterface $logger
    ) {}

    public function __invoke(ContributionCreatedMessage $message): void
    {
        $this->logger->info('Processing ContributionCreatedMessage', [
            'contributionId' => $message->getContributionId(),
            'teamUserId' => $message->getTeamUserId(),
            'typeId' => $message->getTypeId()
        ]);

        $contribution = $this->contributionRepository->find($message->getContributionId());

        if (!$contribution) {
            $this->logger->error('Contribution not found', [
                'contributionId' => $message->getContributionId()
            ]);
            return;
        }

        // Send notification to the user
        $this->notificationService->sendContributionCreatedNotification(
            $contribution->getTeamUser()->getUser()->getId()->toString(),
            $message->getContributionId(),
            $message->getDescription(),
            $message->getAmount(),
            $message->getCurrency(),
            $message->getDueDate()
        );

        $this->logger->info('ContributionCreatedMessage processed successfully', [
            'contributionId' => $message->getContributionId()
        ]);
    }
}
