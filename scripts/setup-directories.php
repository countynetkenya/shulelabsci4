#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Lightweight bootstrap for setting up writable directories when the
 * CI4 runtime is extracted into its own repository.
 */

$projectRoot = dirname(__DIR__);

$directories = [
    'writable/cache'    => 0775,
    'writable/logs'     => 0775,
    'writable/session'  => 0775,
    'writable/uploads'  => 0775,
    'writable/uploads/tmp' => 0775,
    'writable/testing'  => 0775,
    'public/uploads'    => 0775,
];

$created = [];
$errors  = [];

foreach ($directories as $relative => $mode) {
    $path = $projectRoot . DIRECTORY_SEPARATOR . $relative;
    if (is_dir($path)) {
        if (DIRECTORY_SEPARATOR === '/' && !@chmod($path, $mode)) {
            $errors[] = "Failed to ensure permissions for {$relative}";
        }
        continue;
    }

    if (!@mkdir($path, $mode, true)) {
        $errors[] = "Failed to create directory {$relative}";
        continue;
    }

    $created[] = $relative;
}

$indexHtml = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>403 Forbidden</title>
</head>
<body>
<p>Directory access is forbidden.</p>
</body>
</html>
HTML;

$indexTargets = [
    'writable/cache',
    'writable/logs',
    'writable/session',
];

foreach ($indexTargets as $relative) {
    $path = $projectRoot . DIRECTORY_SEPARATOR . $relative;
    if (!is_dir($path)) {
        continue;
    }

    $indexPath = $path . DIRECTORY_SEPARATOR . 'index.html';
    if (!file_exists($indexPath) && file_put_contents($indexPath, $indexHtml) === false) {
        $errors[] = "Failed to write {$relative}/index.html";
    }
}

$envExample = $projectRoot . DIRECTORY_SEPARATOR . '.env.example';
$envFile    = $projectRoot . DIRECTORY_SEPARATOR . '.env';
if (is_file($envExample) && !file_exists($envFile)) {
    if (!@copy($envExample, $envFile)) {
        $errors[] = 'Failed to copy .env.example to .env';
    }
}

if ($created !== []) {
    echo 'Created directories:' . PHP_EOL;
    foreach ($created as $relative) {
        echo "  - {$relative}" . PHP_EOL;
    }
}

if ($errors !== []) {
    fwrite(STDERR, 'Encountered errors:' . PHP_EOL);
    foreach ($errors as $error) {
        fwrite(STDERR, "  - {$error}" . PHP_EOL);
    }
    exit(1);
}

echo "Writable directories verified." . PHP_EOL;
exit(0);
