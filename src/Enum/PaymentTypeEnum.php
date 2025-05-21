<?php

namespace App\Enum;

enum PaymentTypeEnum: string
{
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
    case CREDIT_CARD = 'credit_card';
    case MOBILE_PAYMENT = 'mobile_payment';

    public function getLabel(): string
    {
        return match($this) {
            self::CASH => 'Cash',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CREDIT_CARD => 'Credit Card',
            self::MOBILE_PAYMENT => 'Mobile Payment',
        };
    }

    public function requiresReference(): bool
    {
        return match($this) {
            self::CASH => false,
            self::BANK_TRANSFER, self::CREDIT_CARD, self::MOBILE_PAYMENT => true,
        };
    }
}
