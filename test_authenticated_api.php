 <?php

// Script to test the API endpoint with authentication
$baseUrl = 'http://localhost:8000';
$loginUrl = $baseUrl . '/api/login_check';
$paymentsUrl = $baseUrl . '/api/payments';

echo "Step 1: Authenticating to get JWT token\n";

// First, we need to get a JWT token
$loginData = json_encode([
    'username' => 'admin', // Replace with actual username
    'password' => 'admin'  // Replace with actual password
]);

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login HTTP Code: $httpCode\n";
echo "Login Response:\n";
echo $response . "\n\n";

// Parse the response to get the token
$responseData = json_decode($response, true);
$token = $responseData['token'] ?? null;

if (!$token) {
    echo "Failed to get JWT token. Please check the login credentials.\n";
    echo "You may need to create a user with the correct credentials first.\n";
    exit(1);
}

echo "Step 2: Accessing payments endpoint with JWT token\n";

// Now, use the token to access the payments endpoint
$ch = curl_init($paymentsUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Payments HTTP Code: $httpCode\n";
echo "Payments Response:\n";
echo $response;
