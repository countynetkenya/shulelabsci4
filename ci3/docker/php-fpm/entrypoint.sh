#!/usr/bin/env bash
set -euo pipefail

TARGET_USER="www-data"
TARGET_GROUP="www-data"

DIRECTORIES=(
  "/var/www/html/storage"
  "/var/www/html/writable"
  "/var/www/html/uploads"
  "/var/www/html/ci4/writable"
  "/var/www/html/main/writable"
)

ensure_directory() {
  local dir="$1"
  if [[ ! -d "$dir" ]]; then
    mkdir -p "$dir"
  fi
}

adjust_permissions() {
  local dir="$1"
  if [[ -d "$dir" ]]; then
    if ! chown -R "$TARGET_USER:$TARGET_GROUP" "$dir"; then
      echo "[entrypoint] Warning: unable to change ownership of $dir" >&2
    fi
    find "$dir" -type d -exec chmod 775 {} + || true
    find "$dir" -type f -exec chmod 664 {} + || true
  fi
}

if [[ "${1:-}" == "php-fpm" || "${1:-}" == "php-fpm8.3" ]]; then
  for dir in "${DIRECTORIES[@]}"; do
    ensure_directory "$dir"
    adjust_permissions "$dir"
  done
fi

if [[ "$(id -u)" == "0" ]]; then
  exec gosu "$TARGET_USER" docker-php-entrypoint "$@"
else
  exec docker-php-entrypoint "$@"
fi
