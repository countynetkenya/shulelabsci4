#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

report_event() {
  local task="$1"
  local status="$2"
  local message="${3:-}"
  local duration="${4:-}"

  local args=(--task "$task" --status "$status")
  if [[ -n "$message" ]]; then
    args+=(--message "$message")
  fi
  if [[ -n "$duration" ]]; then
    args+=(--duration "$duration")
  fi

  if ! php scripts/cron/report_scheduler.php "${args[@]}"; then
    echo "[scheduler] failed to record event for ${task}" >&2
  fi
}

run_task() {
  local task="$1"
  shift
  local start_ts=$SECONDS

  if "$@"; then
    local duration=$(( SECONDS - start_ts ))
    echo "[scheduler] ${task} completed successfully"
    report_event "$task" ok "completed" "$duration"
  else
    local exit_code=$?
    local duration=$(( SECONDS - start_ts ))
    echo "[scheduler] ${task} failed with exit code ${exit_code}"
    report_event "$task" error "exit_code=${exit_code}" "$duration"
  fi
}

echo "[scheduler] booting at $(date -u +"%Y-%m-%dT%H:%M:%SZ")"
report_event "boot" ok "scheduler booted"

while true; do
  echo "[scheduler] running audit verification"
  run_task "audit-guard" php scripts/ci/audit-guard.php

  echo "[scheduler] running backup self-test"
  run_task "backup-self-test" env BACKUP_ROOT="${BACKUP_ROOT:-storage/backups}" php scripts/backup/run_backup.php --self-test

  report_event "heartbeat" ok "cycle-complete"

  echo "[scheduler] sleeping until next window"
  # Run daily
  sleep 86400
done
