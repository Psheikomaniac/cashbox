<?php

namespace App\Enum;

enum RecurrencePatternEnum: string
{
    case WEEKLY = 'weekly';
    case BIWEEKLY = 'biweekly';
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case SEMIANNUALLY = 'semiannually';
    case ANNUALLY = 'annually';
    
    public function getLabel(): string
    {
        return match($this) {
            self::WEEKLY => 'Weekly',
            self::BIWEEKLY => 'Bi-weekly',
            self::MONTHLY => 'Monthly',
            self::QUARTERLY => 'Quarterly',
            self::SEMIANNUALLY => 'Semi-annually',
            self::ANNUALLY => 'Annually',
        };
    }
    
    public function getIntervalDays(): int
    {
        return match($this) {
            self::WEEKLY => 7,
            self::BIWEEKLY => 14,
            self::MONTHLY => 30,
            self::QUARTERLY => 90,
            self::SEMIANNUALLY => 180,
            self::ANNUALLY => 365,
        };
    }
    
    public function calculateNextDate(\DateTimeImmutable $baseDate): \DateTimeImmutable
    {
        return match($this) {
            self::WEEKLY => $baseDate->modify('+1 week'),
            self::BIWEEKLY => $baseDate->modify('+2 weeks'),
            self::MONTHLY => $baseDate->modify('+1 month'),
            self::QUARTERLY => $baseDate->modify('+3 months'),
            self::SEMIANNUALLY => $baseDate->modify('+6 months'),
            self::ANNUALLY => $baseDate->modify('+1 year'),
        };
    }
    
    public function getFrequencyPerYear(): float
    {
        return match($this) {
            self::WEEKLY => 52.0,
            self::BIWEEKLY => 26.0,
            self::MONTHLY => 12.0,
            self::QUARTERLY => 4.0,
            self::SEMIANNUALLY => 2.0,
            self::ANNUALLY => 1.0,
        };
    }
}