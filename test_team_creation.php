<?php

// Make a POST request to create a team with just the name field
$url = 'http://localhost:8080/api/teams';
$data = json_encode(['name' => 'Test Team Created via Script']);

// Initialize cURL
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/ld+json',
    'Content-Length: ' . strlen($data)
]);

// Execute the request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for errors
if (curl_errno($ch)) {
    echo "Error: " . curl_error($ch) . "\n";
} else {
    echo "Response status code: " . $httpCode . "\n";
    echo "Response content: " . $response . "\n";
}

// Close cURL
curl_close($ch);
