<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\NotificationPreference;
use App\Entity\User;
use App\Enum\NotificationTypeEnum;
use App\Message\NotificationMessage;
use App\Repository\NotificationPreferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Email;

class NotificationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly NotificationPreferenceRepository $notificationPreferenceRepository,
        private readonly MailerInterface $mailer,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    /**
     * Create and send a notification to a user
     */
    public function notify(User $user, string|NotificationTypeEnum $type, string $title, string $message, ?array $data = null): Notification
    {
        // Convert string type to enum if needed
        $typeEnum = is_string($type) ? NotificationTypeEnum::from($type) : $type;

        // Create in-app notification using constructor with named arguments
        $notification = new Notification(
            user: $user,
            type: $typeEnum,
            title: $title,
            message: $message,
            data: $data
        );

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        // Check if email notifications are enabled for this type
        $preference = $this->notificationPreferenceRepository->findOneByUserAndType(
            $user->getId()->toString(),
            $typeEnum->value
        );

        // If no preference is found, default to sending email
        $sendEmail = $preference ? $preference->isEmailEnabled() : true;

        if ($sendEmail && $user->getEmail()) {
            $this->sendEmail($user, $title, $message, $data);
        }

        return $notification;
    }

    /**
     * Send a notification asynchronously
     */
    public function notifyAsync(User $user, string|NotificationTypeEnum $type, string $title, string $message, ?array $data = null): void
    {
        // Convert string type to enum if needed
        $typeValue = is_string($type) ? $type : $type->value;

        $this->messageBus->dispatch(new NotificationMessage(
            userId: $user->getId()->toString(),
            type: $typeValue,
            title: $title,
            message: $message,
            data: $data
        ));
    }

    /**
     * Send an email notification
     *
     * @param User $user The user to send the email to
     * @param string $title The email subject
     * @param string $message The email body
     * @param array|null $data Additional data for the email
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

        return $this->notify(
            user: $user,
            type: NotificationTypeEnum::PENALTY_CREATED,
            title: $title,
            message: $message,
            data: $data
        );
    }

    /**
     * Create and send a new penalty notification asynchronously
     */
    public function notifyNewPenaltyAsync(User $user, string $penaltyType, float $amount, string $reason): void
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

        $this->notifyAsync(
            user: $user,
            type: NotificationTypeEnum::PENALTY_CREATED,
            title: $title,
            message: $message,
            data: $data
        );
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

        return $this->notify(
            user: $user,
            type: NotificationTypeEnum::PAYMENT_REMINDER,
            title: $title,
            message: $message,
            data: $data
        );
    }

    /**
     * Create and send a payment reminder notification asynchronously
     */
    public function notifyPaymentReminderAsync(User $user, float $totalAmount, int $penaltyCount): void
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

        $this->notifyAsync(
            user: $user,
            type: NotificationTypeEnum::PAYMENT_REMINDER,
            title: $title,
            message: $message,
            data: $data
        );
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

        return $this->notify(
            user: $user,
            type: NotificationTypeEnum::BALANCE_UPDATE,
            title: $title,
            message: $message,
            data: $data
        );
    }

    /**
     * Create and send a balance update notification asynchronously
     */
    public function notifyBalanceUpdateAsync(User $user, float $newBalance): void
    {
        $title = 'Balance Update';
        $message = sprintf(
            'Your current balance is %.2f EUR.',
            $newBalance
        );
        $data = [
            'balance' => $newBalance
        ];

        $this->notifyAsync(
            user: $user,
            type: NotificationTypeEnum::BALANCE_UPDATE,
            title: $title,
            message: $message,
            data: $data
        );
    }
}
