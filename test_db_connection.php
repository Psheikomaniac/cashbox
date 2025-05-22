<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__.'/.env');

// Ensure we're using PostgreSQL by explicitly setting the DATABASE_URL
// This prevents .env.local from overriding with SQLite
$_ENV['DATABASE_URL'] = "postgresql://app:!ChangeMe!@database:5432/app?serverVersion=16&charset=utf8";
$_SERVER['DATABASE_URL'] = $_ENV['DATABASE_URL'];

echo "Connecting to database: " . $_ENV['DATABASE_URL'] . "\n";

try {
    // Create the kernel
    $kernel = new App\Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
    $kernel->boot();
    $container = $kernel->getContainer();

    // Get the entity manager
    $entityManager = $container->get('doctrine.orm.entity_manager');

    // Get the connection
    $connection = $entityManager->getConnection();

    // Test the connection
    $result = $connection->executeQuery('SELECT 1')->fetchOne();
    echo "Connection successful! Result: $result\n";

    // Get the database platform
    $platform = $connection->getDatabasePlatform();
    echo "Database platform: " . get_class($platform) . "\n";

    // List tables
    $schemaManager = $connection->createSchemaManager();
    $tables = $schemaManager->listTableNames();
    echo "Tables:\n";
    print_r($tables);

    // Count records in payment table
    $paymentCount = $connection->executeQuery('SELECT COUNT(*) FROM payment')->fetchOne();
    echo "Number of payments: $paymentCount\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
