<?php

namespace App\DTO;

use App\Entity\Report;

readonly class ReportDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $type,
        public array $parameters,
        public ?array $result,
        public string $createdById,
        public bool $scheduled,
        public ?string $cronExpression,
        public string $createdAt,
        public string $updatedAt
    ) {
    }

    public static function createFromEntity(Report $report): self
    {
        return new self(
            id: $report->getId()->toString(),
            name: $report->getName(),
            type: $report->getType()->value,
            parameters: $report->getParameters(),
            result: $report->getResult(),
            createdById: $report->getCreatedBy()->getId()->toString(),
            scheduled: $report->isScheduled(),
            cronExpression: $report->getCronExpression(),
            createdAt: $report->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $report->getUpdatedAt()->format('Y-m-d H:i:s')
        );
    }
}
