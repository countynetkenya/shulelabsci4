#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * CI3 Backup Script (DISABLED)
 * 
 * This script has been disabled to prevent conflicts with CodeIgniter 4's
 * configuration system. Database backups should be managed through CI4
 * or external backup solutions.
 * 
 * If you need backup functionality, please implement it in CI4.
 */

fwrite(STDERR, "Error: CI3 backup script is disabled. Use CI4 backup solutions instead.\n");
// CI3 backup script is DISABLED - requires explicit configuration
// This script no longer reads from .env (CI3 is disabled)

echo "ERROR: CI3 backup script is disabled.\n";
echo "This script no longer reads from .env as CI3 has been migrated to CI4.\n";
echo "To run database backups, use CI4 commands or configure backups through CI4.\n";
echo "See README.md for migration guide.\n";
exit(1);
