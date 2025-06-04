<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\CurrencyEnum;
use App\Enum\ValidationRuleEnum;
use App\ValueObject\Money;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Modern PHP 8.4 DTO with property hooks for automatic validation
 * Cannot be readonly due to property hooks limitation in PHP 8.4
 */
class ContributionInputDTO
{
    public string $teamUserId {
        set => ValidationRuleEnum::UUID->validate($value)
            ? trim($value)
            : throw new InvalidArgumentException('Invalid team user ID format');
    }
    
    public string $typeId {
        set => ValidationRuleEnum::UUID->validate($value)
            ? trim($value)
            : throw new InvalidArgumentException('Invalid contribution type ID format');
    }
    
    public string $description {
        set => ($trimmed = trim($value)) !== '' && strlen($trimmed) <= 255
            ? $trimmed
            : throw new InvalidArgumentException('Description cannot be empty and must be max 255 characters');
    }
    
    public Money $amount {
        set => $value->getAmount() > 0
            ? $value
            : throw new InvalidArgumentException('Amount must be positive');
    }
    
    public string $dueDate {
        set => $this->validateDateString($value);
    }
    
    public ?string $paidAt = null {
        set => $value !== null 
            ? $this->validateDateString($value)
            : null;
    }

    public function __construct(
        string $teamUserId,
        string $typeId,
        string $description,
        Money $amount,
        string $dueDate,
        ?string $paidAt = null
    ) {
        $this->teamUserId = $teamUserId;
        $this->typeId = $typeId;
        $this->description = $description;
        $this->amount = $amount;
        $this->dueDate = $dueDate;
        $this->paidAt = $paidAt;
    }

    /**
     * Create from array with validation
     */
    public static function fromArray(array $data): self
    {
        // Extract and validate amount
        if (!isset($data['amount']) || !is_array($data['amount'])) {
            throw new InvalidArgumentException('Amount data is required and must be an array');
        }

        $amountData = $data['amount'];
        if (!isset($amountData['value'], $amountData['currency'])) {
            throw new InvalidArgumentException('Amount must have value and currency');
        }

        // Convert float value to cents (int) for Money value object
        $cents = (int) round((float) $amountData['value'] * 100);
        $currency = CurrencyEnum::from($amountData['currency']);
        $amount = new Money($cents, $currency);

        return new self(
            teamUserId: $data['teamUserId'] ?? throw new InvalidArgumentException('Team user ID is required'),
            typeId: $data['typeId'] ?? throw new InvalidArgumentException('Type ID is required'),
            description: $data['description'] ?? throw new InvalidArgumentException('Description is required'),
            amount: $amount,
            dueDate: $data['dueDate'] ?? throw new InvalidArgumentException('Due date is required'),
            paidAt: $data['paidAt'] ?? null
        );
    }

    /**
     * Validate date string format
     */
    private function validateDateString(string $date): string
    {
        $parsedDate = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if ($parsedDate === false || $parsedDate->format('Y-m-d') !== $date) {
            throw new InvalidArgumentException('Invalid date format. Expected Y-m-d format');
        }
        
        return $date;
    }

    /**
     * Convert to array for serialization
     */
    public function toArray(): array
    {
        return [
            'teamUserId' => $this->teamUserId,
            'typeId' => $this->typeId,
            'description' => $this->description,
            'amount' => [
                'value' => $this->amount->getAmount() / 100, // Convert cents back to float
                'currency' => $this->amount->getCurrency()->value,
            ],
            'dueDate' => $this->dueDate,
            'paidAt' => $this->paidAt,
        ];
    }
}
