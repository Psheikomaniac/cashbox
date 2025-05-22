<?php

namespace App\Controller;

use App\DTO\NotificationOutputDTO;
use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/notifications')]
class NotificationController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private NotificationRepository $notificationRepository;
    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        NotificationRepository $notificationRepository,
        UserRepository $userRepository
    ) {
        $this->entityManager = $entityManager;
        $this->notificationRepository = $notificationRepository;
        $this->userRepository = $userRepository;
    }

    #[Route('', methods: ['GET'])]
    public function getNotifications(): JsonResponse
    {
        // In a real implementation, we would get the current user from the security context
        // For now, we'll just get all notifications
        $notifications = $this->notificationRepository->findAll();

        $notificationDTOs = [];
        foreach ($notifications as $notification) {
            $notificationDTOs[] = NotificationOutputDTO::createFromEntity($notification);
        }

        return $this->json($notificationDTOs);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getNotification(string $id): JsonResponse
    {
        $notification = $this->notificationRepository->find(Uuid::fromString($id));

        if (!$notification) {
            return $this->json(['error' => 'Notification not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(NotificationOutputDTO::createFromEntity($notification));
    }

    #[Route('/{id}/read', methods: ['POST'])]
    public function markAsRead(string $id): JsonResponse
    {
        $notification = $this->notificationRepository->find(Uuid::fromString($id));

        if (!$notification) {
            return $this->json(['error' => 'Notification not found'], Response::HTTP_NOT_FOUND);
        }

        $notification->setRead(true);
        $notification->setReadAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $this->json(NotificationOutputDTO::createFromEntity($notification));
    }

    #[Route('/read-all', methods: ['POST'])]
    public function markAllAsRead(Request $request): JsonResponse
    {
        // In a real implementation, we would get the current user from the security context
        // For now, we'll use a user ID from the request
        $userId = $request->request->get('userId');

        if (!$userId) {
            return $this->json(['error' => 'User ID is required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->find(Uuid::fromString($userId));

        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $notifications = $this->notificationRepository->findUnreadByUser($userId);

        foreach ($notifications as $notification) {
            $notification->setRead(true);
            $notification->setReadAt(new \DateTimeImmutable());
        }

        $this->entityManager->flush();

        return $this->json(['success' => true, 'count' => count($notifications)]);
    }
}
