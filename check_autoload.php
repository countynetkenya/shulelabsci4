<?php
require 'vendor/autoload.php';
// CI4 bootstrap
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/CodeIgniter.php';
$app = new CodeIgniter\CodeIgniter($paths);
$app->initialize();

if (class_exists('Modules\Hr\Database\Migrations\CreateHrTables')) {
    echo "Class found!\n";
} else {
    echo "Class NOT found!\n";
}
