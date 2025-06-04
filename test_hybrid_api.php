<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpKernel\HttpKernelBrowser;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__.'/.env');

// Create the kernel
$kernel = new App\Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();

// Create a browser to simulate requests
$browser = new HttpKernelBrowser($kernel);

echo "=== HYBRID API TESTING ===\n\n";

// Test 1: API Platform CRUD (Teams)
echo "1. Testing API Platform CRUD - Teams:\n";
$browser->request('GET', '/api/teams', [], [], ['HTTP_ACCEPT' => 'application/json']);
$response = $browser->getResponse();
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . substr($response->getContent(), 0, 200) . "...\n\n";

// Test 2: API Platform CRUD (Users)
echo "2. Testing API Platform CRUD - Users:\n";
$browser->request('GET', '/api/users', [], [], ['HTTP_ACCEPT' => 'application/json']);
$response = $browser->getResponse();
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . substr($response->getContent(), 0, 200) . "...\n\n";

// Test 3: Manual Controller (Contributions)
echo "3. Testing Manual Controller - Contributions:\n";
$browser->request('GET', '/api/contributions', [], [], ['HTTP_ACCEPT' => 'application/json']);
$response = $browser->getResponse();
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . substr($response->getContent(), 0, 200) . "...\n\n";

// Test 4: Manual Controller (Dashboard)
echo "4. Testing Manual Controller - Dashboard:\n";
$browser->request('GET', '/api/dashboard', [], [], ['HTTP_ACCEPT' => 'application/json']);
$response = $browser->getResponse();
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . substr($response->getContent(), 0, 200) . "...\n\n";

// Test 5: Manual Controller (Penalties)
echo "5. Testing Manual Controller - Penalties:\n";
$browser->request('GET', '/api/penalties', [], [], ['HTTP_ACCEPT' => 'application/json']);
$response = $browser->getResponse();
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . substr($response->getContent(), 0, 200) . "...\n\n";

echo "=== HYBRID API TESTING COMPLETE ===\n";