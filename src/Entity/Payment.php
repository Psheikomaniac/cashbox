<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\DTO\Payment\CreatePaymentDTO;
use App\DTO\Payment\UpdatePaymentDTO;
use App\Enum\CurrencyEnum;
use App\Enum\PaymentTypeEnum;
use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'Payment',
    operations: [
        new GetCollection(
            uriTemplate: '/payments',
            security: "is_granted('ROLE_USER')",
            name: 'get_payments'
        ),
        new Get(
            uriTemplate: '/payments/{id}',
            requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'],
            security: "is_granted('PAYMENT_VIEW', object)"
        ),
        new Post(
            uriTemplate: '/payments',
            security: "is_granted('ROLE_USER')",
            input: CreatePaymentDTO::class
        ),
        new Put(
            uriTemplate: '/payments/{id}',
            requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'],
            security: "is_granted('PAYMENT_EDIT', object)",
            input: UpdatePaymentDTO::class
        )
    ],
    formats: ['jsonld', 'json', 'csv'],
    normalizationContext: ['groups' => ['payment:read']],
    denormalizationContext: ['groups' => ['payment:write']],
    paginationItemsPerPage: 20
)]
#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['payment:read'])]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: TeamUser::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['payment:read', 'payment:write'])]
    private TeamUser $teamUser;

    #[ORM\Column]
    #[Groups(['payment:read', 'payment:write'])]
    private int $amount;

    #[ORM\Column(length: 3)]
    #[Groups(['payment:read', 'payment:write'])]
    private string $currency = CurrencyEnum::EUR->value;

    #[ORM\Column(length: 30)]
    #[Groups(['payment:read', 'payment:write'])]
    private string $type = PaymentTypeEnum::CASH->value;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['payment:read', 'payment:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['payment:read', 'payment:write'])]
    private ?string $reference = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column]
    #[Groups(['payment:read'])]
    private \DateTimeImmutable $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column]
    #[Groups(['payment:read'])]
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

    public function getType(): PaymentTypeEnum
    {
        return PaymentTypeEnum::from($this->type);
    }

    public function setType(PaymentTypeEnum $type): self
    {
        $this->type = $type->value;

        return $this;
    }

    public function getFormattedAmount(): string
    {
        return $this->getCurrency()->formatAmount($this->amount);
    }

    public function requiresReference(): bool
    {
        return $this->getType()->requiresReference();
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

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

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
