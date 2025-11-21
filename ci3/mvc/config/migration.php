<?php

defined('BASEPATH') or exit('No direct script access allowed');

$config['migration_enabled'] = TRUE;
$config['migration_type']    = 'timestamp';
$config['migration_path']    = APPPATH.'migrations/';
$config['migration_table']   = 'migrations';
$config['migration_auto_latest'] = FALSE;
$config['migration_version'] = 0;
