<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Contribution;
use App\ValueObject\Money;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Modern PHP 8.4 readonly output DTO with enhanced business logic
 */
final readonly class ContributionOutputDTO
{
    public function __construct(
        public string $id,
        public string $teamUserId,
        public string $typeId,
        public string $typeName,
        public string $description,
        public Money $amount,
        public string $dueDate,
        public ?string $paidAt,
        public bool $active,
        public bool $isPaid,
        public bool $isOverdue,
        public string $status,
        public int $daysUntilDue,
        public int $daysSinceDue,
        public string $createdAt,
        public string $updatedAt,
        public array $metadata = []
    ) {}

    /**
     * Create from Contribution entity following documentation standards
     */
    public static function fromEntity(Contribution $contribution): self
    {
        $dueDate = $contribution->getDueDate();
        $now = new DateTimeImmutable();
        $daysUntilDue = (int) $now->diff($dueDate)->format('%r%a');
        $daysSinceDue = $daysUntilDue < 0 ? abs($daysUntilDue) : 0;

        return new self(
            id: $contribution->getId()->toString(),
            teamUserId: $contribution->getTeamUser()->getId()->toString(),
            typeId: $contribution->getType()->getId()->toString(),
            typeName: $contribution->getType()->getName(),
            description: $contribution->getDescription(),
            amount: $contribution->getAmount(),
            dueDate: $dueDate->format('Y-m-d'),
            paidAt: $contribution->getPaidAt()?->format('Y-m-d'),
            active: $contribution->isActive(),
            isPaid: $contribution->isPaid(),
            isOverdue: $contribution->isOverdue(),
            status: self::determineStatus($contribution),
            daysUntilDue: max(0, $daysUntilDue),
            daysSinceDue: $daysSinceDue,
            createdAt: $contribution->getCreatedAt()->format(DateTimeInterface::ATOM),
            updatedAt: $contribution->getUpdatedAt()->format(DateTimeInterface::ATOM),
            metadata: [
                'formattedAmount' => $contribution->getAmount()->format(),
                'currency' => $contribution->getAmount()->getCurrency()->value,
                'isRecent' => $contribution->getCreatedAt() > new DateTimeImmutable('-7 days'),
                'urgencyLevel' => self::getUrgencyLevel($contribution),
                'paymentWindow' => self::getPaymentWindow($dueDate, $contribution->isPaid()),
                'teamUserName' => $contribution->getTeamUser()->getUser()->getFullName(),
            ]
        );
    }

    /**
     * Convert to array for API responses
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'teamUserId' => $this->teamUserId,
            'typeId' => $this->typeId,
            'typeName' => $this->typeName,
            'description' => $this->description,
            'amount' => [
                'value' => $this->amount->getAmount() / 100,
                'currency' => $this->amount->getCurrency()->value,
                'formatted' => $this->amount->format(),
            ],
            'dueDate' => $this->dueDate,
            'paidAt' => $this->paidAt,
            'active' => $this->active,
            'isPaid' => $this->isPaid,
            'isOverdue' => $this->isOverdue,
            'status' => $this->status,
            'daysUntilDue' => $this->daysUntilDue,
            'daysSinceDue' => $this->daysSinceDue,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Determine contribution status based on payment and due date
     */
    private static function determineStatus(Contribution $contribution): string
    {
        if ($contribution->isPaid()) {
            return 'paid';
        }

        if ($contribution->isOverdue()) {
            return 'overdue';
        }

        $daysUntilDue = new DateTimeImmutable()->diff($contribution->getDueDate())->days;
        if ($daysUntilDue <= 3) {
            return 'due_soon';
        }

        return 'pending';
    }

    /**
     * Get urgency level for UI prioritization
     */
    private static function getUrgencyLevel(Contribution $contribution): string
    {
        if ($contribution->isPaid()) {
            return 'none';
        }

        if ($contribution->isOverdue()) {
            return 'critical';
        }

        $daysUntilDue = new DateTimeImmutable()->diff($contribution->getDueDate())->days;
        
        return match (true) {
            $daysUntilDue <= 1 => 'high',
            $daysUntilDue <= 7 => 'medium',
            default => 'low'
        };
    }

    /**
     * Get payment window description
     */
    private static function getPaymentWindow(DateTimeImmutable $dueDate, bool $isPaid): string
    {
        if ($isPaid) {
            return 'completed';
        }

        $now = new DateTimeImmutable();
        $diff = $now->diff($dueDate);
        $days = (int) $diff->format('%r%a');

        return match (true) {
            $days < 0 => sprintf('%d days overdue', abs($days)),
            $days === 0 => 'due today',
            $days === 1 => 'due tomorrow',
            default => sprintf('due in %d days', $days)
        };
    }

    /**
     * Check if contribution requires immediate attention
     */
    public function requiresAttention(): bool
    {
        return $this->isOverdue || $this->daysUntilDue <= 3;
    }

    /**
     * Get contribution priority score for sorting
     */
    public function getPriorityScore(): int
    {
        if ($this->isPaid) {
            return 0;
        }

        if ($this->isOverdue) {
            return 1000 + $this->daysSinceDue;
        }

        return 100 - $this->daysUntilDue;
    }
}
