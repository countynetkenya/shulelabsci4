<?php

namespace App\Services\Database;

use CI_Controller;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use RuntimeException;

class DatabaseBackupService
{
    protected CI_Controller $ci;

    /**
     * @var array<string, mixed>
     */
    protected array $config;

    protected string $projectRoot;

    public function __construct(?CI_Controller $ci = null, ?string $projectRoot = null)
    {
        $this->ci = $ci ?? get_instance();
        $this->ci->load->database();
        $this->ci->load->config('backup');

        $config = $this->ci->config->item('backup');
        $this->config = is_array($config) ? $config : [];

        $this->projectRoot = $projectRoot
            ? rtrim($projectRoot, DIRECTORY_SEPARATOR)
            : rtrim((string) getenv('PROJECT_ROOT'), DIRECTORY_SEPARATOR);

        if ($this->projectRoot === '') {
            $this->projectRoot = rtrim(FCPATH, DIRECTORY_SEPARATOR);
        }

        $this->ensureBackupDirectory();
    }

    /**
     * @return array{file:string, checksum:string, drive_file_id:string|null}
     */
    public function runNightlyBackup(): array
    {
        $dumpPath = $this->createDump();
        $encryptedPath = $this->encryptDump($dumpPath);
        @unlink($dumpPath);

        $checksum = hash_file('sha256', $encryptedPath);
        $driveId = $this->uploadToDrive($encryptedPath, $checksum);
        $this->enforceRetention();

        return [
            'file' => $encryptedPath,
            'checksum' => $checksum,
            'drive_file_id' => $driveId,
        ];
    }

    /**
     * @return array{restored_database:string, source_file_id:string, restored_bytes:int}
     */
    public function runMonthlyRestoreDrill(?string $targetDatabase = null): array
    {
        $drive = $this->createDriveService();
        $folder = $this->getConfigValue('gdrive_folder_id');
        if (!$folder) {
            throw new RuntimeException('Google Drive backup folder ID is required for restore drills.');
        }

        $file = $this->fetchLatestBackup($drive, $folder);
        if ($file === null) {
            throw new RuntimeException('No backups available to restore.');
        }

        $response = $drive->files->get($file->getId(), ['alt' => 'media']);
        if (is_string($response)) {
            $contents = $response;
        } elseif (is_object($response) && method_exists($response, 'getBody')) {
            $contents = (string) $response->getBody();
        } else {
            $contents = (string) $response;
        }
        $sql = $this->decryptPayload($contents);

        $database = $targetDatabase
            ?: $this->getConfigValue('restore_database')
            ?: ($this->ci->db->database . '_restore_drill');

        $database = $this->prepareDatabase($database);
        $this->importSql($database, $sql);

        return [
            'restored_database' => $database,
            'source_file_id' => $file->getId(),
            'restored_bytes' => strlen($sql),
        ];
    }

    protected function ensureBackupDirectory(): void
    {
        $path = $this->getBackupPath();
        if (!is_dir($path)) {
            if (!@mkdir($path, 0770, true) && !is_dir($path)) {
                throw new RuntimeException('Unable to create backup directory at ' . $path);
            }
        }
    }

    protected function getBackupPath(): string
    {
        $path = $this->getConfigValue('backup_path');
        if (!$path) {
            $path = $this->projectRoot . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';
        }

        return rtrim($path, DIRECTORY_SEPARATOR);
    }

    protected function createDump(): string
    {
        $db = $this->ci->db;
        $filename = sprintf('%s-%s.sql', $db->database, date('YmdHis'));
        $dumpPath = $this->getBackupPath() . DIRECTORY_SEPARATOR . $filename;

        $commandParts = [
            'mysqldump',
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '-h', $db->hostname,
        ];

        if (!empty($db->port)) {
            $commandParts[] = '-P';
            $commandParts[] = (string) $db->port;
        }

        $commandParts[] = '-u';
        $commandParts[] = $db->username;
        $commandParts[] = $db->database;

        $command = $this->buildCommand($commandParts);
        $env = null;
        if (!empty($db->password)) {
            $env = ['MYSQL_PWD' => (string) $db->password];
        }

        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            null,
            $env
        );

