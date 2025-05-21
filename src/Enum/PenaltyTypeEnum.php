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
}
