<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use App\Message\NotificationMessage;
use App\Message\ReportGenerationMessage;
use App\Message\ExportGenerationMessage;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Messenger\MessageBusInterface;

// Load environment variables from .env.local
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__.'/.env');

// Override environment variables for testing
$_ENV['MESSENGER_TRANSPORT_DSN'] = 'sync://';
$_ENV['DATABASE_URL'] = 'sqlite:///'.__DIR__.'/var/data_test.db';

// Function to get a random entity ID from the database
function getRandomEntityId($entityManager, $entityClass) {
    $repository = $entityManager->getRepository($entityClass);
    $entities = $repository->findAll();

    if (empty($entities)) {
        throw new \Exception("No entities found for class $entityClass");
    }

    $randomEntity = $entities[array_rand($entities)];
    return $randomEntity->getId()->toString();
}

// Create the kernel
$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();
$container = $kernel->getContainer();

// Get the message bus service
$messageBusService = $container->get('App\Service\MessageBusService');
$messageBus = $messageBusService->getMessageBus();

// Get the entity manager
$entityManager = $container->get('doctrine.orm.entity_manager');

// Get random user and report IDs for testing
$randomUserId = getRandomEntityId($entityManager, 'App\Entity\User');
$randomReportId = getRandomEntityId($entityManager, 'App\Entity\Report');

// Create a console output
$output = new ConsoleOutput();
$io = new SymfonyStyle(new ArrayInput([]), $output);

// Test notification message
$io->section('Testing notification message');
try {
    $io->note("Using user ID: $randomUserId");
    $messageBus->dispatch(new NotificationMessage(
        $randomUserId,
        'test',
        'Test Notification',
        'This is a test notification'
    ));
    $io->success('Notification message dispatched successfully');
} catch (\Exception $e) {
    $io->error('Error dispatching notification message: ' . $e->getMessage());
}

// Test report generation message
$io->section('Testing report generation message');
try {
    $io->note("Using report ID: $randomReportId");
    $messageBus->dispatch(new ReportGenerationMessage(
        $randomReportId
    ));
    $io->success('Report generation message dispatched successfully');
} catch (\Exception $e) {
    $io->error('Error dispatching report generation message: ' . $e->getMessage());
}

// Test export generation message
$io->section('Testing export generation message');
try {
    $messageBus->dispatch(new ExportGenerationMessage(
        'test',
        'pdf',
        'test-' . uniqid() . '.pdf'
    ));
    $io->success('Export generation message dispatched successfully');
} catch (\Exception $e) {
    $io->error('Error dispatching export generation message: ' . $e->getMessage());
}

$io->section('Testing scheduled reports command');
try {
    $command = $container->get('App\Command\RunScheduledReportsCommand');
    $command->run(new ArrayInput([]), $output);
    $io->success('Scheduled reports command executed successfully');
} catch (\Exception $e) {
    $io->error('Error executing scheduled reports command: ' . $e->getMessage());
}

$io->success('All tests completed');
