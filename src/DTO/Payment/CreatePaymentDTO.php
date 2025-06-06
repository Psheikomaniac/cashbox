<?php

namespace App\DTO\Payment;

use App\Enum\CurrencyEnum;
use App\Enum\PaymentTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreatePaymentDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Team-Benutzer-ID darf nicht leer sein')]
        #[Assert\Uuid(message: 'Ungültige Team-Benutzer-ID')]
        public string $teamUserId,

        #[Assert\NotBlank(message: 'Betrag darf nicht leer sein')]
        #[Assert\PositiveOrZero(message: 'Betrag muss positiv oder Null sein')]
        public int $amount,

        #[Assert\NotBlank(message: 'Währung darf nicht leer sein')]
        #[Assert\Choice(
            choices: [CurrencyEnum::EUR->value, CurrencyEnum::USD->value, CurrencyEnum::GBP->value],
            message: 'Ungültige Währung'
        )]
        public string $currency = CurrencyEnum::EUR->value,

        #[Assert\NotBlank(message: 'Zahlungstyp darf nicht leer sein')]
        #[Assert\Choice(
            choices: [
                PaymentTypeEnum::CASH->value,
                PaymentTypeEnum::BANK_TRANSFER->value,
                PaymentTypeEnum::CREDIT_CARD->value,
                PaymentTypeEnum::MOBILE_PAYMENT->value
            ],
            message: 'Ungültiger Zahlungstyp'
        )]
        public string $type = PaymentTypeEnum::CASH->value,

        #[Assert\Length(
            max: 255,
            maxMessage: 'Beschreibung darf maximal {{ limit }} Zeichen haben'
        )]
        public ?string $description = null,

        #[Assert\Length(
            max: 255,
            maxMessage: 'Referenz darf maximal {{ limit }} Zeichen haben'
        )]
        public ?string $reference = null
    ) {}
}
