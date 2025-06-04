<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
final class MarkNotificationReadController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[IsGranted('ROLE_USER')]
    public function __invoke(Notification $notification): JsonResponse
    {
        // Ensure user can only mark their own notifications as read
        if ($notification->getUser() !== $this->getUser()) {
            return new JsonResponse([
                'error' => 'Access denied',
                'message' => 'You can only mark your own notifications as read',
            ], 403);
        }

        // Check if already read
        if ($notification->isRead()) {
            return new JsonResponse([
                'message' => 'Notification is already marked as read',
                'notificationId' => $notification->getId()->toString(),
                'readAt' => $notification->getReadAt()?->format('Y-m-d H:i:s'),
            ], 200);
        }

        try {
            $notification->markAsRead();
            $this->entityManager->flush();

            return new JsonResponse([
                'message' => 'Notification marked as read',
                'notificationId' => $notification->getId()->toString(),
                'readAt' => $notification->getReadAt()?->format('Y-m-d H:i:s'),
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to mark notification as read',
                'message' => $e->getMessage(),
                'notificationId' => $notification->getId()->toString(),
            ], 500);
        }
    }
}