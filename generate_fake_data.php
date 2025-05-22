<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Entity\User;
use App\Entity\Report;
use Symfony\Component\Dotenv\Dotenv;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__.'/.env');

// Override environment variables for testing
$_ENV['DATABASE_URL'] = 'sqlite:///'.__DIR__.'/var/data_test.db';

// Create the kernel
$kernel = new App\Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();
$container = $kernel->getContainer();

// Get the entity manager
$entityManager = $container->get('doctrine.orm.entity_manager');

echo "Generating fake data...\n";

// Generate users
echo "Generating users...\n";
$users = [];
for ($i = 0; $i < 10; $i++) {
    $user = new User();
    $user->setFirstName('User' . $i);
    $user->setLastName('Test');
    $user->setEmail('user' . $i . '@example.com');
    $user->setPhoneNumber('123456789' . $i);
    $user->setActive(true);

    $entityManager->persist($user);
    $users[] = $user;

    echo "Created user: " . $user->getFirstName() . " " . $user->getLastName() . "\n";
}

// Generate reports
echo "Generating reports...\n";
$reportTypes = ['financial', 'penalty', 'team', 'user'];
for ($i = 0; $i < 5; $i++) {
    $report = new Report();
    $report->setName('Report ' . $i);
    $report->setType($reportTypes[array_rand($reportTypes)]);
    $report->setParameters(['param1' => 'value1', 'param2' => 'value2']);
    $report->setCreatedBy($users[array_rand($users)]);

    // Make some reports scheduled
    if ($i % 2 === 0) {
        $report->setScheduled(true);
        $report->setCronExpression('0 0 * * *'); // Daily at midnight
    }

    $entityManager->persist($report);

    echo "Created report: " . $report->getName() . " (Type: " . $report->getType() . ")\n";
}

// Flush all entities to the database
$entityManager->flush();

echo "Fake data generation completed!\n";
