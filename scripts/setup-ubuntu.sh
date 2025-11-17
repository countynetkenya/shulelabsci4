#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO_ROOT"

if [[ ${EUID} -eq 0 ]]; then
  echo "[setup] Please run this script as a regular user with sudo privileges instead of root." >&2
  exit 1
fi

SUDO="sudo"

export DEBIAN_FRONTEND=noninteractive

log() {
  echo "[setup] $*"
}

PHP_PPA="ppa:ondrej/php"
DB_NAME=${DB_NAME:-shulelabs}
DB_USER=${DB_USER:-shulelabs}
DB_PASS=${DB_PASS:-shulelabs}
DB_HOST=${DB_HOST:-localhost}

log "Updating apt cache and installing prerequisite packages"
$SUDO apt-get update
$SUDO apt-get install -y software-properties-common ca-certificates curl unzip git

if ! grep -Rq "$PHP_PPA" /etc/apt/sources.list /etc/apt/sources.list.d 2>/dev/null; then
  log "Adding PHP repository ($PHP_PPA)"
  $SUDO add-apt-repository -y "$PHP_PPA"
fi

log "Installing PHP 8.3, Composer, and MySQL Server"
$SUDO apt-get update
$SUDO apt-get install -y \
  php8.3 php8.3-cli php8.3-common php8.3-mbstring php8.3-intl php8.3-sqlite3 \
  php8.3-curl php8.3-xml php8.3-gd php8.3-mysql composer mysql-server

log "Ensuring MySQL server is running"
$SUDO systemctl enable --now mysql

log "Creating or updating database '$DB_NAME' and user '$DB_USER'"
DB_PASS_SQL=$(echo "$DB_PASS" | sed "s/'/''/g")
$SUDO mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'$DB_HOST' IDENTIFIED BY '$DB_PASS_SQL';
ALTER USER '$DB_USER'@'$DB_HOST' IDENTIFIED BY '$DB_PASS_SQL';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'$DB_HOST';
FLUSH PRIVILEGES;
SQL

log "Installing Composer dependencies"
composer install --no-interaction --prefer-dist

log "Ensuring writable directories and environment file exist"
php scripts/setup-directories.php

log "Running database migrations to update schema"
php bin/migrate/latest

cat <<INFO
\nAll done! Next steps:\n- Update your .env file with database credentials if needed (DB name: $DB_NAME, user: $DB_USER).\n- Import your legacy data before rerunning the migrations if you restored from a dump.\nINFO
