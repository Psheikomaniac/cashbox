<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Report;

/**
 * Domain event triggered when a new report is created.
 */
final readonly class ReportCreatedEvent
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

    public function getCreatedBy(): string
    {
        return $this->report->getCreatedBy()->getId()->toString();
    }

    public function isScheduled(): bool
    {
        return $this->report->isScheduled();
    }
}