#!/usr/bin/env php
<?php
/**
 * Setup required directories for ShuleLabs application.
 * 
 * This script creates necessary directories for the application to run properly,
 * including cache, logs, sessions, and uploads directories.
 * 
 * @package ShuleLabs
 * @author  ShuleLabs Team
 */

// Get the project root directory (parent of scripts/)
$projectRoot = dirname(__DIR__);

// Define directories to create with their permissions
$directories = array(
    // CI3 directories
    'application/cache' => 0775,
    'application/logs' => 0775,
    
    // CI4 runtime directories
    'ci4/var/cache' => 0775,
    'ci4/var/logs' => 0775,
    'ci4/var/sessions' => 0775,
    
    // Upload directories
    'ci4/public/uploads' => 0775,
    
    // Storage directories
    'storage/backups' => 0775,
    'storage/restore-drill' => 0775,
);

$errors = array();
$created = array();

$successfulDirectories = array();

foreach ($directories as $dir => $permissions) {
    $fullPath = $projectRoot . DIRECTORY_SEPARATOR . $dir;
    
    if (is_dir($fullPath)) {
        // Directory exists, ensure permissions are correct (Unix-like systems only)
        if (DIRECTORY_SEPARATOR === '/' && !chmod($fullPath, $permissions)) {
            $errors[] = "Failed to set permissions for: {$dir}";
        } else {
            echo "✓ Directory exists and permissions updated: {$dir}\n";
            $successfulDirectories[] = $dir;
        }
    } else {
        // Create directory
        if (mkdir($fullPath, $permissions, true)) {
            $created[] = $dir;
            $successfulDirectories[] = $dir;
            echo "✓ Created directory: {$dir}\n";
        } else {
            $errors[] = "Failed to create directory: {$dir}";
        }
    }
}

// Copy configuration files if they don't exist
$configFiles = array(
    '.env.example' => '.env',
    'phpunit.xml.dist' => 'phpunit.xml',
    'phpunit.ci4.xml' => 'ci4/phpunit.ci4.xml',
);

foreach ($configFiles as $source => $destination) {
    $sourcePath = $projectRoot . DIRECTORY_SEPARATOR . $source;
    $destPath = $projectRoot . DIRECTORY_SEPARATOR . $destination;
    
    if (file_exists($sourcePath) && !file_exists($destPath)) {
        if (copy($sourcePath, $destPath)) {
            echo "✓ Copied {$source} to {$destination}\n";
        } else {
            $errors[] = "Failed to copy {$source} to {$destination}";
        }
    }
}

// Create index.html files to prevent directory listing
$indexHtml = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>403 Forbidden</title>
</head>
<body>
<p>Directory access is forbidden.</p>
</body>
</html>
';

// Only create index.html in cache and log directories that were successfully created/verified
$indexDirectories = array(
    'application/cache',
    'application/logs',
    'ci4/var/cache',
    'ci4/var/logs',
    'ci4/var/sessions',
);

foreach ($indexDirectories as $dir) {
    // Only create index.html if the directory exists in the successful directories list
    if (in_array($dir, $successfulDirectories)) {
        $indexPath = $projectRoot . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'index.html';
        if (!file_exists($indexPath)) {
            if (file_put_contents($indexPath, $indexHtml)) {
                echo "✓ Created index.html in {$dir}\n";
            } else {
                $errors[] = "Failed to create index.html in {$dir}";
            }
        }
    }
}

// Summary
echo "\n";
if (count($created) > 0) {
    echo "Created " . count($created) . " new directories.\n";
}

if (count($errors) > 0) {
    echo "\nErrors encountered:\n";
    foreach ($errors as $error) {
        echo "  ✗ {$error}\n";
    }
    exit(1);
}

echo "✓ All directories setup successfully!\n";
exit(0);
