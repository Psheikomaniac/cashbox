<?php

namespace App\Command;

use App\Message\ReportGenerationMessage;
use App\Repository\ReportRepository;
use Cron\CronExpression;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\DependencyInjection\Attribute\AsPublic;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:run-scheduled-reports',
    description: 'Run scheduled reports that are due',
)]
#[AsPublic]
class RunScheduledReportsCommand extends Command
{
    public function __construct(
        private readonly ReportRepository $reportRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command checks for scheduled reports that are due to run and dispatches them to the message bus.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Checking for scheduled reports...');

        $scheduledReports = $this->reportRepository->findScheduled();
        $now = new \DateTimeImmutable();
        $count = 0;

        foreach ($scheduledReports as $report) {
            $cronExpression = $report->getCronExpression();

            if (!$cronExpression) {
                $this->logger->warning(sprintf(
                    'Report "%s" (ID: %s) is marked as scheduled but has no cron expression',
                    $report->getName(),
                    $report->getId()->toString()
                ));
                continue;
            }

            try {
                $cron = new CronExpression($cronExpression);

                // Check if the report is due to run
                if ($cron->isDue($now)) {
                    $output->writeln(sprintf(
                        'Running scheduled report "%s" (ID: %s)',
                        $report->getName(),
                        $report->getId()->toString()
                    ));

                    // Dispatch a message to generate the report
                    $this->messageBus->dispatch(new ReportGenerationMessage(
                        $report->getId()->toString()
                    ));

                    $count++;
                }
            } catch (\Exception $e) {
                $this->logger->error(sprintf(
                    'Error processing scheduled report "%s" (ID: %s): %s',
                    $report->getName(),
                    $report->getId()->toString(),
                    $e->getMessage()
                ));
            }
        }

        $output->writeln(sprintf('Dispatched %d scheduled reports for execution.', $count));

        return Command::SUCCESS;
    }
}
