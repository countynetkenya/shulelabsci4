#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use CodeIgniter\Database\Config as DatabaseConfig;
use Config\Paths;
use Modules\Foundation\Services\AuditService;

$paths = new Paths();

defined('ROOTPATH') || define('ROOTPATH', realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR);
defined('APPPATH') || define('APPPATH', realpath($paths->appDirectory) . DIRECTORY_SEPARATOR);
defined('SYSTEMPATH') || define('SYSTEMPATH', realpath($paths->systemDirectory) . DIRECTORY_SEPARATOR);
defined('WRITEPATH') || define('WRITEPATH', realpath($paths->writableDirectory) . DIRECTORY_SEPARATOR);
defined('TESTPATH') || define('TESTPATH', realpath($paths->testsDirectory) . DIRECTORY_SEPARATOR);
if (is_dir($paths->testsDirectory . '/_support/') && ! defined('SUPPORTPATH')) {
    define('SUPPORTPATH', realpath($paths->testsDirectory . '/_support/') . DIRECTORY_SEPARATOR);
}

$environment = $_SERVER['CI_ENVIRONMENT'] ?? getenv('CI_ENVIRONMENT') ?? 'development';
if (! is_string($environment) || $environment === '') {
    $environment = 'development';
}

$_SERVER['CI_ENVIRONMENT'] = $environment;
defined('ENVIRONMENT') || define('ENVIRONMENT', $environment);

require_once SYSTEMPATH . 'Common.php';
if (is_file(APPPATH . 'Common.php')) {
    require_once APPPATH . 'Common.php';
}

$environmentBootstrap = APPPATH . 'Config/Boot/' . strtolower($environment) . '.php';
if (is_file($environmentBootstrap)) {
    require_once $environmentBootstrap;
}

$dbConfig = new \Config\Database();
$group    = $dbConfig->defaultGroup;
$settings = $dbConfig->{$group} ?? $dbConfig->default;

$connection = DatabaseConfig::connect($settings, false);
$service = new AuditService($connection);

if ($service->verifyIntegrity()) {
    echo "Audit hash chain verified.\n";
    exit(0);
}

echo "Audit hash chain verification failed." . PHP_EOL;
exit(1);
