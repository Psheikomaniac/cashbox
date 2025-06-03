<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\ReportTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Modern readonly DTO for report creation and updates.
 */
readonly class ReportInputDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,
        
        #[Assert\NotNull]
        public ReportTypeEnum $type,
        
        #[Assert\Type('array')]
        public array $parameters = [],
        
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $createdById,
        
        public bool $scheduled = false,
        
        #[Assert\When(
            expression: 'this.scheduled === true',
            constraints: [new Assert\NotBlank()]
        )]
        public ?string $cronExpression = null,
    ) {}
}
