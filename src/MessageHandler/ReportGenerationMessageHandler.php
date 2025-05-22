<?php

namespace App\MessageHandler;

use App\Message\ReportGenerationMessage;
use App\Service\ReportGeneratorService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ReportGenerationMessageHandler
{
    private ReportGeneratorService $reportGeneratorService;
    private LoggerInterface $logger;

    public function __construct(
        ReportGeneratorService $reportGeneratorService,
        LoggerInterface $logger
    ) {
        $this->reportGeneratorService = $reportGeneratorService;
        $this->logger = $logger;
    }

    public function __invoke(ReportGenerationMessage $message): void
    {
        $reportId = $message->getReportId();
        $parameters = $message->getParameters();

        $this->logger->info(sprintf('Generating report with ID "%s"', $reportId));

        $report = $this->reportGeneratorService->generateReport($reportId, $parameters);

        if (!$report) {
            $this->logger->error(sprintf('Failed to generate report with ID "%s"', $reportId));
            return;
        }

        $this->logger->info(sprintf('Successfully generated report with ID "%s"', $reportId));
    }
}
