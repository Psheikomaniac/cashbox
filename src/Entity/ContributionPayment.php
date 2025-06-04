<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Enum\CurrencyEnum;
use App\Enum\PaymentTypeEnum;
use App\Event\ContributionPaymentRecordedEvent;
use App\Repository\ContributionPaymentRepository;
use App\ValueObject\Money;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

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
#[ORM\Table(name: 'contribution_payments')]
#[ORM\Index(columns: ['contribution_id'], name: 'idx_contribution')]
#[ORM\Index(columns: ['created_at'], name: 'idx_payment_created_at')]
class ContributionPayment implements AggregateRootInterface
{
    use EventRecorderTrait;
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['contribution_payment:read'])]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Contribution::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['contribution_payment:read', 'contribution_payment:write'])]
    private Contribution $contribution;

    #[ORM\Column(type: 'integer')]
    #[Assert\Positive]
    #[Groups(['contribution_payment:read'])]
    private int $amountCents;

    #[ORM\Column(type: 'string', length: 3, enumType: CurrencyEnum::class)]
    #[Groups(['contribution_payment:read'])]
    private CurrencyEnum $currency;

    #[ORM\Column(type: 'string', length: 255, enumType: PaymentTypeEnum::class, nullable: true)]
    #[Groups(['contribution_payment:read', 'contribution_payment:write'])]
    private ?PaymentTypeEnum $paymentMethod = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['contribution_payment:read', 'contribution_payment:write'])]
    private ?string $reference = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(max: 1000)]
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

    private array $domainEvents = [];

    public function __construct(
        Contribution $contribution,
        Money $amount,
        ?PaymentTypeEnum $paymentMethod = null,
        ?string $reference = null,
        ?string $notes = null
    ) {
        $this->id = Uuid::uuid7();
        $this->contribution = $contribution;
        $this->amountCents = $amount->getCents();
        $this->currency = $amount->getCurrency();
        $this->paymentMethod = $paymentMethod;
        $this->reference = $reference;
        $this->notes = $notes;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        
        $this->validatePayment();
        
        $this->recordEvent(new ContributionPaymentRecordedEvent($this));
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

    public function update(
        ?PaymentTypeEnum $paymentMethod = null,
        ?string $reference = null,
        ?string $notes = null
    ): void {
        $this->paymentMethod = $paymentMethod;
        $this->reference = $reference;
        $this->notes = $notes;
        $this->updatedAt = new \DateTimeImmutable();
        
        $this->validatePayment();
    }

    private function validatePayment(): void
    {
        if ($this->currency !== $this->contribution->getAmount()->getCurrency()) {
            throw new \InvalidArgumentException('Payment currency must match contribution currency');
        }
        
        if ($this->paymentMethod && $this->paymentMethod->requiresReference() && empty($this->reference)) {
            throw new \InvalidArgumentException('Reference is required for this payment method');
        }
    }

    public function isPartialPayment(): bool
    {
        return $this->amountCents < $this->contribution->getAmount()->getCents();
    }

    public function getAmount(): Money
    {
        return new Money($this->amountCents, $this->currency);
    }

    public function getPaymentMethod(): ?PaymentTypeEnum
    {
        return $this->paymentMethod;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    private function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    public function getEvents(): array
    {
        return $this->domainEvents;
    }

    public function clearEvents(): void
    {
        $this->domainEvents = [];
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
