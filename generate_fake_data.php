<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Entity\User;
use App\Entity\Team;
use App\Entity\TeamUser;
use App\Entity\Report;
use App\Entity\Notification;
use App\Entity\NotificationPreference;
use App\Entity\Payment;
use App\Entity\Penalty;
use App\Entity\PenaltyType;
use App\Enum\UserRoleEnum;
use App\Enum\CurrencyEnum;
use App\Enum\PaymentTypeEnum;
use App\Enum\PenaltyTypeEnum;
use App\ValueObject\PersonName;
use App\ValueObject\Email;
use App\ValueObject\PhoneNumber;
use Faker\Factory;
use Symfony\Component\Dotenv\Dotenv;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__.'/.env');

// Load .env.local for local PostgreSQL configuration
if (file_exists(__DIR__.'/.env.local')) {
    $dotenv->loadEnv(__DIR__.'/.env.local');
}

// Create the kernel
$kernel = new App\Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();
$container = $kernel->getContainer();

// Get the entity manager
$entityManager = $container->get('doctrine.orm.entity_manager');

// Initialize Faker
$faker = Factory::create();

// Configuration - Generate very large amounts of test data
$numUsers = 200;
$numTeams = 30;
$numTeamUsers = 500; // Users in teams (with roles)
$numReports = 100;
$numNotifications = 500;
$numNotificationPreferences = 200;
$numPenaltyTypes = 20;
$numPenalties = 500;
$numPayments = 500;

echo "Generating fake data...\n";
echo "This will create:\n";
echo "- $numUsers Users\n";
echo "- $numTeams Teams\n";
echo "- $numTeamUsers TeamUsers\n";
echo "- $numReports Reports\n";
echo "- $numNotifications Notifications\n";
echo "- $numNotificationPreferences NotificationPreferences\n";
echo "- $numPenaltyTypes PenaltyTypes\n";
echo "- $numPenalties Penalties\n";
echo "- $numPayments Payments\n";
echo "\n";

// Clear existing data (optional)
echo "Clearing existing data...\n";
$connection = $entityManager->getConnection();
$platform = $connection->getDatabasePlatform();
$isSqlite = $connection->getDriver() instanceof \Doctrine\DBAL\Driver\PDO\SQLite\Driver;

// Tables in order of dependency (to avoid foreign key issues)
$tables = [
    'payment',
    'penalty',
    'penalty_type',
    'notification_preference',
    'notification',
    'report',
    'team_user',
    'team',
    'user'
];

// For SQLite, we need to use a different approach
if ($isSqlite) {
    // Enable foreign keys for SQLite (they're disabled by default)
    $connection->executeStatement('PRAGMA foreign_keys = OFF');

    // Delete from tables
    foreach ($tables as $table) {
        try {
            $connection->executeStatement('DELETE FROM ' . $table);
            echo "Cleared table: $table\n";
        } catch (\Exception $e) {
            echo "Error clearing table $table: " . $e->getMessage() . "\n";
        }
    }

    // Re-enable foreign keys
    $connection->executeStatement('PRAGMA foreign_keys = ON');
} else {
    // For MySQL/MariaDB
    try {
        // Disable foreign key checks
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        // Truncate tables
        foreach ($tables as $table) {
            try {
                $connection->executeStatement('TRUNCATE TABLE `' . $table . '`');
                echo "Cleared table: $table\n";
            } catch (\Exception $e) {
                echo "Error clearing table $table: " . $e->getMessage() . "\n";
            }
        }

        // Re-enable foreign key checks
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    } catch (\Exception $e) {
        echo "Error managing foreign key checks: " . $e->getMessage() . "\n";
        echo "Attempting to clear tables without disabling foreign keys...\n";

        // If we can't manage foreign keys, try to delete from tables in reverse order
        foreach (array_reverse($tables) as $table) {
            try {
                $connection->executeStatement('DELETE FROM `' . $table . '`');
                echo "Cleared table: $table\n";
            } catch (\Exception $e) {
                echo "Error clearing table $table: " . $e->getMessage() . "\n";
            }
        }
    }
}

