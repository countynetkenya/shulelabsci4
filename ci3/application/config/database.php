<?php

defined('BASEPATH') or exit('No direct script access allowed');

$sharedDatabaseConfig = dirname(__DIR__, 2) . '/mvc/config/database.php';

if (!is_file($sharedDatabaseConfig)) {
    trigger_error(
        sprintf('Shared database configuration missing at %s', $sharedDatabaseConfig),
        E_USER_ERROR
    );
}

require_once $sharedDatabaseConfig;
