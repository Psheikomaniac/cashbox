<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use App\Message\NotificationMessage;
use App\Message\ReportGenerationMessage;
use App\Message\ExportGenerationMessage;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

// Create the kernel
$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();
$container = $kernel->getContainer();

// Get the message bus
$messageBus = $container->get(MessageBusInterface::class);

// Create a console output
$output = new ConsoleOutput();
$io = new SymfonyStyle(new ArrayInput([]), $output);

// Test notification message
$io->section('Testing notification message');
try {
    $messageBus->dispatch(new NotificationMessage(
        '00000000-0000-0000-0000-000000000001', // Replace with a valid user ID
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
    $messageBus->dispatch(new ReportGenerationMessage(
        '00000000-0000-0000-0000-000000000001' // Replace with a valid report ID
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