// Generate users
echo "Generating users...\n";
$users = [];
for ($i = 0; $i < $numUsers; $i++) {
    $firstName = $faker->firstName();
    $lastName = $faker->lastName();
    $email = $faker->unique()->safeEmail();
    $phoneNumber = $faker->phoneNumber();
    
    $personName = new PersonName($firstName, $lastName);
    $emailVo = new Email($email);
    $phoneVo = new PhoneNumber($phoneNumber);
    
    $user = new User($personName, $emailVo, $phoneVo);
    $user->setActive($faker->boolean(90)); // 90% active

    $entityManager->persist($user);
    $users[] = $user;

    if ($i % 10 === 0) {
        echo "Created " . ($i + 1) . " users...\n";
    }
}

// Generate teams
echo "Generating teams...\n";
$teams = [];
for ($i = 0; $i < $numTeams; $i++) {
    $teamName = $faker->company();
    $externalId = 'team_' . $faker->unique()->randomNumber(6);
    $team = Team::create($teamName, $externalId);
    // Team is created with name and externalId, setActive if there's a method for it

    $entityManager->persist($team);
    $teams[] = $team;

    echo "Created team: " . $team->getName() . "\n";
}

// Generate team users (users in teams with roles)
echo "Generating team users...\n";
$teamUsers = [];
$roles = [
    UserRoleEnum::ADMIN,
    UserRoleEnum::MANAGER,
    UserRoleEnum::TREASURER,
    UserRoleEnum::MEMBER
];

for ($i = 0; $i < $numTeamUsers; $i++) {
    $team = $teams[array_rand($teams)];
    $user = $users[array_rand($users)];
    
    // Assign 1-3 roles
    $numRoles = $faker->numberBetween(1, 3);
    $selectedRoles = $faker->randomElements($roles, $numRoles);
    
    $teamUser = new TeamUser($team, $user, $selectedRoles);
    // Note: TeamUser is created with active=true by default

    $entityManager->persist($teamUser);
    $teamUsers[] = $teamUser;

    if ($i % 10 === 0) {
        echo "Created " . ($i + 1) . " team users...\n";
    }
}

// Generate reports
echo "Generating reports...\n";
$reportTypes = ['financial', 'penalty', 'team', 'user', 'activity', 'summary'];
for ($i = 0; $i < $numReports; $i++) {
    $report = new Report();
    $report->setName($faker->sentence(3));
    $report->setType($reportTypes[array_rand($reportTypes)]);

    // Generate random parameters
    $paramCount = $faker->numberBetween(1, 5);
    $params = [];
    for ($p = 0; $p < $paramCount; $p++) {
        $params[$faker->word()] = $faker->word();
    }
    $report->setParameters($params);

    // Set random result for some reports
    if ($faker->boolean(70)) {
        $resultCount = $faker->numberBetween(1, 10);
        $results = [];
        for ($r = 0; $r < $resultCount; $r++) {
            $results[] = [
                'id' => $faker->uuid(),
                'name' => $faker->word(),
                'value' => $faker->randomNumber(3),
                'date' => $faker->date()
            ];
        }
        $report->setResult($results);
    }

    $report->setCreatedBy($users[array_rand($users)]);

    // Make some reports scheduled
    if ($faker->boolean(30)) {
        $report->setScheduled(true);

        // Random cron expressions
        $cronExpressions = [
            '0 0 * * *',     // Daily at midnight
            '0 12 * * *',    // Daily at noon
            '0 0 * * 0',     // Weekly on Sunday
            '0 0 1 * *',     // Monthly on the 1st
            '0 0 15 * *',    // Monthly on the 15th
            '0 0 1 1 *'      // Yearly on Jan 1
        ];
        $report->setCronExpression($cronExpressions[array_rand($cronExpressions)]);
    }

    $entityManager->persist($report);

    if ($i % 10 === 0) {
        echo "Created " . ($i + 1) . " reports...\n";
    }
}

