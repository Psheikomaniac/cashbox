<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\RecurringContributionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:process-recurring-contributions',
    description: 'Process recurring contributions and create new ones based on schedule',
)]
class ProcessRecurringContributionsCommand extends Command
{
    public function __construct(
        private readonly RecurringContributionService $recurringService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Process only specific recurrence type (monthly, weekly, etc.)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be processed without making changes')
            ->setHelp('This command processes recurring contributions and creates new contributions based on their schedule.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $isDryRun = $input->getOption('dry-run');
        $type = $input->getOption('type');

        $io->title('Processing Recurring Contributions');

        if ($isDryRun) {
            $io->note('Running in DRY-RUN mode - no changes will be made');
        }

        try {
            $processedCount = match($type) {
                'monthly' => $this->recurringService->processMonthlyContributions(),
                null => $this->recurringService->processRecurringContributions(),
                default => throw new \InvalidArgumentException("Unsupported recurrence type: {$type}")
            };

            if ($processedCount > 0) {
                $io->success(sprintf('Successfully processed %d recurring contributions', $processedCount));
            } else {
                $io->info('No recurring contributions needed processing at this time');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error(sprintf('Error processing recurring contributions: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}