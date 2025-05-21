<?php

require_once __DIR__ . '/vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Symfony\Component\Dotenv\Dotenv;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

// Create a connection to the database
$connectionParams = [
    'dbname' => $_ENV['DATABASE_NAME'] ?? 'app',
    'user' => $_ENV['DATABASE_USER'] ?? 'app',
    'password' => $_ENV['DATABASE_PASSWORD'] ?? '!ChangeMe!',
    'host' => $_ENV['DATABASE_HOST'] ?? 'localhost',
    'driver' => 'pdo_pgsql',
];

try {
    $conn = DriverManager::getConnection($connectionParams);

    // Check if the external_id column in the team table is nullable
    $sql = "SELECT column_name, is_nullable
            FROM information_schema.columns
            WHERE table_name = 'team' AND column_name = 'external_id'";

    $stmt = $conn->executeQuery($sql);
    $result = $stmt->fetchAssociative();

    if ($result) {
        echo "Column: " . $result['column_name'] . "\n";
        echo "Is Nullable: " . $result['is_nullable'] . "\n";
    } else {
        echo "Column 'external_id' not found in table 'team'\n";
    }

    // Also check the migration status
    $sql = "SELECT version FROM doctrine_migration_versions WHERE version = 'DoctrineMigrations\\\\Version20250521'";
    $stmt = $conn->executeQuery($sql);
    $migrationResult = $stmt->fetchAssociative();

    if ($migrationResult) {
        echo "Migration Version20250521 has been applied\n";
    } else {
        echo "Migration Version20250521 has NOT been applied\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
