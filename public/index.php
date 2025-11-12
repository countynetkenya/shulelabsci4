<?php

use CodeIgniter\Boot;
use Config\Paths;

/*
 *---------------------------------------------------------------
 * CHECK PHP VERSION
 *---------------------------------------------------------------
 */

$minPhpVersion = '8.1'; // If you update this, don't forget to update `spark`.
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION,
    );

    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo $message;

    exit(1);
}

/*
 *---------------------------------------------------------------
 * SET THE CURRENT DIRECTORY
 *---------------------------------------------------------------
 */

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 * This process sets up the path constants, loads and registers
 * our autoloader, along with Composer's, loads our constants
 * and fires up an environment-specific bootstrapping.
 */

// LOAD OUR PATHS CONFIG FILE
// This is the line that might need to be changed, depending on your folder structure.
require FCPATH . '../app/Config/Paths.php';
// ^^^ Change this line if you move your application folder

$paths = new Paths();

// LOAD THE FRAMEWORK BOOTSTRAP FILE
$bootPath = $paths->systemDirectory . '/Boot.php';

if (! is_file($bootPath)) {
    $message = <<<'HTML'
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Missing CodeIgniter System</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 2rem; line-height: 1.5; }
            pre { background: #f6f6f6; padding: 1rem; border-radius: .5rem; overflow: auto; }
        </style>
    </head>
    <body>
        <h1>Framework bootstrap not found</h1>
        <p>The CodeIgniter system directory could not be located at the expected path:</p>
        <pre>%s</pre>
        <p>Please ensure project dependencies are installed (for example by running <code>composer install</code>)
        and that the <code>vendor/codeigniter4/framework</code> package is available.</p>
    </body>
</html>
HTML;

    header('HTTP/1.1 503 Service Unavailable', true, 503);
    echo sprintf($message, htmlspecialchars($bootPath, ENT_QUOTES, 'UTF-8')) . PHP_EOL;

    exit(1);
}

require $bootPath;

exit(Boot::bootWeb($paths));
