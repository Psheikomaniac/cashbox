<?php

namespace App\Enum;

enum PenaltyTypeEnum: string
{
    case DRINK = 'drink';
    case LATE_ARRIVAL = 'late_arrival';
    case MISSED_TRAINING = 'missed_training';
    case CUSTOM = 'custom';

    public function getLabel(): string
    {
        return match($this) {
            self::DRINK => 'Drink',
            self::LATE_ARRIVAL => 'Late Arrival',
            self::MISSED_TRAINING => 'Missed Training',
            self::CUSTOM => 'Custom',
        };
    }

    public function isDrink(): bool
    {
        return $this === self::DRINK;
    }

    public function getDefaultAmount(): int
    {
        return match($this) {
            self::DRINK => 150, // 1.50 EUR in cents
            self::LATE_ARRIVAL => 500,
            self::MISSED_TRAINING => 1500,
            self::CUSTOM => 0,
        };
    }
}
