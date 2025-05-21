<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\NotificationPreferenceRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['notification_preference:read']],
    denormalizationContext: ['groups' => ['notification_preference:write']]
)]
#[ORM\Entity(repositoryClass: NotificationPreferenceRepository::class)]
class NotificationPreference
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['notification_preference:read'])]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['notification_preference:read', 'notification_preference:write'])]
    private User $user;

    #[ORM\Column(length: 255)]
    #[Groups(['notification_preference:read', 'notification_preference:write'])]
    private string $notificationType;

    #[ORM\Column]
    #[Groups(['notification_preference:read', 'notification_preference:write'])]
    private bool $emailEnabled = true;

    #[ORM\Column]
    #[Groups(['notification_preference:read', 'notification_preference:write'])]
    private bool $inAppEnabled = true;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column]
    #[Groups(['notification_preference:read'])]
    private \DateTimeImmutable $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column]
    #[Groups(['notification_preference:read'])]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getNotificationType(): string
    {
        return $this->notificationType;
    }

    public function setNotificationType(string $notificationType): self
    {
        $this->notificationType = $notificationType;

        return $this;
    }

    public function isEmailEnabled(): bool
    {
        return $this->emailEnabled;
    }

    public function setEmailEnabled(bool $emailEnabled): self
    {
        $this->emailEnabled = $emailEnabled;

        return $this;
    }

    public function isInAppEnabled(): bool
    {
        return $this->inAppEnabled;
    }

    public function setInAppEnabled(bool $inAppEnabled): self
    {
        $this->inAppEnabled = $inAppEnabled;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
