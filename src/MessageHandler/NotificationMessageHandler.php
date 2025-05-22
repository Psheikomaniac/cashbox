<?php

namespace App\MessageHandler;

use App\Message\NotificationMessage;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class NotificationMessageHandler
{
    private UserRepository $userRepository;
    private NotificationService $notificationService;

    public function __construct(
        UserRepository $userRepository,
        NotificationService $notificationService
    ) {
        $this->userRepository = $userRepository;
        $this->notificationService = $notificationService;
    }

    public function __invoke(NotificationMessage $message): void
    {
        $userId = $message->getUserId();
        $user = $this->userRepository->find($userId);

        if (!$user) {
            throw new \Exception(sprintf('User with ID "%s" not found', $userId));
        }

        $this->notificationService->notify(
            $user,
            $message->getType(),
            $message->getTitle(),
            $message->getMessage(),
            $message->getData()
        );
    }
}
