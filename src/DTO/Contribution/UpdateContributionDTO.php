<?php

namespace App\DTO\Contribution;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateContributionDTO
{
    public function __construct(
        #[Assert\Date(message: 'Ungültiges Datumsformat')]
        public ?string $dueDate = null,

        #[Assert\Type(type: 'boolean', message: 'Bezahlt-Status muss ein Boolean sein')]
        public ?bool $paid = null,

        #[Assert\Type(type: 'boolean', message: 'Aktiv-Status muss ein Boolean sein')]
        public ?bool $active = null
    ) {}
}
