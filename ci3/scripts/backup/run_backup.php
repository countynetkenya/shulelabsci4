#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__ . '/../../../vendor/autoload.php';

use CodeIgniter\I18n\Time;

$options = getopt('', ['self-test']);
$backupRoot = getenv('BACKUP_ROOT') ?: __DIR__ . '/../../storage/backups';
if (! is_dir($backupRoot)) {
    mkdir($backupRoot, 0775, true);
}

$timestamp = Time::now('UTC')->format('Ymd\THis\Z');

if (array_key_exists('self-test', $options)) {
    $payload = $backupRoot . '/self-test-' . $timestamp . '.txt';
    file_put_contents($payload, "ShuleLabs backup self-test executed at {$timestamp}\n");
    $archive = $payload . '.gz';
    $resource = gzopen($archive, 'wb9');
    gzwrite($resource, file_get_contents($payload));
    gzclose($resource);
    unlink($payload);
    echo "Created backup self-test archive: {$archive}\n";
    exit(0);
}

$dbName = getenv('DB_DATABASE') ?: getenv('DB_NAME') ?: '';
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbUser = getenv('DB_USERNAME') ?: getenv('DB_USER') ?: 'root';
$dbPassword = getenv('DB_PASSWORD') ?: '';

if (empty($dbName)) {
    fwrite(STDERR, "Error: DB_DATABASE environment variable is not set.\n");
    exit(1);
}

$dumpPath = sprintf('%s/database-%s.sql', $backupRoot, $timestamp);
$archivePath = $dumpPath . '.gz';

$command = sprintf(
    'mysqldump --single-transaction --no-tablespaces --column-statistics=0 -h%s -u%s -p%s %s > %s',
    escapeshellarg($dbHost),
    escapeshellarg($dbUser),
    escapeshellarg($dbPassword),
    escapeshellarg($dbName),
    escapeshellarg($dumpPath)
);

$exitCode = 0;
$descriptorSpec = [0 => ['pipe', 'r'], 1 => STDOUT, 2 => STDERR];
$process = proc_open($command, $descriptorSpec, $pipes);
if (is_resource($process)) {
    fclose($pipes[0]);
    $exitCode = proc_close($process);
}

if ($exitCode !== 0) {
    throw new RuntimeException('Failed to execute mysqldump command.');
}

$resource = gzopen($archivePath, 'wb9');
if ($resource === false) {
    throw new RuntimeException('Unable to open archive for writing.');
}

gzwrite($resource, (string) file_get_contents($dumpPath));
gzclose($resource);
unlink($dumpPath);

echo "Created backup archive: {$archivePath}\n";
