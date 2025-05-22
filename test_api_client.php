<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelBrowser;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__.'/.env');

// Create the kernel
$kernel = new App\Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();

// Create a browser to simulate requests
$browser = new HttpKernelBrowser($kernel);

// Test the API endpoint
echo "Testing API endpoint: /api/payments\n";
$browser->request(Request::METHOD_GET, '/api/payments', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);

// Get the response
$response = $browser->getResponse();
$statusCode = $response->getStatusCode();
$content = $response->getContent();

// Output the response
echo "Status code: $statusCode\n";
echo "Response content:\n";
echo $content;
