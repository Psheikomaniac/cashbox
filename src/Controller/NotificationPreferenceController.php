<?php

namespace App\Controller;

use App\Entity\NotificationPreference;
use App\Entity\User;
use App\Repository\NotificationPreferenceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/notification-preferences')]
class NotificationPreferenceController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private NotificationPreferenceRepository $notificationPreferenceRepository;
    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        NotificationPreferenceRepository $notificationPreferenceRepository,
        UserRepository $userRepository
    ) {
        $this->entityManager = $entityManager;
        $this->notificationPreferenceRepository = $notificationPreferenceRepository;
        $this->userRepository = $userRepository;
    }

    #[Route('', methods: ['GET'])]
    public function getPreferences(Request $request): JsonResponse
    {
        // In a real implementation, we would get the current user from the security context
        // For now, we'll use a user ID from the request
        $userId = $request->query->get('userId');

        if (!$userId) {
            return $this->json(['error' => 'User ID is required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->find(Uuid::fromString($userId));

        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $preferences = $this->notificationPreferenceRepository->findByUser($userId);

        $result = [];
        foreach ($preferences as $preference) {
            $result[] = [
                'id' => $preference->getId()->toString(),
                'notificationType' => $preference->getNotificationType(),
                'emailEnabled' => $preference->isEmailEnabled(),
                'inAppEnabled' => $preference->isInAppEnabled()
            ];
        }

        return $this->json($result);
    }

    #[Route('', methods: ['PUT'])]
    public function updatePreferences(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['userId']) || !isset($data['preferences']) || !is_array($data['preferences'])) {
            return $this->json(['error' => 'Invalid request data'], Response::HTTP_BAD_REQUEST);
        }

        $userId = $data['userId'];
        $user = $this->userRepository->find(Uuid::fromString($userId));

        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $updatedPreferences = [];

        foreach ($data['preferences'] as $preferenceData) {
            if (!isset($preferenceData['notificationType'])) {
                continue;
            }

            $notificationType = $preferenceData['notificationType'];
            $emailEnabled = $preferenceData['emailEnabled'] ?? true;
            $inAppEnabled = $preferenceData['inAppEnabled'] ?? true;

            $preference = $this->notificationPreferenceRepository->findOneByUserAndType($userId, $notificationType);

            if (!$preference) {
                $preference = new NotificationPreference();
                $preference->setUser($user);
                $preference->setNotificationType($notificationType);
                $this->entityManager->persist($preference);
            }

            $preference->setEmailEnabled($emailEnabled);
            $preference->setInAppEnabled($inAppEnabled);

            $updatedPreferences[] = [
                'id' => $preference->getId()->toString(),
                'notificationType' => $preference->getNotificationType(),
                'emailEnabled' => $preference->isEmailEnabled(),
                'inAppEnabled' => $preference->isInAppEnabled()
            ];
        }

        $this->entityManager->flush();

        return $this->json($updatedPreferences);
    }

    #[Route('/types', methods: ['GET'])]
    public function getNotificationTypes(): JsonResponse
    {
        // This is a placeholder for the actual implementation
        // In a real application, this would return the available notification types
        $types = [
            [
                'type' => 'new_penalty',
                'description' => 'Notifications for new penalties'
            ],
            [
                'type' => 'payment_reminder',
                'description' => 'Reminders for unpaid penalties'
            ],
            [
                'type' => 'balance_update',
                'description' => 'Updates about your balance'
            ]
        ];

        return $this->json($types);
    }
}
