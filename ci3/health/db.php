<?php
declare(strict_types=1);

header('Content-Type: application/json');

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = (int) (getenv('DB_PORT') ?: 3306);
$database = getenv('DB_DATABASE') ?: '';
$username = getenv('DB_USERNAME') ?: '';
$password = getenv('DB_PASSWORD') ?: '';

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @mysqli_init();

if ($connection === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to initialise mysqli']);
    exit;
}

$connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);

if (!@mysqli_real_connect($connection, $host, $username, $password, $database, $port)) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to connect to the database server'
    ]);
    exit;
}

if (!@mysqli_query($connection, 'SELECT 1')) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection established but simple query failed'
    ]);
    $connection->close();
    exit;
}

$connection->close();

echo json_encode([
    'status' => 'ok',
    'database' => $database ?: null,
    'host' => $host,
    'port' => $port
]);
