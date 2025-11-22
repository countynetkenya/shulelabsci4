<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CI3 Database Configuration (DISABLED)
 * 
 * This file has been disabled to prevent conflicts with CodeIgniter 4's
 * database configuration. All database configuration should be done through
 * CodeIgniter 4's app/Config/Database.php and the .env file.
 * 
 * If you need to use CI3 database functionality, please migrate to CI4.
 */

throw new \RuntimeException(
    'CI3 database configuration is disabled. ' .
    'Use CodeIgniter 4 database configuration in app/Config/Database.php instead.'
// CI3 database configuration is DISABLED
throw new Exception(
    'CI3 database configuration is DISABLED. '
    . 'This application has migrated to CodeIgniter 4. '
    . 'CI3 is legacy and no longer supported. '
    . 'To use CI4, ensure .env is properly configured with DB_* variables. '
    . 'See README.md for migration guide.'
);

