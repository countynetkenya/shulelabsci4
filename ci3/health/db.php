<?php
declare(strict_types=1);

// CI3 health check is DISABLED
http_response_code(410); // Gone
header('Content-Type: application/json');

echo json_encode([
    'status' => 'error',
    'message' => 'CI3 health check is disabled. Use CI4 instead.',
    'code' => 'CI3_DISABLED'
]);
exit;
