<?php

namespace App\DTO;

use App\Entity\Report;

class ReportDTO
{
    public string $id;
    public string $name;
    public string $type;
    public array $parameters;
    public ?array $result;
    public string $createdById;
    public bool $scheduled;
    public ?string $cronExpression;
    public string $createdAt;
    public string $updatedAt;

    public static function createFromEntity(Report $report): self
    {
        $dto = new self();
        $dto->id = $report->getId()->toString();
        $dto->name = $report->getName();
        $dto->type = $report->getType();
        $dto->parameters = $report->getParameters();
        $dto->result = $report->getResult();
        $dto->createdById = $report->getCreatedBy()->getId()->toString();
        $dto->scheduled = $report->isScheduled();
        $dto->cronExpression = $report->getCronExpression();
        $dto->createdAt = $report->getCreatedAt()->format('Y-m-d H:i:s');
        $dto->updatedAt = $report->getUpdatedAt()->format('Y-m-d H:i:s');

        return $dto;
    }
}
