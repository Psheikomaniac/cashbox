<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Dotenv\Dotenv;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__ . '/.env');

// Bootstrap the Symfony kernel
$kernel = new \App\Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

// Get the entity manager
$entityManager = $kernel->getContainer()->get('doctrine')->getManager();

// Create a new user
$user = new User();
$user->setFirstName('Test');
$user->setLastName('User');
$user->setEmail('test@example.com');
$user->setPhoneNumber('1234567890');

// Persist and flush
try {
    $entityManager->persist($user);
    $entityManager->flush();
    echo "User created successfully with ID: " . $user->getId() . PHP_EOL;
    echo "Created at: " . $user->getCreatedAt()->format('Y-m-d H:i:s') . PHP_EOL;
} catch (\Exception $e) {
    echo "Error creating user: " . $e->getMessage() . PHP_EOL;
}
