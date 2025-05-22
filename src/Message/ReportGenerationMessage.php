<?php

namespace App\Message;

class ReportGenerationMessage
{
    private string $reportId;
    private ?array $parameters;

    public function __construct(
        string $reportId,
        ?array $parameters = null
    ) {
        $this->reportId = $reportId;
        $this->parameters = $parameters;
    }

    public function getReportId(): string
    {
        return $this->reportId;
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }
}
