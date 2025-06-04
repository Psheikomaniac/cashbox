<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
final class MarkAllNotificationsReadController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly NotificationRepository $notificationRepository,
    ) {}

    #[IsGranted('ROLE_USER')]
    public function __invoke(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse([
                'error' => 'Authentication required',
                'message' => 'User must be authenticated',
            ], 401);
        }

        try {
            // Get all unread notifications for the current user
            $unreadNotifications = $this->notificationRepository->findUnreadForUser($user);
            
            if (empty($unreadNotifications)) {
                return new JsonResponse([
                    'message' => 'No unread notifications found',
                    'markedCount' => 0,
                ], 200);
            }

            $markedCount = 0;
            foreach ($unreadNotifications as $notification) {
                $notification->markAsRead();
                $markedCount++;
            }

            $this->entityManager->flush();

            return new JsonResponse([
                'message' => 'All notifications marked as read',
                'markedCount' => $markedCount,
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to mark notifications as read',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}