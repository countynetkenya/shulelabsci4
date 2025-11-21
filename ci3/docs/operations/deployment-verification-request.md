# Deployment Verification Request – Inventory Patch Rollout

## Context
This request accompanies the one-click deployment bundle for the inventory module refresh. Reviewers need the exact Bash payload
that operators will execute on staging/production hosts in order to audit safety characteristics (backups, idempotency, privileg
e usage) and verify that every checklist item is satisfied.

## Deployment Script
```bash
#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/var/www/besha"
DB_NAME="shulelabs_staging"
SQL_FILE="${APP_DIR}/final_inventory_patch.sql"
CRON_LOG="/var/log/inventory_monthly.log"
PHP_FPM_SERVICE="php8.2-fpm"
WEB_USER="www-data"
WEB_GROUP="www-data"

echo "==> Shulelabs One-Click Patch"

# Sanity
test -f "${SQL_FILE}" || { echo "Missing ${SQL_FILE}"; exit 1; }
test -d "${APP_DIR}/patch_bundle_files" || { echo "Missing ${APP_DIR}/patch_bundle_files (copy the bundle folder)"; exit 1; }

# Prompt for DB password
read -s -p "Enter MySQL root password: " DB_PASS
echo

# Backups
echo "==> Backups"
sudo mkdir -p /var/backups
sudo mysqldump -u root -p"${DB_PASS}" "${DB_NAME}" > "/var/backups/${DB_NAME}_$(date +%F_%H%M%S).sql" || { echo "Backup failed"; exit 1; }
sudo tar -czf "/var/backups/besha_code_$(date +%F_%H%M%S).tar.gz" "${APP_DIR}" || true

# Apply SQL
echo "==> Applying SQL"
mysql -u root -p"${DB_PASS}" "${DB_NAME}" < "${SQL_FILE}"

# Install cron (monthly aggregate)
echo "==> Installing CRON"
touch "${CRON_LOG}" || true
sudo chown ${WEB_USER}:${WEB_GROUP} "${CRON_LOG}" || true
( crontab -l 2>/dev/null | grep -v "php index.php Cron/refreshInventoryMonthly" ; echo "10 1 * * * cd ${APP_DIR} && php index.php Cron/refreshInventoryMonthly >> ${CRON_LOG} 2>&1" ) | crontab -

# Copy files
echo "==> Copying controllers/helpers/views/js"
sudo mkdir -p "${APP_DIR}/mvc/controllers" "${APP_DIR}/mvc/views/partials" "${APP_DIR}/mvc/views/inventory" "${APP_DIR}/application/helpers" "${APP_DIR}/public/js"
sudo cp -f "${APP_DIR}/patch_bundle_files/mvc/controllers/"*.php "${APP_DIR}/mvc/controllers/" || true
sudo cp -f "${APP_DIR}/patch_bundle_files/mvc/views/partials/"*.php "${APP_DIR}/mvc/views/partials/" || true
sudo cp -f "${APP_DIR}/patch_bundle_files/mvc/views/inventory/"*.php "${APP_DIR}/mvc/views/inventory/" || true
sudo cp -f "${APP_DIR}/patch_bundle_files/application/helpers/"*.php "${APP_DIR}/application/helpers/" || true
sudo cp -f "${APP_DIR}/patch_bundle_files/public/js/"*.js "${APP_DIR}/public/js/" || true

# Wire routes for CI3 (application/config/routes.php) or CI4 (app/Config/Routes.php)
echo "==> Wiring routes"
if [ -f "${APP_DIR}/application/config/routes.php" ]; then
  ROUTES_FILE="${APP_DIR}/application/config/routes.php"
elif [ -f "${APP_DIR}/app/Config/Routes.php" ]; then
  ROUTES_FILE="${APP_DIR}/app/Config/Routes.php"
else
  echo "  ! Could not find routes.php (CI3) or Routes.php (CI4). Skipping route wiring."
  ROUTES_FILE=""
fi

if [ -n "$ROUTES_FILE" ]; then
  sudo cp "$ROUTES_FILE" "$ROUTES_FILE.bak.$(date +%s)" || true
  if ! grep -q "InventoryTransfer/accept" "$ROUTES_FILE" 2>/dev/null; then
    sudo bash -c "cat >> \"$ROUTES_FILE\" <<'PHP_EOF'
// --- BEGIN: auto-wired (inventory patch) ---
\$route['inventory/transfer/(:num)/accept']['POST'] = 'InventoryTransfer/accept/\$1';
\$route['inventory/transfer/(:num)/reject']['POST'] = 'InventoryTransfer/reject/\$1';
\$route['productapi/movement_series/(:num)']        = 'ProductApi/movement_series/\$1';
// --- END: auto-wired ---
PHP_EOF"
    echo "  + Routes appended to $ROUTES_FILE"
  else
    echo "  = Routes already present"
  fi
fi

# Permissions
echo "==> Permissions & reload"
sudo chown -R ${WEB_USER}:${WEB_GROUP} "${APP_DIR}"
sudo find "${APP_DIR}" -type d -exec chmod 755 {} \;
sudo find "${APP_DIR}" -type f -exec chmod 644 {} \;
sudo systemctl reload ${PHP_FPM_SERVICE} || true
sudo systemctl reload nginx || true

# Verify
echo "==> Verify DB objects"
mysql -u root -p"${DB_PASS}" -e "
USE ${DB_NAME};
SHOW FULL TABLES LIKE 'inventory_ledger';
SHOW FULL TABLES LIKE 'inventory_onhand';
DESC productsaleitem;
DESC mainstock;
" || true

echo "==> Done."
```

## Reviewer Notes
- **Variables & guards:** Environment-specific paths (`APP_DIR`, `DB_NAME`, log locations) and sanity checks are defined up fro
nt so reviewers can confirm the operator must provide the correct bundle and credentials before proceeding.
- **Backups & rollback:** The script performs database and code backups before applying changes, satisfying the rollback preche
ck.
- **Cron definition:** The crontab mutation is fully spelled out (monthly aggregate job at `10 1 * * *`) with log redirection, m
aking it easy to audit for idempotency and duplicate suppression via `grep -v`.
- **File sync & permissions:** Copy operations are constrained to the patched directories and followed by ownership/permission
resets, highlighting the scope of filesystem changes.
- **Service reload & verification:** Explicit reloads for PHP-FPM/nginx and schema verification queries demonstrate the closing
steps reviewers expect.

All sections from the deployment bundle are now embedded here (no placeholders remain) so the security/operations review can eva
luate shell safety end-to-end.