        if (!is_resource($process)) {
            throw new RuntimeException('Unable to start mysqldump process.');
        }

        fclose($pipes[0]);
        $dumpStream = fopen($dumpPath, 'wb');
        if ($dumpStream === false) {
            proc_close($process);
            throw new RuntimeException('Unable to write dump file: ' . $dumpPath);
        }

        stream_copy_to_stream($pipes[1], $dumpStream);
        fclose($dumpStream);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);
        if ($exitCode !== 0) {
            @unlink($dumpPath);
            throw new RuntimeException('mysqldump failed: ' . trim($stderr));
        }

        return $dumpPath;
    }

    protected function encryptDump(string $path): string
    {
        $key = $this->getEncryptionKey();
        $plaintext = file_get_contents($path);
        if ($plaintext === false) {
            throw new RuntimeException('Unable to read dump file for encryption.');
        }

        $iv = random_bytes(16);
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        if ($ciphertext === false) {
            throw new RuntimeException('Unable to encrypt dump file.');
        }

        $mac = hash_hmac('sha256', $ciphertext, $key, true);

        $payload = json_encode([
            'algorithm' => 'aes-256-cbc',
            'iv' => base64_encode($iv),
            'ciphertext' => base64_encode($ciphertext),
            'hmac' => base64_encode($mac),
            'created_at' => date(DATE_ATOM),
        ], JSON_UNESCAPED_SLASHES);

        if ($payload === false) {
            throw new RuntimeException('Unable to encode encrypted payload.');
        }

        $encryptedPath = $path . '.enc';
        if (file_put_contents($encryptedPath, $payload) === false) {
            throw new RuntimeException('Unable to write encrypted backup file.');
        }

        return $encryptedPath;
    }

    protected function decryptPayload(string $payload): string
    {
        $data = json_decode($payload, true);
        if (!is_array($data) || empty($data['ciphertext']) || empty($data['iv']) || empty($data['hmac'])) {
            throw new RuntimeException('Invalid backup payload.');
        }

        $key = $this->getEncryptionKey();
        $ciphertext = base64_decode($data['ciphertext'], true);
        $iv = base64_decode($data['iv'], true);
        $mac = base64_decode($data['hmac'], true);

        if ($ciphertext === false || $iv === false || $mac === false) {
            throw new RuntimeException('Unable to decode encrypted payload.');
        }

        $expectedMac = hash_hmac('sha256', $ciphertext, $key, true);
        if (!hash_equals($expectedMac, $mac)) {
            throw new RuntimeException('Encrypted backup integrity check failed.');
        }

        $plaintext = openssl_decrypt($ciphertext, $data['algorithm'] ?? 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        if ($plaintext === false) {
            throw new RuntimeException('Unable to decrypt backup payload.');
        }

        return $plaintext;
    }

    protected function uploadToDrive(string $filePath, string $checksum): string
    {
        $drive = $this->createDriveService();
        $folderId = $this->getConfigValue('gdrive_folder_id');
        if (!$folderId) {
            throw new RuntimeException('Google Drive folder ID is not configured.');
        }

        $metadata = new DriveFile([
            'name' => basename($filePath),
            'parents' => [$folderId],
            'appProperties' => ['sha256' => $checksum],
        ]);

        $stream = fopen($filePath, 'rb');
        if ($stream === false) {
            throw new RuntimeException('Unable to open encrypted backup for upload.');
        }

        try {
            $file = $drive->files->create(
                $metadata,
                [
                    'data' => $stream,
                    'mimeType' => 'application/octet-stream',
                    'uploadType' => 'multipart',
                    'fields' => 'id,name,sha256Checksum,appProperties',
                ]
            );
        } finally {
            fclose($stream);
        }

        $remoteChecksum = $file->getSha256Checksum() ?: ($file->getAppProperties()['sha256'] ?? null);
        if ($remoteChecksum !== $checksum) {
            throw new RuntimeException('Checksum verification failed after upload.');
        }

        return (string) $file->getId();
    }

    protected function fetchLatestBackup(Drive $drive, string $folderId): ?DriveFile
    {
        $query = sprintf("'%s' in parents and trashed = false", $folderId);
        $results = $drive->files->listFiles([
            'q' => $query,
            'orderBy' => 'createdTime desc',
            'pageSize' => 1,
            'fields' => 'files(id,name,createdTime)',
        ]);

        $files = $results->getFiles();
        if (empty($files)) {
            return null;
        }

        return $files[0];
    }

    protected function enforceRetention(): void
    {
        $days = (int) $this->getConfigValue('retention_days', 30);
        if ($days <= 0) {
            return;
        }

        $cutoff = time() - ($days * 86400);
        foreach (glob($this->getBackupPath() . DIRECTORY_SEPARATOR . '*.enc') as $file) {
            if (filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }

    protected function getEncryptionKey(): string
    {
        $passphrase = $this->getConfigValue('encryption_passphrase');
        if (!$passphrase) {
            $passphrase = getenv('DB_BACKUP_PASSPHRASE') ?: '';
        }

        if ($passphrase === '') {
            throw new RuntimeException('Database backup passphrase is not configured.');
        }

        return hash('sha256', $passphrase, true);
    }

    protected function createDriveService(): Drive
    {
        $client = new Client();
        $client->setApplicationName('Shulelabs Backups');
        $client->setScopes([Drive::DRIVE_FILE]);

        $credentialsPath = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        if ($credentialsPath && is_file($credentialsPath)) {
            $client->setAuthConfig($credentialsPath);
        } elseif ($json = getenv('GOOGLE_APPLICATION_CREDENTIALS_JSON')) {
            $client->setAuthConfig(json_decode($json, true));
        } else {
            throw new RuntimeException('Google API credentials are not configured.');
        }

        return new Drive($client);
    }

    protected function prepareDatabase(string $database): string
    {
        $sanitized = preg_replace('/[^A-Za-z0-9_]/', '_', $database);
        if ($sanitized === '') {
            throw new RuntimeException('Restore database name is not valid.');
        }

        $db = $this->ci->db;
        $password = (string) $db->password;
        $port = (int) ($db->port !== 0 ? $db->port : 3306);

        $mysqli = @new \mysqli($db->hostname, $db->username, $password, '', $port);
        if ($mysqli->connect_errno) {
            throw new RuntimeException('Unable to prepare restore database: ' . $mysqli->connect_error);
        }

        $escaped = $mysqli->real_escape_string($sanitized);
        $mysqli->query("DROP DATABASE IF EXISTS `{$escaped}`");
        if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `{$escaped}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
            throw new RuntimeException('Unable to create restore database: ' . $mysqli->error);
        }

        $mysqli->close();

        return $sanitized;
    }

    protected function importSql(string $database, string $sql): void
    {
        $db = $this->ci->db;
        $commandParts = ['mysql', '-h', $db->hostname];

        if (!empty($db->port)) {
            $commandParts[] = '-P';
            $commandParts[] = (string) $db->port;
        }

        $commandParts[] = '-u';
        $commandParts[] = $db->username;
        $commandParts[] = $database;

        $command = $this->buildCommand($commandParts);
        $env = null;
        if (!empty($db->password)) {
            $env = ['MYSQL_PWD' => (string) $db->password];
        }

        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'w'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            null,
            $env
        );

        if (!is_resource($process)) {
            throw new RuntimeException('Unable to run mysql client for restore.');
        }

        fwrite($pipes[0], $sql);
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);
        if ($exitCode !== 0) {
            throw new RuntimeException('Restore drill failed: ' . trim($stderr ?: $stdout));
        }
    }

    /**
     * @param list<string> $parts
     */
    protected function buildCommand(array $parts): string
    {
        $command = [];
        foreach ($parts as $index => $part) {
            if ($index === 0) {
                $command[] = $part;
            } else {
                $command[] = escapeshellarg($part);
            }
        }

        return implode(' ', $command);
    }

    protected function getConfigValue(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }
}
