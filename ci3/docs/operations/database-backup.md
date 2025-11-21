# Database backup and restore playbook

Nightly database backups are handled by the `cron/nightly_database_backup` CLI task which wraps the
`App\Services\Database\DatabaseBackupService`. A matching `cron/monthly_restore_drill` command
rehearses restores to guarantee that encrypted artefacts can be recovered.

## Configuration

The service reads configuration from `mvc/config/backup.php` and the following environment variables:

| Variable | Purpose |
| --- | --- |
| `DB_BACKUP_PASSPHRASE` | Secret used to encrypt/decrypt dump payloads. |
| `GDRIVE_BACKUP_FOLDER_ID` | Google Drive folder that will store `.sql.enc` files. |
| `GOOGLE_APPLICATION_CREDENTIALS` or `GOOGLE_APPLICATION_CREDENTIALS_JSON` | Google service-account credentials used for Drive uploads. |
| `DB_RESTORE_DATABASE` (optional) | Override the name of the ephemeral database used for drill restores. |

Backups are written to `storage/backups/` before being uploaded to Google Drive. Files older than the
configured retention window (30 days by default) are pruned locally after every successful backup.

## Nightly flow

1. Run `php index.php cron nightly_database_backup` from a crontab or scheduler.
2. The service shells out to `mysqldump` using the configured connection credentials and stores the
   dump in a timestamped file.
3. The plain-text dump is encrypted with AES-256-CBC, salted by a random IV, and signed with an
   HMAC. The encrypted blob is uploaded to Google Drive where the SHA-256 checksum is enforced via
   Drive metadata.
4. Local plain-text dumps are deleted immediately after encryption completes.

## Restore drills

Monthly restore rehearsals ensure that the encrypted artefacts are usable:

1. Run `php index.php cron monthly_restore_drill`.
2. The most recent backup is downloaded, its integrity verified, and the payload decrypted.
3. A throwaway database (default `<dbname>_restore_drill`) is created, the SQL streamed to `mysql`,
   and the import verified. Operators may inspect the database before dropping it.

The drill exits non-zero and emits structured log output if any stage fails (missing credentials,
checksum mismatch, MySQL import errors, etc.).

## Cron template

```
# Nightly at 02:00 EAT
0 2 * * * www-data /usr/bin/php /var/www/shulelabs/index.php cron nightly_database_backup >> /var/log/shulelabs/backup.log 2>&1

# First Sunday of every month at 03:00 EAT
0 3 1-7 * 0 www-data /usr/bin/php /var/www/shulelabs/index.php cron monthly_restore_drill >> /var/log/shulelabs/backup.log 2>&1
```

Ensure the crontab environment exposes the necessary environment variables (e.g. through
`/etc/cron.d/shulelabs`) and that `mysqldump`/`mysql` binaries are present on the host.
