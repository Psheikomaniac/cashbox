<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\ReportRepository;
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
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['report:read']],
    denormalizationContext: ['groups' => ['report:write']]
)]
#[ORM\Entity(repositoryClass: ReportRepository::class)]
class Report
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['report:read'])]
    private UuidInterface $id;

    #[ORM\Column(length: 255)]
    #[Groups(['report:read', 'report:write'])]
    private string $name;

    #[ORM\Column(length: 255)]
    #[Groups(['report:read', 'report:write'])]
    private string $type;

    #[ORM\Column(type: 'json')]
    #[Groups(['report:read', 'report:write'])]
    private array $parameters = [];

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['report:read'])]
    private ?array $result = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['report:read', 'report:write'])]
    private User $createdBy;

    #[ORM\Column]
    #[Groups(['report:read', 'report:write'])]
    private bool $scheduled = false;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['report:read', 'report:write'])]
    private ?string $cronExpression = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column]
    #[Groups(['report:read'])]
    private \DateTimeImmutable $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column]
    #[Groups(['report:read'])]
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getResult(): ?array
    {
        return $this->result;
    }

    public function setResult(?array $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function isScheduled(): bool
    {
        return $this->scheduled;
    }

    public function setScheduled(bool $scheduled): self
    {
        $this->scheduled = $scheduled;

        return $this;
    }

    public function getCronExpression(): ?string
    {
        return $this->cronExpression;
    }

    public function setCronExpression(?string $cronExpression): self
    {
        $this->cronExpression = $cronExpression;

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
