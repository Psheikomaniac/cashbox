<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__.'/.env');

// Create the kernel
$kernel = new App\Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();
$container = $kernel->getContainer();

// Get the environment
$environment = $kernel->getEnvironment();
echo "Current environment: $environment\n";

// Get the database URL from the environment
$databaseUrl = $_ENV['DATABASE_URL'] ?? 'Not set';
echo "Database URL from environment: $databaseUrl\n";

// Get the entity manager
$entityManager = $container->get('doctrine.orm.entity_manager');

// Get the connection
$connection = $entityManager->getConnection();
$connectionParams = $connection->getParams();
echo "Connection params:\n";
print_r($connectionParams);

// Check if the payment table exists
$schemaManager = $connection->createSchemaManager();
$tables = $schemaManager->listTableNames();
echo "Tables:\n";
print_r($tables);

// Count the number of payments
$paymentCount = $connection->executeQuery('SELECT COUNT(*) FROM payment')->fetchOne();
echo "Number of payments: $paymentCount\n";
