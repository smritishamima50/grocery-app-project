<?php
session_start();

// Test admin login first
echo "=== Testing Admin Login ===\n";
$loginData = [
    'email' => 'admin@grocery.com',
    'password' => 'admin123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($loginData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Login Response Code: $httpCode\n";
echo "Login Response: " . substr($response, 0, 200) . "...\n\n";

// Test orders API
echo "=== Testing Orders API ===\n";
curl_setopt($ch, CURLOPT_URL, 'http://localhost/api/admin/orders');
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, null);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Orders API Response Code: $httpCode\n";
echo "Orders API Response: " . substr($response, 0, 500) . "...\n\n";

// Test order status update
echo "=== Testing Order Status Update ===\n";
$updateData = json_encode([
    'status' => 'confirmed',
    'assigned_driver' => 'Test Driver'
]);

curl_setopt($ch, CURLOPT_URL, 'http://localhost/api/admin/orders/1');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
curl_setopt($ch, CURLOPT_POSTFIELDS, $updateData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($updateData)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Order Update Response Code: $httpCode\n";
echo "Order Update Response: " . substr($response, 0, 500) . "...\n\n";

// Test products API
echo "=== Testing Products API ===\n";
curl_setopt($ch, CURLOPT_URL, 'http://localhost/api/admin/products');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_POSTFIELDS, null);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Products API Response Code: $httpCode\n";
echo "Products API Response: " . substr($response, 0, 500) . "...\n\n";

// Test coupons API
echo "=== Testing Coupons API ===\n";
curl_setopt($ch, CURLOPT_URL, 'http://localhost/api/admin/coupons');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_POSTFIELDS, null);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Coupons API Response Code: $httpCode\n";
echo "Coupons API Response: " . substr($response, 0, 500) . "...\n\n";

curl_close($ch);

// Clean up
if (file_exists('cookies.txt')) {
    unlink('cookies.txt');
}
?>
