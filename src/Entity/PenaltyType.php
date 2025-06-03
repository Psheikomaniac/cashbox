<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Enum\PenaltyTypeEnum;
use App\Repository\PenaltyTypeRepository;
use DateTimeImmutable;
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
    normalizationContext: ['groups' => ['penalty_type:read']],
    denormalizationContext: ['groups' => ['penalty_type:write']]
)]
#[ORM\Entity(repositoryClass: PenaltyTypeRepository::class)]
#[ORM\Table(name: 'penalty_types')]
class PenaltyType
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['penalty_type:read'])]
    private UuidInterface $id;

    #[ORM\Column(length: 255)]
    #[Groups(['penalty_type:read', 'penalty_type:write'])]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['penalty_type:read', 'penalty_type:write'])]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 30)]
    #[Groups(['penalty_type:read', 'penalty_type:write'])]
    private string $type;

    #[ORM\Column(type: 'integer')]
    #[Groups(['penalty_type:read'])]
    private int $defaultAmount;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['penalty_type:read'])]
    private bool $active = true;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['penalty_type:read'])]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    #[Groups(['penalty_type:read'])]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $name,
        PenaltyTypeEnum $type,
        ?string $description = null
    ) {
        $this->id = Uuid::uuid7();
        $this->name = $name;
        $this->type = $type->value;
        $this->defaultAmount = $type->getDefaultAmount();
        $this->description = $description;
    }

    public function getType(): PenaltyTypeEnum
    {
        return PenaltyTypeEnum::from($this->type);
    }

    public function setType(PenaltyTypeEnum $type): self
    {
        $this->type = $type->value;
        $this->defaultAmount = $type->getDefaultAmount();

        return $this;
    }

    public function isDrink(): bool
    {
        return $this->getType()->isDrink();
    }

    public function getDefaultAmount(): int
    {
        return $this->defaultAmount;
    }

    public function setDefaultAmount(int $defaultAmount): self
    {
        $this->defaultAmount = $defaultAmount;
        return $this;
    }

    // Getters
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isActive(): bool
    {
        return $this->active;
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
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }
}
