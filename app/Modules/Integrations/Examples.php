<?php

/**
 * ⚠️ EXAMPLE FILE - NOT FOR PRODUCTION USE
 * 
 * Example: Using the Integrations Module
 *
 * This file demonstrates how to use the IntegrationService to:
 * 1. Register adapters
 * 2. Execute integration operations
 * 3. Handle errors and retries
 * 
 * This file contains echo statements for demonstration purposes only.
 * Do not include this file in production code.
 */

// Bootstrap CodeIgniter (adjust path as needed)
// require_once __DIR__ . '/../../../vendor/autoload.php';

use Modules\Integrations\Services\Adapters\Storage\LocalStorageAdapter;
use Modules\Integrations\Services\Adapters\Payment\MpesaAdapter;
use Modules\Integrations\Services\Adapters\Communication\SmsAdapter;

// Example 1: Using Local Storage
function exampleLocalStorage()
{
    $integrations = service('integrations');

    // Register the adapter
    $storageAdapter = new LocalStorageAdapter([
        'base_path' => WRITEPATH . 'uploads',
    ]);
    $integrations->register('local_storage', $storageAdapter);

    // Upload a file
    try {
        $response = $integrations->execute(
            'local_storage',
            'upload',
            [
                'file_path'   => '/path/to/source/file.pdf',
                'destination' => 'documents/2024/report.pdf',
            ],
            ['tenant_id' => 'school123', 'user_id' => 1]
        );

        echo "File uploaded successfully!\n";
        echo "File ID: {$response['file_id']}\n";
        echo "URL: {$response['url']}\n";
    } catch (\RuntimeException $e) {
        echo "Error uploading file: {$e->getMessage()}\n";
    }

    // List files
    try {
        $response = $integrations->execute(
            'local_storage',
            'list',
            ['path' => 'documents/2024/'],
            ['tenant_id' => 'school123']
        );

        echo "Files found: " . count($response['files']) . "\n";
        foreach ($response['files'] as $file) {
            echo "  - {$file['name']} ({$file['size']} bytes)\n";
        }
    } catch (\RuntimeException $e) {
        echo "Error listing files: {$e->getMessage()}\n";
    }
}

// Example 2: Using M-Pesa Payment
function exampleMpesaPayment()
{
    $integrations = service('integrations');

    // Register the adapter with configuration
    $mpesaAdapter = new MpesaAdapter([
        'consumer_key'    => getenv('MPESA_CONSUMER_KEY'),
        'consumer_secret' => getenv('MPESA_CONSUMER_SECRET'),
        'shortcode'       => getenv('MPESA_SHORTCODE'),
        'passkey'         => getenv('MPESA_PASSKEY'),
        'environment'     => getenv('MPESA_ENVIRONMENT') ?: 'sandbox',
    ]);
    $integrations->register('mpesa', $mpesaAdapter);

    // Charge a customer
    try {
        $response = $integrations->execute(
            'mpesa',
            'charge',
            [
                'amount'    => 1000.0,
                'currency'  => 'KES',
                'phone'     => '254712345678',
                'reference' => 'FEE-2024-001',
            ],
            ['tenant_id' => 'school123', 'user_id' => 1]
        );

        echo "Payment initiated!\n";
        echo "Transaction ID: {$response['transaction_id']}\n";
        echo "Status: {$response['status']}\n";
    } catch (\RuntimeException $e) {
        echo "Error initiating payment: {$e->getMessage()}\n";
    }
}

// Example 3: Using SMS
function exampleSms()
{
    $integrations = service('integrations');

    // Register the adapter
    $smsAdapter = new SmsAdapter([
        'username'  => getenv('SMS_USERNAME'),
        'api_key'   => getenv('SMS_API_KEY'),
        'sender_id' => getenv('SMS_SENDER_ID') ?: 'SHULELABS',
    ]);
    $integrations->register('sms', $smsAdapter);

    // Send SMS
    try {
        $response = $integrations->execute(
            'sms',
            'send',
            [
                'to'      => '+254712345678',
                'message' => 'Your payment of KES 1,000 has been received. Thank you!',
                'from'    => 'SHULELABS',
            ],
            ['tenant_id' => 'school123', 'user_id' => 1]
        );

        echo "SMS sent successfully!\n";
        echo "Message ID: {$response['message_id']}\n";
        echo "Status: {$response['status']}\n";
        echo "Cost: KES {$response['cost']}\n";
    } catch (\RuntimeException $e) {
        echo "Error sending SMS: {$e->getMessage()}\n";
    }
}

// Example 4: Health Checks
function exampleHealthCheck()
{
    $integrations = service('integrations');

    // Register adapters (abbreviated for example)
    $integrations->register('local_storage', new LocalStorageAdapter(['base_path' => WRITEPATH . 'uploads']));

    // Check health of a specific adapter
    try {
        $health = $integrations->checkHealth('local_storage');

        echo "Local Storage Health:\n";
        echo "  Status: {$health['status']}\n";
        echo "  Message: {$health['message']}\n";

        if (isset($health['details'])) {
            echo "  Details: " . json_encode($health['details']) . "\n";
        }
    } catch (\RuntimeException $e) {
        echo "Error checking health: {$e->getMessage()}\n";
    }

    // List all registered adapters
    $adapters = $integrations->getRegisteredAdapters();
    echo "\nRegistered Adapters:\n";
    foreach ($adapters as $adapter) {
        echo "  - {$adapter}\n";
    }
}

// Example 5: Error Handling and Idempotency
function exampleErrorHandling()
{
    $integrations = service('integrations');
    $integrations->register('sms', new SmsAdapter([
        'username'  => 'test',
        'api_key'   => 'test',
        'sender_id' => 'TEST',
    ]));

    // The same operation called multiple times will only execute once
    // due to idempotency key (based on operation + payload hash)
    $payload = [
        'to'      => '+254712345678',
        'message' => 'Test message',
    ];
    $context = ['tenant_id' => 'school123', 'user_id' => 1];

    try {
        // First call - will execute
        $response1 = $integrations->execute('sms', 'send', $payload, $context);
        echo "First call - Message ID: {$response1['message_id']}\n";

        // Second call with same payload - will return cached result
        $response2 = $integrations->execute('sms', 'send', $payload, $context);
        echo "Second call - Message ID: {$response2['message_id']}\n";

        // Both should have the same message_id due to idempotency
        echo "Same result: " . ($response1['message_id'] === $response2['message_id'] ? 'Yes' : 'No') . "\n";
    } catch (\RuntimeException $e) {
        echo "Error: {$e->getMessage()}\n";
    }
}

/*
 * Uncomment the example you want to run:
 */

// exampleLocalStorage();
// exampleMpesaPayment();
// exampleSms();
// exampleHealthCheck();
// exampleErrorHandling();
