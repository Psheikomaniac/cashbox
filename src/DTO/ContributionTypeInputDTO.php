<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\RecurrencePatternEnum;
use InvalidArgumentException;

/**
 * Modern PHP 8.4 DTO with property hooks for automatic validation
 * Cannot be readonly due to property hooks limitation in PHP 8.4
 */
class ContributionTypeInputDTO
{
    public string $name {
        set => ($trimmed = trim($value)) !== '' && strlen($trimmed) <= 255
            ? $trimmed
            : throw new InvalidArgumentException('Name cannot be empty and must be max 255 characters');
    }
    
    public ?string $description = null {
        set => $value !== null 
            ? (strlen(trim($value)) <= 1000 
                ? trim($value)
                : throw new InvalidArgumentException('Description must be max 1000 characters'))
            : null;
    }
    
    public bool $recurring = false;
    
    public ?RecurrencePatternEnum $recurrencePattern = null {
        set => $this->validateRecurrencePattern($value);
    }

    public function __construct(
        string $name,
        ?string $description = null,
        bool $recurring = false,
        ?RecurrencePatternEnum $recurrencePattern = null
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->recurring = $recurring;
        $this->recurrencePattern = $recurrencePattern;
    }

    /**
     * Create from array with validation
     */
    public static function fromArray(array $data): self
    {
        $recurring = $data['recurring'] ?? false;
        $recurrencePattern = null;
        
        if (isset($data['recurrencePattern'])) {
            $recurrencePattern = RecurrencePatternEnum::from($data['recurrencePattern']);
        }

        return new self(
            name: $data['name'] ?? throw new InvalidArgumentException('Name is required'),
            description: $data['description'] ?? null,
            recurring: $recurring,
            recurrencePattern: $recurrencePattern
        );
    }

    /**
     * Convert to array for serialization
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'recurring' => $this->recurring,
            'recurrencePattern' => $this->recurrencePattern?->value,
        ];
    }

    /**
     * Validate recurrence pattern based on recurring flag
     */
    private function validateRecurrencePattern(?RecurrencePatternEnum $value): ?RecurrencePatternEnum
    {
        if ($this->recurring && $value === null) {
            throw new InvalidArgumentException('Recurrence pattern is required when recurring is true');
        }
        
        if (!$this->recurring && $value !== null) {
            throw new InvalidArgumentException('Recurrence pattern should not be set when recurring is false');
        }
        
        return $value;
    }
}
