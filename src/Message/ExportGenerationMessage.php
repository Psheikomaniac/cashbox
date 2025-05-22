<?php

namespace App\Message;

class ExportGenerationMessage
{
    private string $type;
    private string $format;
    private ?string $reportId;
    private ?array $filters;
    private string $filename;

    public function __construct(
        string $type,
        string $format,
        string $filename,
        ?string $reportId = null,
        ?array $filters = null
    ) {
        $this->type = $type;
        $this->format = $format;
        $this->reportId = $reportId;
        $this->filters = $filters;
        $this->filename = $filename;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getReportId(): ?string
    {
        return $this->reportId;
    }

    public function getFilters(): ?array
    {
        return $this->filters;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
