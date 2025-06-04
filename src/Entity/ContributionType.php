<?php

namespace App\Entity;

use App\Enum\RecurrencePatternEnum;
use App\Event\ContributionTypeCreatedEvent;
use App\Event\ContributionTypeUpdatedEvent;
use App\Repository\ContributionTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContributionTypeRepository::class)]
#[ORM\Table(name: 'contribution_types')]
#[ORM\Index(columns: ['active'], name: 'idx_active')]
class ContributionType implements AggregateRootInterface
{
    use EventRecorderTrait;
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['contribution_type:read'])]
    private UuidInterface $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['contribution_type:read', 'contribution_type:write'])]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(max: 1000)]
    #[Groups(['contribution_type:read', 'contribution_type:write'])]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['contribution_type:read', 'contribution_type:write'])]
    private bool $recurring = false;

    #[ORM\Column(type: 'string', length: 255, enumType: RecurrencePatternEnum::class, nullable: true)]
    #[Groups(['contribution_type:read', 'contribution_type:write'])]
    private ?RecurrencePatternEnum $recurrencePattern = null;

    #[ORM\Column]
    #[Groups(['contribution_type:read'])]
    private bool $active = true;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column]
    #[Groups(['contribution_type:read'])]
    private \DateTimeImmutable $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column]
    #[Groups(['contribution_type:read'])]
    private \DateTimeImmutable $updatedAt;

    private array $domainEvents = [];

    public function __construct(
        string $name,
        ?string $description = null,
        bool $recurring = false,
        ?RecurrencePatternEnum $recurrencePattern = null
    ) {
        $this->id = Uuid::uuid7();
        $this->name = $name;
        $this->description = $description;
        $this->recurring = $recurring;
        $this->recurrencePattern = $recurrencePattern;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        
        $this->validateRecurrence();
        
        $this->recordEvent(new ContributionTypeCreatedEvent($this));
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isRecurring(): bool
    {
        return $this->recurring;
    }

    public function update(
        string $name,
        ?string $description = null,
        bool $recurring = false,
        ?RecurrencePatternEnum $recurrencePattern = null
    ): void {
        $this->name = $name;
        $this->description = $description;
        $this->recurring = $recurring;
        $this->recurrencePattern = $recurrencePattern;
        $this->updatedAt = new \DateTimeImmutable();
        
        $this->validateRecurrence();
        
        $this->recordEvent(new ContributionTypeUpdatedEvent($this));
    }

    public function activate(): void
    {
        $this->active = true;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->active = false;
        $this->updatedAt = new \DateTimeImmutable();
    }

    private function validateRecurrence(): void
    {
        if ($this->recurring && $this->recurrencePattern === null) {
            throw new \InvalidArgumentException('Recurrence pattern is required for recurring contribution types');
        }
        
        if (!$this->recurring && $this->recurrencePattern !== null) {
            throw new \InvalidArgumentException('Recurrence pattern should be null for non-recurring contribution types');
        }
    }

    public function calculateNextDueDate(\DateTimeImmutable $baseDate): ?\DateTimeImmutable
    {
        if (!$this->recurring || !$this->recurrencePattern) {
            return null;
        }
        
        return $this->recurrencePattern->calculateNextDate($baseDate);
    }

    public function getRecurrencePattern(): ?RecurrencePatternEnum
    {
        return $this->recurrencePattern;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    private function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    public function getEvents(): array
    {
        return $this->domainEvents;
    }

    public function clearEvents(): void
    {
        $this->domainEvents = [];
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
