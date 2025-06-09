<?php

namespace App\DTO\Contribution;

use App\Enum\CurrencyEnum;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateContributionDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Team-Benutzer-ID darf nicht leer sein')]
        #[Assert\Uuid(message: 'Ungültige Team-Benutzer-ID')]
        public string $teamUserId,

        #[Assert\NotBlank(message: 'Beitragstyp-ID darf nicht leer sein')]
        #[Assert\Uuid(message: 'Ungültige Beitragstyp-ID')]
        public string $typeId,

        #[Assert\NotBlank(message: 'Beschreibung darf nicht leer sein')]
        #[Assert\Length(
            max: 255,
            maxMessage: 'Beschreibung darf maximal {{ limit }} Zeichen haben'
        )]
        public string $description,

        #[Assert\NotBlank(message: 'Betrag darf nicht leer sein')]
        #[Assert\Positive(message: 'Betrag muss positiv sein')]
        public int $amount,

        #[Assert\NotBlank(message: 'Währung darf nicht leer sein')]
        #[Assert\Choice(
            choices: [CurrencyEnum::EUR->value, CurrencyEnum::USD->value, CurrencyEnum::GBP->value],
            message: 'Ungültige Währung'
        )]
        public string $currency = CurrencyEnum::EUR->value,

        #[Assert\NotBlank(message: 'Fälligkeitsdatum darf nicht leer sein')]
        #[Assert\Date(message: 'Ungültiges Datumsformat')]
        public string $dueDate,

        #[Assert\Type(type: 'boolean', message: 'Bezahlt-Status muss ein Boolean sein')]
        public ?bool $paid = false
    ) {}
}
