<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Entity\Payment;
use Symfony\Component\Dotenv\Dotenv;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__.'/.env');

// Create the kernel
$kernel = new App\Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();
$container = $kernel->getContainer();

// Get the entity manager
$entityManager = $container->get('doctrine.orm.entity_manager');

// Get the payment repository
$paymentRepository = $entityManager->getRepository(Payment::class);

// Get all payments
$payments = $paymentRepository->findAll();

// Output the number of payments
echo "Number of payments: " . count($payments) . "\n";

// Output the first 5 payments
echo "First 5 payments:\n";
for ($i = 0; $i < min(5, count($payments)); $i++) {
    $payment = $payments[$i];
    echo "Payment ID: " . $payment->getId() . "\n";
    echo "Amount: " . $payment->getAmount() . "\n";
    echo "Currency: " . $payment->getCurrency()->value . "\n";
    echo "Type: " . $payment->getType()->value . "\n";
    echo "Created At: " . $payment->getCreatedAt()->format('Y-m-d H:i:s') . "\n";
    echo "\n";
}
