<?php

namespace App\MessageHandler;

use App\Message\PenaltyCreatedMessage;
use App\Repository\PenaltyRepository;
use App\Service\NotificationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PenaltyCreatedMessageHandler
{
    public function __construct(
        private readonly PenaltyRepository $penaltyRepository,
        private readonly NotificationService $notificationService,
        private readonly LoggerInterface $logger
    ) {}

    public function __invoke(PenaltyCreatedMessage $message): void
    {
        $this->logger->info('Processing PenaltyCreatedMessage', [
            'penaltyId' => $message->getPenaltyId(),
            'userId' => $message->getUserId(),
            'teamId' => $message->getTeamId()
        ]);

        $penalty = $this->penaltyRepository->find($message->getPenaltyId());

        if (!$penalty) {
            $this->logger->error('Penalty not found', [
                'penaltyId' => $message->getPenaltyId()
            ]);
            return;
        }

        // Send notification to the user
        $this->notificationService->sendPenaltyCreatedNotification(
            $message->getUserId(),
            $message->getPenaltyId(),
            $message->getReason(),
            $message->getAmount(),
            $message->getCurrency()
        );

        $this->logger->info('PenaltyCreatedMessage processed successfully', [
            'penaltyId' => $message->getPenaltyId()
        ]);
    }
}
