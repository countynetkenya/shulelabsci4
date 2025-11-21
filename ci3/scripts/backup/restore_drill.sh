#!/usr/bin/env bash
set -euo pipefail

BACKUP_ROOT=${BACKUP_ROOT:-storage/backups}
LATEST_ARCHIVE=$(ls -1t "$BACKUP_ROOT"/*.gz 2>/dev/null | head -n1 || true)

if [[ -z "$LATEST_ARCHIVE" ]]; then
  echo "No backup archives found under $BACKUP_ROOT"
  exit 1
fi

RESTORE_DIR=${RESTORE_DIR:-storage/restore-drill}
rm -rf "$RESTORE_DIR"
mkdir -p "$RESTORE_DIR"

gzip -cd "$LATEST_ARCHIVE" > "$RESTORE_DIR/database.sql"

if [[ ! -s "$RESTORE_DIR/database.sql" ]]; then
  echo "Restore drill failed: database dump is empty"
  exit 1
fi

echo "Restore drill completed using $LATEST_ARCHIVE"
