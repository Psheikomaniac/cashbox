<?php

namespace App\MessageHandler;

use App\Message\ReportGenerationMessage;
use App\Service\ReportGeneratorService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ReportGenerationMessageHandler
{
    public function __construct(
        private readonly ReportGeneratorService $reportGeneratorService,
        private readonly LoggerInterface $logger
    ) {
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
