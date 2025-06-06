<?php

namespace App\Command;

use App\Service\TestDataGeneratorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Kommando zum Generieren von Testdaten für die Entwicklungsumgebung.
 */
#[AsCommand(
    name: 'app:generate-test-data',
    description: 'Generiert Testdaten für die Entwicklungsumgebung',
)]
class GenerateTestDataCommand extends Command
{
    public function __construct(
        private readonly TestDataGeneratorService $testDataGenerator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'amount',
            'a',
            InputOption::VALUE_OPTIONAL,
            'Menge der zu generierenden Daten (small, medium, large)',
            'medium'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $amount = $input->getOption('amount');

        $io->title('Testdaten-Generator');
        $io->text("Generiere {$amount} Testdaten...");

        try {
            $result = $this->testDataGenerator->generate($amount);

            $io->success(sprintf(
                "Erfolgreich generiert: %d Benutzer, %d Teams, %d Strafen, %d Zahlungen",
                $result['users'],
                $result['teams'],
                $result['penalties'],
                $result['payments']
            ));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error("Fehler beim Generieren der Testdaten: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
