<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\DTO\Team\CreateTeamDTO;
use App\DTO\Team\UpdateTeamDTO;
use App\Event\TeamActivatedEvent;
use App\Event\TeamCreatedEvent;
use App\Event\TeamDeactivatedEvent;
use App\Event\TeamRenamedEvent;
use App\Repository\TeamRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use Gedmo\Mapping\Annotation as Gedmo;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Team',
    operations: [
        new GetCollection(
            uriTemplate: '/teams',
            security: "is_granted('ROLE_USER')",
            name: 'get_teams'
        ),
        new Get(
            uriTemplate: '/teams/{id}',
            requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'],
            security: "is_granted('TEAM_VIEW', object)"
        ),
        new Post(
            uriTemplate: '/teams',
            security: "is_granted('ROLE_ADMIN')",
            input: CreateTeamDTO::class
        ),
        new Put(
            uriTemplate: '/teams/{id}',
            requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'],
            security: "is_granted('TEAM_EDIT', object)",
            input: UpdateTeamDTO::class
        ),
        new Delete(
            uriTemplate: '/teams/{id}',
            requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'],
            security: "is_granted('TEAM_DELETE', object)"
        )
    ],
    formats: ['jsonld', 'json', 'csv'],
    normalizationContext: ['groups' => ['team:read']],
    denormalizationContext: ['groups' => ['team:write']],
    filters: [
        'teams.search_filter',
        'teams.order_filter',
        'teams.date_filter'
    ],
    paginationItemsPerPage: 20
)]
#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[ORM\Table(name: 'teams')]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt')]
class Team implements AggregateRootInterface
{
    use EventRecorderTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['team:read'])]
    private UuidInterface $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[Groups(['team:read', 'team:write'])]
    private string $name;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['team:read'])]
    private string $externalId;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['team:read'])]
    private bool $active = true;

    #[ORM\Column(type: 'json')]
    private array $metadata = [];

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $deletedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['team:read'])]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    #[Groups(['team:read'])]
    private DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(targetEntity: TeamUser::class, mappedBy: 'team')]
    private Collection $teamUsers;

    private function __construct(
        UuidInterface $id,
        string $name,
        string $externalId
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->externalId = $externalId;
        $this->teamUsers = new ArrayCollection();

        $this->record(new TeamCreatedEvent($id, $name, $externalId));
    }

    public static function create(
        string $name,
        string $externalId
    ): self {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('Team name cannot be empty');
        }
        if (empty(trim($externalId))) {
            throw new InvalidArgumentException('External ID cannot be empty');
        }

        return new self(
            Uuid::uuid7(),
            trim($name),
            trim($externalId)
        );
    }

    public function rename(string $newName): void
    {
        if (empty(trim($newName))) {
            throw new InvalidArgumentException('Team name cannot be empty');
        }
        if ($this->name === trim($newName)) {
            throw new InvalidArgumentException('New name must be different from current name');
        }

        $oldName = $this->name;
        $this->name = trim($newName);

        $this->record(new TeamRenamedEvent($this->id, $oldName, $newName));
    }

    public function deactivate(): void
    {
        if (!$this->active) {
            throw new DomainException('Team is already inactive');
        }

        $this->active = false;
        $this->record(new TeamDeactivatedEvent($this->id));
    }

    public function activate(): void
    {
        if ($this->active) {
            throw new DomainException('Team is already active');
        }

        $this->active = true;
        $this->record(new TeamActivatedEvent($this->id));
    }

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function getMetadata(string $key): mixed
    {
        return $this->metadata[$key] ?? null;
    }

    public function getAllMetadata(): array
    {
        return $this->metadata;
    }

    // Legacy compatibility methods - to be removed in future versions
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
        $this->rename($name);
        return $this;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): self
    {
        if (empty(trim($externalId))) {
            throw new InvalidArgumentException('External ID cannot be empty');
        }
        $this->externalId = trim($externalId);
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        if ($active && !$this->active) {
            $this->activate();
        } elseif (!$active && $this->active) {
            $this->deactivate();
        }
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
