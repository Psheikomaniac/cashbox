<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\PenaltyTypeEnum;
use App\Repository\PenaltyTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    normalizationContext: ['groups' => ['penalty_type:read']],
    denormalizationContext: ['groups' => ['penalty_type:write']]
)]
#[ORM\Entity(repositoryClass: PenaltyTypeRepository::class)]
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

    #[ORM\Column(length: 30)]
    #[Groups(['penalty_type:read', 'penalty_type:write'])]
    private string $type;

    #[ORM\Column]
    #[Groups(['penalty_type:read', 'penalty_type:write'])]
    private bool $active = true;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column]
    #[Groups(['penalty_type:read'])]
    private \DateTimeImmutable $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column]
    #[Groups(['penalty_type:read'])]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
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

    public function getType(): PenaltyTypeEnum
    {
        return PenaltyTypeEnum::from($this->type);
    }

    public function setType(PenaltyTypeEnum $type): self
    {
        $this->type = $type->value;

        return $this;
    }

    public function isDrink(): bool
    {
        return $this->getType()->isDrink();
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
