<?php

namespace App\Entity;

use App\Enum\CurrencyEnum;
use App\Enum\RecurrencePatternEnum;
use App\Event\ContributionTemplateCreatedEvent;
use App\Event\ContributionTemplateAppliedEvent;
use App\Repository\ContributionTemplateRepository;
use App\ValueObject\Money;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContributionTemplateRepository::class)]
#[ORM\Table(name: 'contribution_templates')]
#[ORM\Index(columns: ['team_id', 'active'], name: 'idx_team_active')]
class ContributionTemplate implements AggregateRootInterface
{
    use EventRecorderTrait;
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['contribution_template:read'])]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['contribution_template:read', 'contribution_template:write'])]
    private Team $team;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['contribution_template:read', 'contribution_template:write'])]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(max: 1000)]
    #[Groups(['contribution_template:read', 'contribution_template:write'])]
    private ?string $description = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\Positive]
    #[Groups(['contribution_template:read'])]
    private int $amountCents;

    #[ORM\Column(type: 'string', length: 3, enumType: CurrencyEnum::class)]
    #[Groups(['contribution_template:read'])]
    private CurrencyEnum $currency;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['contribution_template:read', 'contribution_template:write'])]
    private bool $recurring = false;

    #[ORM\Column(type: 'string', length: 255, enumType: RecurrencePatternEnum::class, nullable: true)]
    #[Groups(['contribution_template:read', 'contribution_template:write'])]
    private ?RecurrencePatternEnum $recurrencePattern = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Range(min: 1, max: 365)]
    #[Groups(['contribution_template:read', 'contribution_template:write'])]
    private ?int $dueDays = null;

    #[ORM\Column]
    #[Groups(['contribution_template:read'])]
    private bool $active = true;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column]
    #[Groups(['contribution_template:read'])]
    private \DateTimeImmutable $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column]
    #[Groups(['contribution_template:read'])]
    private \DateTimeImmutable $updatedAt;

    private array $domainEvents = [];

    public function __construct(
        Team $team,
        string $name,
        Money $amount,
        ?string $description = null,
        bool $recurring = false,
        ?RecurrencePatternEnum $recurrencePattern = null,
        ?int $dueDays = null
    ) {
        $this->id = Uuid::uuid7();
        $this->team = $team;
        $this->name = $name;
        $this->description = $description;
        $this->amountCents = $amount->getCents();
        $this->currency = $amount->getCurrency();
        $this->recurring = $recurring;
        $this->recurrencePattern = $recurrencePattern;
        $this->dueDays = $dueDays;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        
        $this->validateConfiguration();
        
        $this->recordEvent(new ContributionTemplateCreatedEvent($this));
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): self
    {
        $this->team = $team;

        return $this;
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

    public function applyToUsers(array $teamUsers): array
    {
        $contributions = [];
        
        foreach ($teamUsers as $teamUser) {
            $dueDate = $this->calculateDueDate();
            $contribution = new Contribution(
                $teamUser,
                $this->createContributionType(),
                $this->name,
                $this->getAmount(),
                $dueDate
            );
            
            $contributions[] = $contribution;
        }
        
        $this->recordEvent(new ContributionTemplateAppliedEvent($this, count($teamUsers)));
        
        return $contributions;
    }

    public function update(
        string $name,
        Money $amount,
        ?string $description = null,
        bool $recurring = false,
        ?RecurrencePatternEnum $recurrencePattern = null,
        ?int $dueDays = null
    ): void {
        $this->name = $name;
        $this->description = $description;
        $this->amountCents = $amount->getCents();
        $this->currency = $amount->getCurrency();
        $this->recurring = $recurring;
        $this->recurrencePattern = $recurrencePattern;
        $this->dueDays = $dueDays;
        $this->updatedAt = new \DateTimeImmutable();
        
        $this->validateConfiguration();
    }

    private function validateConfiguration(): void
    {
        if ($this->recurring && $this->recurrencePattern === null) {
            throw new \InvalidArgumentException('Recurrence pattern is required for recurring templates');
        }
        
        if ($this->dueDays !== null && ($this->dueDays < 1 || $this->dueDays > 365)) {
            throw new \InvalidArgumentException('Due days must be between 1 and 365');
        }
    }

    private function calculateDueDate(): \DateTimeImmutable
    {
        $baseDate = new \DateTimeImmutable();
        
        if ($this->dueDays !== null) {
            return $baseDate->modify("+{$this->dueDays} days");
        }
        
        return $baseDate->modify('+30 days');
    }

    private function createContributionType(): ContributionType
    {
        return new ContributionType(
            $this->name,
            $this->description,
            $this->recurring,
            $this->recurrencePattern
        );
    }

    public function getAmount(): Money
    {
        return new Money($this->amountCents, $this->currency);
    }

    public function isRecurring(): bool
    {
        return $this->recurring;
    }

    public function setRecurring(bool $recurring): self
    {
        $this->recurring = $recurring;

        return $this;
    }

    public function getRecurrencePattern(): ?RecurrencePatternEnum
    {
        return $this->recurrencePattern;
    }

    public function setRecurrencePattern(?string $recurrencePattern): self
    {
        $this->recurrencePattern = $recurrencePattern;

        return $this;
    }

    public function getDueDays(): ?int
    {
        return $this->dueDays;
    }

    public function setDueDays(?int $dueDays): self
    {
        $this->dueDays = $dueDays;

        return $this;
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
