<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\NotificationPreference;
use App\Entity\User;
use App\Repository\NotificationPreferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NotificationService
{
    private EntityManagerInterface $entityManager;
    private NotificationPreferenceRepository $notificationPreferenceRepository;
    private MailerInterface $mailer;

    public function __construct(
        EntityManagerInterface $entityManager,
        NotificationPreferenceRepository $notificationPreferenceRepository,
        MailerInterface $mailer
    ) {
        $this->entityManager = $entityManager;
        $this->notificationPreferenceRepository = $notificationPreferenceRepository;
        $this->mailer = $mailer;
    }

    /**
     * Create and send a notification to a user
     */
    public function notify(User $user, string $type, string $title, string $message, ?array $data = null): Notification
    {
        // Create in-app notification
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType($type);
        $notification->setTitle($title);
        $notification->setMessage($message);
        $notification->setData($data);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        // Check if email notifications are enabled for this type
        $preference = $this->notificationPreferenceRepository->findOneByUserAndType(
            $user->getId()->toString(),
            $type
        );

        // If no preference is found, default to sending email
        $sendEmail = $preference ? $preference->isEmailEnabled() : true;

        if ($sendEmail && $user->getEmail()) {
            $this->sendEmail($user, $title, $message, $data);
        }

        return $notification;
    }

    /**
     * Send an email notification
     */
    private function sendEmail(User $user, string $title, string $message, ?array $data = null): void
    {
        $email = (new Email())
            ->from('notifications@cashbox.example.com')
            ->to($user->getEmail())
            ->subject('Cashbox: ' . $title)
            ->text($message);

        $this->mailer->send($email);
    }

    /**
     * Create and send a new penalty notification
     */
    public function notifyNewPenalty(User $user, string $penaltyType, float $amount, string $reason): Notification
    {
        $title = 'New Penalty: ' . $penaltyType;
        $message = sprintf(
            'You have received a new penalty of %.2f EUR for %s: %s',
            $amount,
            $penaltyType,
            $reason
        );
        $data = [
            'penaltyType' => $penaltyType,
            'amount' => $amount,
            'reason' => $reason
        ];

        return $this->notify($user, 'new_penalty', $title, $message, $data);
    }

    /**
     * Create and send a payment reminder notification
     */
    public function notifyPaymentReminder(User $user, float $totalAmount, int $penaltyCount): Notification
    {
        $title = 'Payment Reminder';
        $message = sprintf(
            'You have %d unpaid penalties totaling %.2f EUR. Please make a payment soon.',
            $penaltyCount,
            $totalAmount
        );
        $data = [
            'totalAmount' => $totalAmount,
            'penaltyCount' => $penaltyCount
        ];

        return $this->notify($user, 'payment_reminder', $title, $message, $data);
    }

    /**
     * Create and send a balance update notification
     */
    public function notifyBalanceUpdate(User $user, float $newBalance): Notification
    {
        $title = 'Balance Update';
        $message = sprintf(
            'Your current balance is %.2f EUR.',
            $newBalance
        );
        $data = [
            'balance' => $newBalance
        ];

        return $this->notify($user, 'balance_update', $title, $message, $data);
    }
}
