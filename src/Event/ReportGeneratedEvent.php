<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Report;

/**
 * Domain event triggered when a report generation is completed.
 */
final readonly class ReportGeneratedEvent
{
    public function __construct(
        private Report $report
    ) {}

    public function getReport(): Report
    {
        return $this->report;
    }

    public function getReportId(): string
    {
        return $this->report->getId()->toString();
    }

    public function getReportType(): string
    {
        return $this->report->getType()->value;
    }

    public function hasResult(): bool
    {
        return $this->report->getResult() !== null;
    }

    public function getResultSize(): int
    {
        $result = $this->report->getResult();
        return $result ? count($result) : 0;
    }
}