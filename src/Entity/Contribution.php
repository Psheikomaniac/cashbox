<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\ContributionRepository;
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
        new Patch()
    ],
    normalizationContext: ['groups' => ['contribution:read']],
    denormalizationContext: ['groups' => ['contribution:write']]
)]
#[ORM\Entity(repositoryClass: ContributionRepository::class)]
class Contribution
{
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

    #[ORM\Column(length: 255)]
    #[Groups(['contribution:read', 'contribution:write'])]
    private string $description;

    #[ORM\Column]
    #[Groups(['contribution:read', 'contribution:write'])]
    private int $amount;

    #[ORM\Column(length: 3)]
    #[Groups(['contribution:read', 'contribution:write'])]
    private string $currency = 'EUR';

    #[ORM\Column]
    #[Groups(['contribution:read', 'contribution:write'])]
    private \DateTimeInterface $dueDate;

    #[ORM\Column(nullable: true)]
    #[Groups(['contribution:read', 'contribution:write'])]
    private ?\DateTimeInterface $paidAt = null;

    #[ORM\Column]
    #[Groups(['contribution:read'])]
    private bool $active = true;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column]
    #[Groups(['contribution:read'])]
    private \DateTimeImmutable $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column]
    #[Groups(['contribution:read'])]
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

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
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

    public function setActive(bool $active): self
    {
        $this->active = $active;

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
}
