<?php

defined('BASEPATH') or exit('No direct script access allowed');

$config['backup'] = [
    'backup_path' => FCPATH . 'storage/backups',
    'gdrive_folder_id' => getenv('GDRIVE_BACKUP_FOLDER_ID') ?: null,
    'encryption_passphrase' => getenv('DB_BACKUP_PASSPHRASE') ?: null,
    'restore_database' => getenv('DB_RESTORE_DATABASE') ?: null,
    'retention_days' => 30,
];