// Generate notifications
echo "Generating notifications...\n";
$notificationTypes = ['system', 'payment', 'penalty', 'report', 'team', 'user'];
for ($i = 0; $i < $numNotifications; $i++) {
    $notification = new Notification();
    $notification->setUser($users[array_rand($users)]);
    $notification->setType($notificationTypes[array_rand($notificationTypes)]);
    $notification->setTitle($faker->sentence());
    $notification->setMessage($faker->paragraph());

    // Add data to some notifications
    if ($faker->boolean(50)) {
        $notification->setData([
            'entityId' => $faker->uuid(),
            'entityType' => $faker->randomElement(['user', 'team', 'payment', 'penalty']),
            'action' => $faker->randomElement(['created', 'updated', 'deleted']),
        ]);
    }

    // Mark some as read
    if ($faker->boolean(30)) {
        $notification->setRead(true);
        $notification->setReadAt(new \DateTimeImmutable($faker->dateTimeThisYear()->format('Y-m-d H:i:s')));
    }

    $entityManager->persist($notification);

    if ($i % 20 === 0) {
        echo "Created " . ($i + 1) . " notifications...\n";
    }
}

// Generate notification preferences
echo "Generating notification preferences...\n";
for ($i = 0; $i < $numNotificationPreferences; $i++) {
    $preference = new NotificationPreference();
    $preference->setUser($users[array_rand($users)]);
    $preference->setNotificationType($notificationTypes[array_rand($notificationTypes)]);
    $preference->setEmailEnabled($faker->boolean(70));
    $preference->setInAppEnabled($faker->boolean(90));

    $entityManager->persist($preference);

    if ($i % 10 === 0) {
        echo "Created " . ($i + 1) . " notification preferences...\n";
    }
}

// Generate penalty types
echo "Generating penalty types...\n";
$penaltyTypeNames = [
    'Late to practice',
    'Missed game',
    'Forgot equipment',
    'Yellow card',
    'Red card',
    'Missed team meeting',
    'Late payment',
    'Inappropriate behavior',
    'Dress code violation',
    'Phone ringing during meeting',
    'Social media violation',
    'Missing team event',
    'Not following team rules',
    'Beer for the team',
    'Drinks for everyone'
];

$penaltyTypes = [];
for ($i = 0; $i < $numPenaltyTypes; $i++) {
    $penaltyType = new PenaltyType();

    // Use predefined names if available, otherwise generate random ones
    if ($i < count($penaltyTypeNames)) {
        $penaltyType->setName($penaltyTypeNames[$i]);
    } else {
        $penaltyType->setName($faker->sentence(2));
    }

    $penaltyType->setDescription($faker->paragraph());

    // Set type based on name for more realistic data
    if (stripos($penaltyType->getName(), 'beer') !== false ||
        stripos($penaltyType->getName(), 'drink') !== false) {
        $penaltyType->setType(PenaltyTypeEnum::DRINK);
    } else if (stripos($penaltyType->getName(), 'late') !== false) {
        $penaltyType->setType(PenaltyTypeEnum::LATE_ARRIVAL);
    } else if (stripos($penaltyType->getName(), 'miss') !== false) {
        $penaltyType->setType(PenaltyTypeEnum::MISSED_TRAINING);
    } else {
        $penaltyType->setType(PenaltyTypeEnum::CUSTOM);
    }

    $penaltyType->setActive($faker->boolean(90)); // 90% active

    $entityManager->persist($penaltyType);
    $penaltyTypes[] = $penaltyType;

    echo "Created penalty type: " . $penaltyType->getName() . "\n";
}

// Generate penalties
echo "Generating penalties...\n";
$reasons = [
    'Late to practice by %d minutes',
    'Missed game against %s',
    'Forgot %s',
    'Got a yellow card for %s',
    'Got a red card for %s',
    'Missed team meeting on %s',
    'Late payment for %s',
    'Inappropriate behavior: %s',
    'Violated dress code: %s',
    'Phone rang during %s',
];

