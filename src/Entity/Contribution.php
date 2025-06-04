<?php

namespace App\Entity;

use App\Enum\CurrencyEnum;
use App\Event\ContributionCreatedEvent;
use App\Event\ContributionPaidEvent;
use App\Repository\ContributionRepository;
use App\ValueObject\Money;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContributionRepository::class)]
#[ORM\Table(name: 'contributions')]
#[ORM\Index(columns: ['team_user_id', 'active'], name: 'idx_team_user_active')]
#[ORM\Index(columns: ['due_date'], name: 'idx_due_date')]
#[ORM\Index(columns: ['paid_at'], name: 'idx_paid_at')]
class Contribution implements AggregateRootInterface
{
    use EventRecorderTrait;
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['contribution:read'])]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: TeamUser::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['contribution:read', 'contribution:write'])]
    private TeamUser $teamUser;

    #[ORM\ManyToOne(targetEntity: ContributionType::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['contribution:read', 'contribution:write'])]
    private ContributionType $type;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['contribution:read', 'contribution:write'])]
    private string $description;

    #[ORM\Column(type: 'integer')]
    #[Assert\Positive]
    #[Groups(['contribution:read'])]
    private int $amountCents;

    #[ORM\Column(type: 'string', length: 3, enumType: CurrencyEnum::class)]
    #[Groups(['contribution:read'])]
    private CurrencyEnum $currency;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['contribution:read', 'contribution:write'])]
    private \DateTimeInterface $dueDate;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['contribution:read', 'contribution:write'])]
    private ?\DateTimeInterface $paidAt = null;

    #[ORM\Column]
    #[Groups(['contribution:read'])]
    private bool $active = true;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['contribution:read'])]
    private \DateTimeImmutable $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['contribution:read'])]
    private \DateTimeImmutable $updatedAt;

    private array $domainEvents = [];

    public function __construct(
        TeamUser $teamUser,
        ContributionType $type,
        string $description,
        Money $amount,
        \DateTimeImmutable $dueDate
    ) {
        $this->id = Uuid::uuid7();
        $this->teamUser = $teamUser;
        $this->type = $type;
        $this->description = $description;
        $this->amountCents = $amount->getCents();
        $this->currency = $amount->getCurrency();
        $this->dueDate = $dueDate;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        
        $this->recordEvent(new ContributionCreatedEvent($this));
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTeamUser(): TeamUser
    {
        return $this->teamUser;
    }

    public function setTeamUser(TeamUser $teamUser): self
    {
        $this->teamUser = $teamUser;

        return $this;
    }

    public function getType(): ContributionType
    {
        return $this->type;
    }

    public function setType(ContributionType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function pay(): void
    {
        if ($this->isPaid()) {
            throw new \DomainException('Contribution is already paid');
        }
        
        $this->paidAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        
        $this->recordEvent(new ContributionPaidEvent($this));
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

    public function updateDueDate(\DateTimeImmutable $dueDate): void
    {
        if ($this->isPaid()) {
            throw new \DomainException('Cannot update due date for paid contribution');
        }
        
        $this->dueDate = $dueDate;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isPaid(): bool
    {
        return $this->paidAt !== null;
    }

    public function isOverdue(): bool
    {
        return !$this->isPaid() && $this->dueDate < new \DateTimeImmutable();
    }

    public function getAmount(): Money
    {
        return new Money($this->amountCents, $this->currency);
    }

    public function getDueDate(): \DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeInterface $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getPaidAt(): ?\DateTimeInterface
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeInterface $paidAt): self
    {
        $this->paidAt = $paidAt;

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
