<?php

/**
 * ShuleLabs Traffic Simulator
 * 
 * Simulates real user traffic for specific roles to validate system stability
 * and permission boundaries in real-time.
 * 
 * Usage: php scripts/simulate_traffic.php --role=student --interval=2
 */

require 'vendor/autoload.php';

// Configuration
$baseUrl = 'http://localhost:8080';
$cookieFile = sys_get_temp_dir() . '/cookie_' . getmypid() . '.txt';
$interval = 2; // Seconds between requests
$role = 'student';

// Parse Arguments
foreach ($argv as $arg) {
    if (strpos($arg, '--role=') === 0) {
        $role = substr($arg, 7);
    }
    if (strpos($arg, '--interval=') === 0) {
        $interval = (int)substr($arg, 11);
    }
    if (strpos($arg, '--base=') === 0) {
        $baseUrl = substr($arg, 7);
    }
}

// Load Persona Config
$configFile = __DIR__ . "/../tests/personas/{$role}.json";
if (!file_exists($configFile)) {
    die("❌ Error: Persona configuration not found for role: {$role}\n");
}
$config = json_decode(file_get_contents($configFile), true);

echo "🚀 Starting Traffic Simulator for Role: " . strtoupper($role) . "\n";
echo "👤 User: {$config['username']}\n";
echo "Target: {$baseUrl}\n";
echo "--------------------------------------------------\n";

// 1. Login
echo "🔑 Attempting Login... ";
$loginUrl = $baseUrl . '/auth/signin';
$postData = [
    'username' => $config['username'],
    'password' => $config['password'],
    // CSRF handling would go here if strict token checks were enabled for API
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200 || $httpCode == 302 || $httpCode == 303) {
    echo "✅ Success\n";
} else {
    echo "❌ Failed (HTTP $httpCode)\n";
    exit(1);
}

// 2. Traffic Loop
$endpoints = $config['endpoints'];
$count = 0;

while (true) {
    $endpoint = $endpoints[array_rand($endpoints)];
    $url = $baseUrl . $endpoint['path'];
    $method = $endpoint['method'] ?? 'GET';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true); // We want headers to check for redirects
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        if (isset($endpoint['data'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($endpoint['data']));
        }
    }

    $start = microtime(true);
    $response = curl_exec($ch);
    $duration = round((microtime(true) - $start) * 1000);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Status Indicator
    $statusIcon = '🟢';
    if ($httpCode >= 300 && $httpCode < 400) $statusIcon = '🟡'; // Redirect
    if ($httpCode >= 400 && $httpCode < 500) $statusIcon = '🟠'; // Client Error (Auth/Validation)
    if ($httpCode >= 500) $statusIcon = '🔴'; // Server Error

    // Timestamp
    $time = date('H:i:s');

    echo "[$time] $statusIcon $method $endpoint[path] - $httpCode ({$duration}ms)\n";

    // Alert on 500
    if ($httpCode >= 500) {
        echo "    ⚠️  CRITICAL ERROR DETECTED! Check logs.\n";
        // Optional: Play sound or notify
    }

    $count++;
    sleep($interval);
}
