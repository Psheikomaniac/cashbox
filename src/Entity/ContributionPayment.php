<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\ContributionPaymentRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post()
    ],
    normalizationContext: ['groups' => ['contribution_payment:read']],
    denormalizationContext: ['groups' => ['contribution_payment:write']]
)]
#[ORM\Entity(repositoryClass: ContributionPaymentRepository::class)]
class ContributionPayment
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['contribution_payment:read'])]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Contribution::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['contribution_payment:read', 'contribution_payment:write'])]
    private Contribution $contribution;

    #[ORM\Column]
    #[Groups(['contribution_payment:read', 'contribution_payment:write'])]
    private int $amount;

    #[ORM\Column(length: 3)]
    #[Groups(['contribution_payment:read', 'contribution_payment:write'])]
    private string $currency = 'EUR';

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['contribution_payment:read', 'contribution_payment:write'])]
    private ?string $paymentMethod = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['contribution_payment:read', 'contribution_payment:write'])]
    private ?string $reference = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['contribution_payment:read', 'contribution_payment:write'])]
    private ?string $notes = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column]
    #[Groups(['contribution_payment:read'])]
    private \DateTimeImmutable $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column]
    #[Groups(['contribution_payment:read'])]
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

    public function getContribution(): Contribution
    {
        return $this->contribution;
    }

    public function setContribution(Contribution $contribution): self
    {
        $this->contribution = $contribution;

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

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

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
