<?php

namespace App\Entity;

use App\Enum\CurrencyEnum;
use App\Enum\PaymentTypeEnum;
use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: TeamUser::class)]
    #[ORM\JoinColumn(nullable: false)]
    private TeamUser $teamUser;

    #[ORM\Column]
    private int $amount;

    #[ORM\Column(length: 3)]
    private string $currency = CurrencyEnum::EUR->value;

    #[ORM\Column(length: 30)]
    private string $type = PaymentTypeEnum::CASH->value;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
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
