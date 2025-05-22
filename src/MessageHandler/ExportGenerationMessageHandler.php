<?php

namespace App\MessageHandler;

use App\Message\ExportGenerationMessage;
use App\Service\ExportGeneratorService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ExportGenerationMessageHandler
{
    private ExportGeneratorService $exportGeneratorService;
    private LoggerInterface $logger;

    public function __construct(
        ExportGeneratorService $exportGeneratorService,
        LoggerInterface $logger
    ) {
        $this->exportGeneratorService = $exportGeneratorService;
        $this->logger = $logger;
    }

    public function __invoke(ExportGenerationMessage $message): void
    {
        $type = $message->getType();
        $format = $message->getFormat();
        $filename = $message->getFilename();
        $reportId = $message->getReportId();
        $filters = $message->getFilters();

        $this->logger->info(sprintf(
            'Generating export of type "%s" in format "%s" with filename "%s"',
            $type,
            $format,
            $filename
        ));

        $filePath = $this->exportGeneratorService->generateExport(
            $type,
            $format,
            $filename,
            $reportId,
            $filters
        );

        if (!$filePath) {
            $this->logger->error(sprintf(
                'Failed to generate export of type "%s" in format "%s" with filename "%s"',
                $type,
                $format,
                $filename
            ));
            return;
        }

        $this->logger->info(sprintf(
            'Successfully generated export at "%s"',
            $filePath
        ));
    }
}