for ($i = 0; $i < $numPenalties; $i++) {
    $penalty = new Penalty();
    $penalty->setTeamUser($teamUsers[array_rand($teamUsers)]);
    $penalty->setType($penaltyTypes[array_rand($penaltyTypes)]);

    // Generate a reason based on the type
    $reasonTemplate = $reasons[array_rand($reasons)];
    $reasonValue = '';

    switch (true) {
        case strpos($reasonTemplate, '%d') !== false:
            $reasonValue = $faker->numberBetween(5, 60);
            break;
        case strpos($reasonTemplate, '%s') !== false:
            $reasonValue = $faker->word();
            break;
    }

    $penalty->setReason(sprintf($reasonTemplate, $reasonValue));

    // Set amount based on type
    if ($penalty->getType()->getType() === PenaltyTypeEnum::DRINK) {
        $penalty->setAmount($faker->numberBetween(500, 2000)); // 5-20 EUR
    } else if ($penalty->getType()->getType() === PenaltyTypeEnum::LATE_ARRIVAL) {
        $penalty->setAmount($faker->numberBetween(100, 500)); // 1-5 EUR
    } else if ($penalty->getType()->getType() === PenaltyTypeEnum::MISSED_TRAINING) {
        $penalty->setAmount($faker->numberBetween(1000, 3000)); // 10-30 EUR
    } else {
        $penalty->setAmount($faker->numberBetween(200, 5000)); // 2-50 EUR
    }

    // Set currency
    $currencies = [CurrencyEnum::EUR, CurrencyEnum::USD, CurrencyEnum::GBP];
    $penalty->setCurrency($currencies[array_rand($currencies)]);

    // Set some as archived
    $penalty->setArchived($faker->boolean(20));

    // Set some as paid
    if ($faker->boolean(40)) {
        $penalty->setPaidAt(new \DateTimeImmutable($faker->dateTimeThisYear()->format('Y-m-d H:i:s')));
    }

    $entityManager->persist($penalty);

    if ($i % 20 === 0) {
        echo "Created " . ($i + 1) . " penalties...\n";
    }
}

// Generate payments
echo "Generating payments...\n";
$paymentTypes = [
    PaymentTypeEnum::CASH,
    PaymentTypeEnum::BANK_TRANSFER,
    PaymentTypeEnum::CREDIT_CARD,
    PaymentTypeEnum::MOBILE_PAYMENT
];

for ($i = 0; $i < $numPayments; $i++) {
    $payment = new Payment();
    $payment->setTeamUser($teamUsers[array_rand($teamUsers)]);
    $payment->setAmount($faker->numberBetween(500, 10000)); // 5-100 EUR/USD/GBP

    // Set currency
    $currencies = [CurrencyEnum::EUR, CurrencyEnum::USD, CurrencyEnum::GBP];
    $payment->setCurrency($currencies[array_rand($currencies)]);

    // Set payment type
    $paymentType = $paymentTypes[array_rand($paymentTypes)];
    $payment->setType($paymentType);

    // Add reference if required
    if ($paymentType->requiresReference()) {
        $payment->setReference($faker->unique()->regexify('[A-Z0-9]{10}'));
    }

    // Add description to some payments
    if ($faker->boolean(70)) {
        $payment->setDescription($faker->sentence());
    }

    $entityManager->persist($payment);

    if ($i % 20 === 0) {
        echo "Created " . ($i + 1) . " payments...\n";
    }
}

// Flush all entities to the database
try {
    $entityManager->flush();

    echo "Fake data generation completed!\n";
    echo "Generated:\n";
    echo "- " . count($users) . " Users\n";
    echo "- " . count($teams) . " Teams\n";
    echo "- " . count($teamUsers) . " TeamUsers\n";
    echo "- " . $numReports . " Reports\n";
    echo "- " . $numNotifications . " Notifications\n";
    echo "- " . $numNotificationPreferences . " NotificationPreferences\n";
    echo "- " . count($penaltyTypes) . " PenaltyTypes\n";
    echo "- " . $numPenalties . " Penalties\n";
    echo "- " . $numPayments . " Payments\n";
} catch (\Exception $e) {
    echo "Error during data generation: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";

    // Try to get more specific error information
    if ($e instanceof \Doctrine\DBAL\Exception) {
        echo "DBAL Exception: " . $e->getMessage() . "\n";
        if ($e->getPrevious()) {
            echo "Previous exception: " . $e->getPrevious()->getMessage() . "\n";
        }
    }
}
