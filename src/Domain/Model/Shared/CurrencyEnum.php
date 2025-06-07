<?php

namespace App\Domain\Model\Shared;

enum CurrencyEnum: string
{
    case EUR = 'EUR';
    case USD = 'USD';
    case GBP = 'GBP';
    case CHF = 'CHF';

    /**
     * Formats an amount according to the currency
     */
    public function formatAmount(int $amount): string
    {
        $formattedAmount = number_format($amount / 100, 2, '.', ',');

        return match($this) {
            self::EUR => '€' . $formattedAmount,
            self::USD => '$' . $formattedAmount,
            self::GBP => '£' . $formattedAmount,
            self::CHF => 'CHF ' . $formattedAmount,
        };
    }

    /**
     * Returns the symbol for the currency
     */
    public function getSymbol(): string
    {
        return match($this) {
            self::EUR => '€',
            self::USD => '$',
            self::GBP => '£',
            self::CHF => 'CHF',
        };
    }
}
