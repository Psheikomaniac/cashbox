<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\ApiPlatform\Processor\ContributionProcessor;
use App\ApiPlatform\Provider\ContributionCollectionProvider;
use App\ApiPlatform\Provider\ContributionItemProvider;
use App\DTO\Contribution\ContributionResponseDTO;
use App\DTO\Contribution\CreateContributionDTO;
use App\DTO\Contribution\UpdateContributionDTO;
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

#[ApiResource(
    shortName: 'Contribution',
    operations: [
        new GetCollection(
            uriTemplate: '/contributions',
            security: "is_granted('ROLE_USER')",
            name: 'get_contributions',
            provider: ContributionCollectionProvider::class,
            output: ContributionResponseDTO::class
        ),
        new GetCollection(
            uriTemplate: '/contributions/unpaid',
            security: "is_granted('ROLE_USER')",
            name: 'get_unpaid_contributions',
            provider: ContributionCollectionProvider::class,
            output: ContributionResponseDTO::class,
            extraProperties: ['filters' => ['unpaid' => 'true']]
        ),
        new GetCollection(
            uriTemplate: '/contributions/upcoming',
            security: "is_granted('ROLE_USER')",
            name: 'get_upcoming_contributions',
            provider: ContributionCollectionProvider::class,
            output: ContributionResponseDTO::class,
            extraProperties: ['filters' => ['upcoming' => 'true']]
        ),
        new GetCollection(
            uriTemplate: '/contributions/team/{teamId}',
            security: "is_granted('ROLE_USER')",
            name: 'get_team_contributions',
            provider: ContributionCollectionProvider::class,
            output: ContributionResponseDTO::class,
            uriVariables: ['teamId' => ['from_class' => 'App\Entity\Team', 'to_class' => 'App\Entity\Contribution']],
            extraProperties: ['filters' => ['team' => '{teamId}']]
        ),
        new GetCollection(
            uriTemplate: '/contributions/user/{userId}',
            security: "is_granted('ROLE_USER')",
            name: 'get_user_contributions',
            provider: ContributionCollectionProvider::class,
            output: ContributionResponseDTO::class,
            uriVariables: ['userId' => ['from_class' => 'App\Entity\User', 'to_class' => 'App\Entity\Contribution']],
            extraProperties: ['filters' => ['user' => '{userId}']]
        ),
        new Get(
            uriTemplate: '/contributions/{id}',
            requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'],
            security: "is_granted('ROLE_USER')",
            provider: ContributionItemProvider::class,
            output: ContributionResponseDTO::class
        ),
        new Post(
            uriTemplate: '/contributions',
            security: "is_granted('ROLE_USER')",
            input: CreateContributionDTO::class,
            output: ContributionResponseDTO::class,
            processor: ContributionProcessor::class
        ),
        new Put(
            uriTemplate: '/contributions/{id}',
            requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'],
            security: "is_granted('ROLE_USER')",
            input: UpdateContributionDTO::class,
            output: ContributionResponseDTO::class,
            processor: ContributionProcessor::class
        ),
        new Post(
            uriTemplate: '/contributions/{id}/pay',
            requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'],
            security: "is_granted('ROLE_USER')",
            name: 'pay',
            output: ContributionResponseDTO::class,
            processor: ContributionProcessor::class
        ),
        new Delete(
            uriTemplate: '/contributions/{id}',
            requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'],
            security: "is_granted('ROLE_USER')",
            processor: ContributionProcessor::class,
            output: false
        )
    ],
    formats: ['jsonld', 'json', 'csv'],
    normalizationContext: ['groups' => ['contribution:read']],
    denormalizationContext: ['groups' => ['contribution:write']],
    paginationItemsPerPage: 20
)]
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
