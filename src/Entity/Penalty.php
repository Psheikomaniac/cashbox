<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\CurrencyEnum;
use App\Repository\PenaltyRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    normalizationContext: ['groups' => ['penalty:read']],
    denormalizationContext: ['groups' => ['penalty:write']]
)]
#[ORM\Entity(repositoryClass: PenaltyRepository::class)]
class Penalty
{
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

    #[ORM\Column]
    #[Groups(['penalty:read', 'penalty:write'])]
    private int $amount;

    #[ORM\Column(length: 3)]
    #[Groups(['penalty:read', 'penalty:write'])]
    private string $currency = CurrencyEnum::EUR->value;

    #[ORM\Column]
    #[Groups(['penalty:read', 'penalty:write'])]
    private bool $archived = false;

    #[ORM\Column(nullable: true)]
    #[Groups(['penalty:read', 'penalty:write'])]
    private ?\DateTimeImmutable $paidAt = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column]
    #[Groups(['penalty:read'])]
    private \DateTimeImmutable $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column]
    #[Groups(['penalty:read'])]
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

    public function getTeamUser(): TeamUser
    {
        return $this->teamUser;
    }

    public function setTeamUser(TeamUser $teamUser): self
    {
        $this->teamUser = $teamUser;

        return $this;
    }

    public function getType(): PenaltyType
    {
        return $this->type;
    }

    public function setType(PenaltyType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): CurrencyEnum
    {
        return CurrencyEnum::from($this->currency);
    }

    public function setCurrency(CurrencyEnum $currency): self
    {
        $this->currency = $currency->value;

        return $this;
    }

    public function getFormattedAmount(): string
    {
        return $this->getCurrency()->formatAmount($this->amount);
    }

    public function isArchived(): bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived): self
    {
        $this->archived = $archived;

        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeImmutable $paidAt): self
    {
        $this->paidAt = $paidAt;

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
