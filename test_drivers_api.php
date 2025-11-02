<?php
/**
 * Test script for Drivers API endpoint
 * Run this from command line: php test_drivers_api.php
 * Or access via browser (make sure you're logged in as admin)
 */

// Start session for testing
session_start();

// Check if running from CLI
$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    // For web browser testing, ensure admin session
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        die("Please log in as admin first. Go to /login and login with admin credentials.");
    }
}

// Simulate API request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/api/admin/drivers';

// Include the main entry point
ob_start();
try {
    require_once 'index.php';
    $output = ob_get_clean();
    
    // Check if output is valid JSON
    $json = json_decode($output, true);
    
    if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ FAILED: Response is not valid JSON\n";
        echo "Response (first 500 chars):\n";
        echo substr($output, 0, 500) . "\n";
        echo "\nJSON Error: " . json_last_error_msg() . "\n";
        exit(1);
    }
    
    echo "✅ SUCCESS: Valid JSON response received\n";
    echo "Response structure:\n";
    print_r($json);
    
    if (isset($json['success']) && $json['success']) {
        echo "\n✅ API returned success=true\n";
        if (isset($json['drivers'])) {
            echo "✅ Drivers array found with " . count($json['drivers']) . " driver(s)\n";
        } else {
            echo "⚠️  No drivers array in response\n";
        }
    } else {
        echo "\n⚠️  API returned success=false\n";
        if (isset($json['error'])) {
            echo "Error message: " . $json['error'] . "\n";
        }
    }
    
} catch (Throwable $e) {
    ob_end_clean();
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

