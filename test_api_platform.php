<?php
/**
 * API Platform Test Script
 * 
 * Dieses Script testet die API-Platform-Endpunkte
 */

// API-Platform Base URL
$baseUrl = 'http://localhost:8080/api';

// Test-Funktionen
function testApiEndpoint($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'response' => json_decode($response, true) ?: $response
    ];
}

echo "=== API Platform Test ===\n\n";

// Test 1: API-Dokumentation
echo "1. Teste API-Dokumentation...\n";
$result = testApiEndpoint($baseUrl . '/docs');
echo "Status: " . $result['status'] . "\n";
if ($result['status'] !== 200) {
    echo "Response: " . print_r($result['response'], true) . "\n";
}
echo "\n";

// Test 2: Users-Endpunkt
echo "2. Teste Users-Endpunkt...\n";
$result = testApiEndpoint($baseUrl . '/users');
echo "Status: " . $result['status'] . "\n";
echo "Response: " . print_r($result['response'], true) . "\n";

// Test 3: User erstellen
echo "3. Teste User erstellen...\n";
$userData = [
    'name' => [
        'firstName' => 'Test',
        'lastName' => 'User'
    ],
    'emailValue' => 'test@example.com',
    'phoneNumberValue' => '+49123456789'
];

$result = testApiEndpoint($baseUrl . '/users', 'POST', $userData);
echo "Status: " . $result['status'] . "\n";
echo "Response: " . print_r($result['response'], true) . "\n";

echo "\n=== Test abgeschlossen ===\n";