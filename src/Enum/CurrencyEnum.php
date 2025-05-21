<?php

namespace App\Enum;

enum CurrencyEnum: string
{
    case EUR = 'EUR';
    case USD = 'USD';
    case GBP = 'GBP';

    public function getSymbol(): string
    {
        return match($this) {
            self::EUR => '€',
            self::USD => '$',
            self::GBP => '£',
        };
    }

    public function formatAmount(int $amount): string
    {
        $formattedAmount = number_format($amount / 100, 2);

        return match($this) {
            self::EUR => $formattedAmount . ' ' . $this->getSymbol(),
            self::USD, self::GBP => $this->getSymbol() . $formattedAmount,
        };
    }
}
