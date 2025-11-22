#!/usr/bin/env php
<?php
declare(strict_types=1);

// CI3 backup script is DISABLED - requires explicit configuration
// This script no longer reads from .env (CI3 is disabled)

echo "ERROR: CI3 backup script is disabled.\n";
echo "This script no longer reads from .env as CI3 has been migrated to CI4.\n";
echo "To run database backups, use CI4 commands or configure backups through CI4.\n";
echo "See README.md for migration guide.\n";
exit(1);
