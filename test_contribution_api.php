<?php

// Script to test the contribution API endpoints
echo "Testing Contribution API Endpoints\n";

// Base URL for the API
$baseUrl = 'http://localhost:8000';

// Test GET /api/contribution-types
echo "\n1. Testing GET /api/contribution-types\n";
$url = $baseUrl . '/api/contribution-types';
testGetEndpoint($url);

// Test GET /api/contributions
echo "\n2. Testing GET /api/contributions\n";
$url = $baseUrl . '/api/contributions';
testGetEndpoint($url);

// Test GET /api/contribution-templates
echo "\n3. Testing GET /api/contribution-templates\n";
$url = $baseUrl . '/api/contribution-templates';
testGetEndpoint($url);

// Test GET /api/contribution-payments
echo "\n4. Testing GET /api/contribution-payments\n";
$url = $baseUrl . '/api/contribution-payments';
testGetEndpoint($url);

// Function to test a GET endpoint
function testGetEndpoint($url) {
    echo "Testing endpoint: $url\n";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP Code: $httpCode\n";

    if ($httpCode >= 200 && $httpCode < 300) {
        echo "Success! Response:\n";
        $jsonResponse = json_decode($response, true);
        echo json_encode($jsonResponse, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "Error! Response:\n";
        echo $response . "\n";
    }
}
