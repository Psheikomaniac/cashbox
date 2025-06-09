<?php

namespace App\MessageHandler;

use App\Message\ContributionPaidMessage;
use App\Repository\ContributionRepository;
use App\Service\NotificationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ContributionPaidMessageHandler
{
    public function __construct(
        private readonly ContributionRepository $contributionRepository,
        private readonly NotificationService $notificationService,
        private readonly LoggerInterface $logger
    ) {}

    public function __invoke(ContributionPaidMessage $message): void
    {
        $this->logger->info('Processing ContributionPaidMessage', [
            'contributionId' => $message->getContributionId(),
            'paidAt' => $message->getPaidAt()
        ]);

        $contribution = $this->contributionRepository->find($message->getContributionId());

        if (!$contribution) {
            $this->logger->error('Contribution not found', [
                'contributionId' => $message->getContributionId()
            ]);
            return;
        }

        // Send notification to the user
        $this->notificationService->sendContributionPaidNotification(
            $contribution->getTeamUser()->getUser()->getId()->toString(),
            $message->getContributionId(),
            $contribution->getDescription(),
            $contribution->getAmount()->getCents(),
            $contribution->getAmount()->getCurrency()->value,
            $message->getPaidAt()
        );

        $this->logger->info('ContributionPaidMessage processed successfully', [
            'contributionId' => $message->getContributionId()
        ]);
    }
}
