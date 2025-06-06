<?php

namespace App\MessageHandler;

use App\Message\PenaltyPaidMessage;
use App\Repository\PenaltyRepository;
use App\Service\NotificationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PenaltyPaidMessageHandler
{
    public function __construct(
        private readonly PenaltyRepository $penaltyRepository,
        private readonly NotificationService $notificationService,
        private readonly LoggerInterface $logger
    ) {}

    public function __invoke(PenaltyPaidMessage $message): void
    {
        $this->logger->info('Processing PenaltyPaidMessage', [
            'penaltyId' => $message->getPenaltyId(),
            'paidAt' => $message->getPaidAt()
        ]);

        $penalty = $this->penaltyRepository->find($message->getPenaltyId());

        if (!$penalty) {
            $this->logger->error('Penalty not found', [
                'penaltyId' => $message->getPenaltyId()
            ]);
            return;
        }

        // Send notification to the user
        $this->notificationService->sendPenaltyPaidNotification(
            $penalty->getTeamUser()->getUser()->getId()->toString(),
            $message->getPenaltyId(),
            $message->getPaidAt()
        );

        $this->logger->info('PenaltyPaidMessage processed successfully', [
            'penaltyId' => $message->getPenaltyId()
        ]);
    }
}
