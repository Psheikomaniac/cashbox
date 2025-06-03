<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Enum\NotificationTypeEnum;
use App\Event\NotificationCreatedEvent;
use App\Event\NotificationReadEvent;
use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(
            paginationEnabled: true,
            paginationItemsPerPage: 50,
        ),
        new Post(
            denormalizationContext: ['groups' => ['notification:create']],
            validationContext: ['groups' => ['Default', 'notification:create']],
        ),
        new Put(
            denormalizationContext: ['groups' => ['notification:update']],
        ),
        new Delete(),
        new Post(
            uriTemplate: '/notifications/{id}/read',
            controller: 'App\Controller\MarkNotificationReadController',
            openapiContext: [
                'summary' => 'Mark notification as read',
                'description' => 'Marks a notification as read and records the timestamp',
            ]
        ),
        new Post(
            uriTemplate: '/notifications/read-all',
            controller: 'App\Controller\MarkAllNotificationsReadController',
            openapiContext: [
                'summary' => 'Mark all notifications as read',
                'description' => 'Marks all user notifications as read',
            ]
        ),
    ],
    normalizationContext: ['groups' => ['notification:read']],
    security: "is_granted('ROLE_USER')"
)]
#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notifications')]
#[ORM\Index(columns: ['user_id', 'read'], name: 'idx_user_read')]
#[ORM\Index(columns: ['created_at'], name: 'idx_created_at')]
class Notification implements AggregateRootInterface
{
    use EventRecorderTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['notification:read'])]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['notification:read', 'notification:create'])]
    private User $user;

    #[ORM\Column(type: 'string', length: 255, enumType: NotificationTypeEnum::class)]
    #[Groups(['notification:read', 'notification:create'])]
    private NotificationTypeEnum $type;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['notification:read', 'notification:create'])]
    private string $title;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Groups(['notification:read', 'notification:create'])]
    private string $message;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['notification:read', 'notification:create'])]
    private ?array $data = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['notification:read'])]
    private bool $read = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['notification:read'])]
    private ?\DateTimeImmutable $readAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['notification:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        User $user,
        NotificationTypeEnum $type,
        string $title,
        string $message,
        ?array $data = null
    ) {
        $this->id = Uuid::uuid7();
        $this->user = $user;
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
        $this->createdAt = new \DateTimeImmutable();
        
        $this->recordEvent(new NotificationCreatedEvent($this));
    }

    public function markAsRead(): void
    {
        if ($this->read) {
            return;
        }
        
        $this->read = true;
        $this->readAt = new \DateTimeImmutable();
        
        $this->recordEvent(new NotificationReadEvent($this));
    }

    public function isUnread(): bool
    {
        return !$this->read;
    }

    public function isExpired(): bool
    {
        $retentionDays = $this->type->getRetentionDays();
        $expiryDate = $this->createdAt->modify("+{$retentionDays} days");
        
        return new \DateTimeImmutable() > $expiryDate;
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

    public function getType(): NotificationTypeEnum
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function isRead(): bool
    {
        return $this->read;
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
