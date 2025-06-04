<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\NotificationTypeEnum;
use App\Event\NotificationPreferenceUpdatedEvent;
use App\Repository\NotificationPreferenceRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NotificationPreferenceRepository::class)]
#[ORM\Table(name: 'notification_preferences')]
#[ORM\UniqueConstraint(columns: ['user_id', 'notification_type'])]
class NotificationPreference implements AggregateRootInterface
{
    use EventRecorderTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['notification_preference:read'])]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['notification_preference:read', 'notification_preference:create'])]
    private User $user;

    #[ORM\Column(type: 'string', length: 255, enumType: NotificationTypeEnum::class)]
    #[Groups(['notification_preference:read', 'notification_preference:create'])]
    private NotificationTypeEnum $notificationType;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['notification_preference:read', 'notification_preference:create', 'notification_preference:update'])]
    private bool $emailEnabled = true;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['notification_preference:read', 'notification_preference:create', 'notification_preference:update'])]
    private bool $inAppEnabled = true;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['notification_preference:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['notification_preference:read'])]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        User $user,
        NotificationTypeEnum $notificationType,
        bool $emailEnabled = true,
        bool $inAppEnabled = true
    ) {
        $this->id = Uuid::uuid7();
        $this->user = $user;
        $this->notificationType = $notificationType;
        $this->emailEnabled = $emailEnabled;
        $this->inAppEnabled = $inAppEnabled;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updatePreferences(bool $emailEnabled, bool $inAppEnabled): void
    {
        $changed = $this->emailEnabled !== $emailEnabled || $this->inAppEnabled !== $inAppEnabled;
        
        if (!$changed) {
            return;
        }
        
        $this->emailEnabled = $emailEnabled;
        $this->inAppEnabled = $inAppEnabled;
        $this->updatedAt = new \DateTimeImmutable();
        
        $this->recordEvent(new NotificationPreferenceUpdatedEvent($this));
    }

    public function isNotificationAllowed(string $channel): bool
    {
        return match ($channel) {
            'email' => $this->emailEnabled,
            'in_app' => $this->inAppEnabled,
            default => false,
        };
    }

    // Property accessors
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getNotificationType(): NotificationTypeEnum
    {
        return $this->notificationType;
    }

    public function isEmailEnabled(): bool
    {
        return $this->emailEnabled;
    }

    public function isInAppEnabled(): bool
    {
        return $this->inAppEnabled;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
