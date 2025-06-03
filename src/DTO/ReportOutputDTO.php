<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Report;
use App\Enum\ReportTypeEnum;

/**
 * Modern readonly DTO for report responses.
 */
readonly class ReportOutputDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public ReportTypeEnum $type,
        public array $parameters,
        public ?array $result,
        public string $createdBy,
        public bool $scheduled,
        public ?string $cronExpression,
        public string $createdAt,
        public string $updatedAt,
    ) {}
    
    public static function fromEntity(Report $report): self
    {
        return new self(
            id: $report->getId()->toString(),
            name: $report->getName(),
            type: $report->getType(),
            parameters: $report->getParameters(),
            result: $report->getResult(),
            createdBy: $report->getCreatedBy()->getId()->toString(),
            scheduled: $report->isScheduled(),
            cronExpression: $report->getCronExpression(),
            createdAt: $report->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $report->getUpdatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
