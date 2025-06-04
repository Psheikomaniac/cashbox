<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\ContributionType;
use App\Enum\RecurrencePatternEnum;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Modern PHP 8.4 readonly output DTO with enhanced business logic
 */
final readonly class ContributionTypeOutputDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description,
        public bool $recurring,
        public ?RecurrencePatternEnum $recurrencePattern,
        public ?float $estimatedFrequencyPerYear,
        public bool $active,
        public string $createdAt,
        public string $updatedAt,
        public array $metadata = []
    ) {}

    /**
     * Create from ContributionType entity following documentation standards
     */
    public static function fromEntity(ContributionType $type): self
    {
        return new self(
            id: $type->getId()->toString(),
            name: $type->getName(),
            description: $type->getDescription(),
            recurring: $type->isRecurring(),
            recurrencePattern: $type->getRecurrencePattern(),
            estimatedFrequencyPerYear: $type->getRecurrencePattern()?->getFrequencyPerYear(),
            active: $type->isActive(),
            createdAt: $type->getCreatedAt()->format(DateTimeInterface::ATOM),
            updatedAt: $type->getUpdatedAt()->format(DateTimeInterface::ATOM),
            metadata: [
                'category' => self::determineCategory($type),
                'usageFrequency' => self::getUsageFrequency($type),
                'isSystemType' => self::isSystemType($type),
                'nextDueEstimate' => self::getNextDueEstimate($type),
                'displayName' => self::getDisplayName($type),
                'recurrenceDescription' => $type->getRecurrencePattern()?->getLabel() ?? 'One-time',
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
            'name' => $this->name,
            'description' => $this->description,
            'recurring' => $this->recurring,
            'recurrencePattern' => $this->recurrencePattern?->value,
            'estimatedFrequencyPerYear' => $this->estimatedFrequencyPerYear,
            'active' => $this->active,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Determine category based on name and pattern
     */
    private static function determineCategory(ContributionType $type): string
    {
        $name = strtolower($type->getName());
        
        return match (true) {
            str_contains($name, 'membership') || str_contains($name, 'dues') => 'membership',
            str_contains($name, 'event') || str_contains($name, 'activity') => 'event',
            str_contains($name, 'donation') || str_contains($name, 'charity') => 'donation',
            str_contains($name, 'fee') || str_contains($name, 'fine') => 'fee',
            $type->isRecurring() => 'recurring',
            default => 'general'
        };
    }

    /**
     * Get usage frequency description
     */
    private static function getUsageFrequency(ContributionType $type): string
    {
        if (!$type->isRecurring()) {
            return 'as-needed';
        }

        return match ($type->getRecurrencePattern()) {
            RecurrencePatternEnum::WEEKLY => 'weekly',
            RecurrencePatternEnum::BIWEEKLY => 'biweekly',
            RecurrencePatternEnum::MONTHLY => 'monthly',
            RecurrencePatternEnum::QUARTERLY => 'quarterly',
            RecurrencePatternEnum::SEMIANNUALLY => 'semiannually',
            RecurrencePatternEnum::ANNUALLY => 'annually',
            default => 'variable'
        };
    }

    /**
     * Check if this is a system-managed type
     */
    private static function isSystemType(ContributionType $type): bool
    {
        $systemNames = ['membership', 'annual fee', 'registration'];
        $name = strtolower($type->getName());
        
        foreach ($systemNames as $systemName) {
            if (str_contains($name, $systemName)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get estimated next due date
     */
    private static function getNextDueEstimate(ContributionType $type): ?string
    {
        if (!$type->isRecurring() || !$type->getRecurrencePattern()) {
            return null;
        }

        $now = new DateTimeImmutable();
        
        return match ($type->getRecurrencePattern()) {
            RecurrencePatternEnum::WEEKLY => $now->modify('+1 week')->format('Y-m-d'),
            RecurrencePatternEnum::BIWEEKLY => $now->modify('+2 weeks')->format('Y-m-d'),
            RecurrencePatternEnum::MONTHLY => $now->modify('+1 month')->format('Y-m-d'),
            RecurrencePatternEnum::QUARTERLY => $now->modify('+3 months')->format('Y-m-d'),
            RecurrencePatternEnum::SEMIANNUALLY => $now->modify('+6 months')->format('Y-m-d'),
            RecurrencePatternEnum::ANNUALLY => $now->modify('+1 year')->format('Y-m-d'),
        };
    }

    /**
     * Get display-friendly name
     */
    private static function getDisplayName(ContributionType $type): string
    {
        $name = $type->getName();
        
        if ($type->isRecurring() && $type->getRecurrencePattern()) {
            $pattern = match ($type->getRecurrencePattern()) {
                RecurrencePatternEnum::WEEKLY => 'Weekly',
                RecurrencePatternEnum::BIWEEKLY => 'Bi-weekly',
                RecurrencePatternEnum::MONTHLY => 'Monthly',
                RecurrencePatternEnum::QUARTERLY => 'Quarterly',
                RecurrencePatternEnum::SEMIANNUALLY => 'Semi-annually',
                RecurrencePatternEnum::ANNUALLY => 'Annual',
            };
            
            return "$name ($pattern)";
        }
        
        return $name;
    }

    /**
     * Check if type is suitable for automation
     */
    public function isAutomatable(): bool
    {
        return $this->recurring && $this->active;
    }

    /**
     * Get priority score for sorting
     */
    public function getPriorityScore(): int
    {
        $score = 0;
        
        if (!$this->active) {
            return -100;
        }
        
        if ($this->recurring) {
            $score += 50;
        }
        
        if ($this->metadata['isSystemType'] ?? false) {
            $score += 30;
        }
        
        return $score;
    }
}
