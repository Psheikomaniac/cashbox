<?php

namespace App\Entity;

use App\Enum\CurrencyEnum;
use App\Event\PenaltyArchivedEvent;
use App\Event\PenaltyCreatedEvent;
use App\Event\PenaltyPaidEvent;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\PenaltyRepository;
use App\ValueObject\Money;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put()
    ],
    normalizationContext: ['groups' => ['penalty:read']],
    denormalizationContext: ['groups' => ['penalty:write']]
)]
#[ORM\Entity(repositoryClass: PenaltyRepository::class)]
#[ORM\Table(name: 'penalties')]
#[ORM\HasLifecycleCallbacks]
class Penalty implements AggregateRootInterface
{
    use EventRecorderTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['penalty:read'])]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: TeamUser::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['penalty:read', 'penalty:write'])]
    private TeamUser $teamUser;

    #[ORM\ManyToOne(targetEntity: PenaltyType::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['penalty:read', 'penalty:write'])]
    private PenaltyType $type;

    #[ORM\Column(length: 255)]
    #[Groups(['penalty:read', 'penalty:write'])]
    private string $reason;

    #[ORM\Column(type: 'money')]
    #[Groups(['penalty:read', 'penalty:write'])]
    private Money $money;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['penalty:read'])]
    private bool $archived = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['penalty:read', 'penalty:write'])]
    private ?DateTimeImmutable $paidAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['penalty:read'])]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    #[Groups(['penalty:read'])]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        TeamUser $teamUser,
        PenaltyType $type,
        string $reason,
        Money $money
    ) {
        $this->id = Uuid::uuid7();
        $this->teamUser = $teamUser;
        $this->type = $type;
        $this->reason = $reason;
        $this->money = $money;

        $this->record(new PenaltyCreatedEvent(
            $this->id,
            $teamUser->getUser()->getId(),
            $teamUser->getTeam()->getId(),
            $reason,
            $money
        ));
    }

    public function pay(?DateTimeImmutable $paidAt = null): void
    {
        if ($this->paidAt !== null) {
            throw new DomainException('Penalty is already paid');
        }

        $this->paidAt = $paidAt ?? new DateTimeImmutable();

        $this->record(new PenaltyPaidEvent(
            $this->id,
            $this->paidAt
        ));
    }

    public function archive(): void
    {
        if ($this->archived) {
            throw new DomainException('Penalty is already archived');
        }

        $this->archived = true;

        $this->record(new PenaltyArchivedEvent($this->id));
    }

    public function getMoney(): Money
    {
        return $this->money;
    }

    public function setMoney(Money $money): self
    {
        $this->money = $money;
        return $this;
    }

    public function getCurrency(): CurrencyEnum
    {
        return $this->money->getCurrency();
    }

    public function getAmount(): int
    {
        return $this->money->getAmount();
    }

    public function getFormattedAmount(): string
    {
        return $this->money->format();
    }

    public function isPaid(): bool
    {
        return $this->paidAt !== null;
    }

    // Getters
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTeamUser(): TeamUser
    {
        return $this->teamUser;
    }

    public function getType(): PenaltyType
    {
        return $this->type;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function isArchived(): bool
    {
        return $this->archived;
    }

    public function getPaidAt(): ?DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Legacy compatibility methods - to be removed in future versions
    public function setTeamUser(TeamUser $teamUser): self
    {
        $this->teamUser = $teamUser;
        return $this;
    }

    public function setType(PenaltyType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }

    public function setAmount(int $amount): self
    {
        $this->money = new Money($amount, $this->money->getCurrency());
        return $this;
    }

    public function setCurrency(CurrencyEnum $currency): self
    {
        $this->money = new Money($this->money->getAmount(), $currency);
        return $this;
    }

    public function setArchived(bool $archived): self
    {
        if ($archived && !$this->archived) {
            $this->archive();
        }
        return $this;
    }

    public function setPaidAt(?DateTimeImmutable $paidAt): self
    {
        if ($paidAt && !$this->isPaid()) {
            $this->pay($paidAt);
        }
        return $this;
    }
}
