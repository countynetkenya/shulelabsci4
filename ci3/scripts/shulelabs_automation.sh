#!/usr/bin/env bash
set -uo pipefail

# ---------------------------
# ShuleLabs Automation Script
# CI4-first, CI3-safe fallbacks
# ---------------------------

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT_ENV="${PROJECT_ROOT:-}"

if [[ -n "$PROJECT_ROOT_ENV" ]]; then
  PROJECT_ROOT="$(cd "$PROJECT_ROOT_ENV" && pwd)"
else
  if [[ -f "$SCRIPT_DIR/composer.json" ]]; then
    PROJECT_ROOT="$SCRIPT_DIR"
  elif [[ -f "$SCRIPT_DIR/../composer.json" ]]; then
    PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
  else
    echo "Unable to locate composer.json. Set PROJECT_ROOT to the repository root." >&2
    exit 1
  fi
fi

cd "$PROJECT_ROOT"

TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
LOG_DIR="${LOG_DIR:-$PROJECT_ROOT/var/log}"
BACKUP_DIR="${BACKUP_DIR:-$PROJECT_ROOT/var/backups}"

mkdir -p "$LOG_DIR" "$BACKUP_DIR" "$PROJECT_ROOT/var/phpstan"

LOG_FILE="$LOG_DIR/automation_${TIMESTAMP}.log"
exec > >(tee -a "$LOG_FILE") 2>&1
exec 2>&1

echo "==> Shulelabs automation ($(date))"
echo "    Project root: $PROJECT_ROOT"
echo "    Log file: $LOG_FILE"

# ---------------------------
# Flags & env
# ---------------------------
APPLY_DB="${APPLY_DB:-1}"
DB_BACKUP="${DB_BACKUP:-1}"
RUN_TESTS="${RUN_TESTS:-1}"
RUN_STATIC="${RUN_STATIC:-1}"
RUN_PHPCS="${RUN_PHPCS:-1}"
RUN_SEEDERS="${RUN_SEEDERS:-0}"
VERIFY_DB="${VERIFY_DB:-0}"
MIGRATION_VERBOSE="${MIGRATION_VERBOSE:-1}"
CHECK_GIT_STATUS="${CHECK_GIT_STATUS:-1}"
CHECK_ENV_FILE="${CHECK_ENV_FILE:-1}"
ENV_FILE="${ENV_FILE:-.env}"

# Seeder control for CI4 (comma-separated list of seeder class names)
SEEDER_LIST="${SEEDER_LIST:-}"

# PHPStan scope: ci4|all
PHPSTAN_SCOPE="${PHPSTAN_SCOPE:-ci4}"

# SQL patches behavior
SQL_PATCH_DIR="${SQL_PATCH_DIR:-$PROJECT_ROOT/database/scripts}"
SQL_SCRIPTS="${SQL_SCRIPTS:-}"
FAIL_ON_SQL_ERROR="${FAIL_ON_SQL_ERROR:-1}"

# DB env
DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-root}"
DB_NAME="${DB_NAME:-}"
DB_PASSWORD="${DB_PASSWORD:-}"

FAILED_STEPS=()
SKIPPED_STEPS=()
MISSING_CMDS=()

require_cmd() {
  local cmd="$1"
  if ! command -v "$cmd" >/dev/null 2>&1; then
    MISSING_CMDS+=("$cmd")
  fi
}

run_step() {
  local label="$1"; shift
  echo -e "\n==> $label"
  echo "    Running: $*"
  "$@"
  local rc=$?
  if ((rc == 0)); then
    echo "<== Completed: $label"
  else
    echo "!! FAILED: $label (exit $rc)"
    FAILED_STEPS+=("$label (exit $rc)")
  fi
  return 0
}

skip_step() {
  local label="$1"; local reason="$2"
  SKIPPED_STEPS+=("$label ($reason)")
  echo -e "\n==> Skipping $label ($reason)"
}

git_status_summary() {
  if ! command -v git >/dev/null 2>&1 || ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    echo "    Git workspace not detected; skipping status details."
    return 0
  fi
  local branch last_commit
  branch=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "(unknown)")
  last_commit=$(git log -1 --pretty=format:'%h %s' 2>/dev/null || echo "(no commits)")
  echo "    Branch: ${branch}"
  echo "    Last commit: ${last_commit}"
  git status -sb || true
  if ! git diff --quiet --ignore-submodules --exit-code; then
    echo "    Working tree has local modifications."
  else
    echo "    Working tree clean."
  fi
}

verify_env_file() {
  if [[ -f "$ENV_FILE" ]]; then
    echo "    Found environment file: $ENV_FILE"
  else
    echo "    NOTE: ${ENV_FILE} not found. Copy .env.example and populate required secrets."
  fi
}

# ---------------------------
# Framework detection
# ---------------------------
is_ci4() {
  [[ -f "$PROJECT_ROOT/ci4/spark" ]] || [[ -f "$PROJECT_ROOT/spark" ]] || [[ -d "$PROJECT_ROOT/ci4/app" ]]
}

is_ci3() {
  [[ -d "$PROJECT_ROOT/application" ]] && [[ -d "$PROJECT_ROOT/application/migrations" ]]
}

# ---------------------------
# DB helpers
# ---------------------------
backup_database() {
  local backup_file="$BACKUP_DIR/${DB_NAME}_${TIMESTAMP}.sql"
  echo "    Writing backup to $backup_file"
  if ! mysqldump "${MYSQL_AUTH[@]}" "$DB_NAME" > "$backup_file"; then
    echo "    mysqldump failed"
    return 1
  fi
  return 0
}

# ---------------------------
# CI4 migrations & seeders
# ---------------------------
ci4_spark_bin() {
  if [[ -f "$PROJECT_ROOT/ci4/spark" ]]; then
    echo "$PROJECT_ROOT/ci4/spark"
  elif [[ -f "$PROJECT_ROOT/spark" ]]; then
    echo "$PROJECT_ROOT/spark"
  else
    echo ""
  fi
}

ci4_show_migration_status() {
  local spark; spark="$(ci4_spark_bin)"; [[ -n "$spark" ]] || return 1
  local cmd=(php "$spark" migrate:status)
  [[ "$MIGRATION_VERBOSE" == "1" ]] && cmd+=(-v)
  "${cmd[@]}"
}

ci4_run_migrations() {
  local spark; spark="$(ci4_spark_bin)"; [[ -n "$spark" ]] || return 1
  local cmd=(php "$spark" migrate --all)
  [[ "$MIGRATION_VERBOSE" == "1" ]] && cmd+=(-v)
  "${cmd[@]}"
}

ci4_run_seeders() {
  local spark; spark="$(ci4_spark_bin)"; [[ -n "$spark" ]] || return 1
  [[ "$RUN_SEEDERS" == "1" ]] || return 0
  if [[ -n "$SEEDER_LIST" ]]; then
    IFS=',' read -r -a seeds <<< "$SEEDER_LIST"
    for s in "${seeds[@]}"; do
      s="${s// /}"
      [[ -z "$s" ]] && continue
      php "$spark" db:seed "$s" || return $?
    done
  else
    echo "    No SEEDER_LIST provided; skipping CI4 seeders."
  fi
  return 0
}

# ---------------------------
# CI3 migrations (CLI only)
# Tries common tool routes; no web URIs.
# ---------------------------
ci3_try_run() {
  # Try a list of known CLI routes; first that succeeds wins.
  local action="$1" # "status" | "latest" | "seed_all"
  declare -a variants=(
    "php index.php tools migrate $action"
    "php index.php cli migrate $action"
    "php index.php migrate $action"
  )
  for v in "${variants[@]}"; do
    echo "    Trying: $v"
    if eval "$v"; then
      echo "    OK: $v"
      return 0
    fi
  done
  return 1
}

ci3_show_migration_status() { ci3_try_run "status"; }
ci3_run_migrations()        { ci3_try_run "latest"; }
ci3_run_seeders() {
  [[ "$RUN_SEEDERS" == "1" ]] || return 0
  # If you have a custom seeder runner in your CI3 Tools controller, this will hit it:
  ci3_try_run "seed_all" || { echo "    CI3 seeders not configured; skipping."; return 0; }
}

# ---------------------------
# SQL patches
# ---------------------------
apply_sql_scripts() {
  local scripts=() rc=0
  shopt -s nullglob
  if [[ -n "$SQL_SCRIPTS" ]]; then
    IFS=':' read -r -a scripts <<< "$SQL_SCRIPTS"
  else
    scripts=("$SQL_PATCH_DIR"/*.sql)
  fi
  if (( ${#scripts[@]} == 0 )); then
    echo "    No SQL scripts found in ${SQL_PATCH_DIR}; nothing to run."
    shopt -u nullglob; return 0
  fi
  for script in "${scripts[@]}"; do
    if [[ ! -f "$script" ]]; then
      echo "    Skipping missing script: $script"; continue
    fi
    echo "    Applying $(basename "$script")"
    if ! mysql "${MYSQL_DB_ARGS[@]}" < "$script"; then
      rc=$?
      echo "    ERROR applying $(basename "$script") (exit $rc)"
      shopt -u nullglob
      if [[ "$FAIL_ON_SQL_ERROR" == "1" ]]; then
        return $rc
      else
        echo "    Continuing despite SQL error (FAIL_ON_SQL_ERROR=0)"
      fi
    fi
  done
  shopt -u nullglob
  return 0
}

verify_database() {
  mysql "${MYSQL_AUTH[@]}" <<SQL
USE ${DB_NAME};
SHOW FULL TABLES LIKE 'inventory_ledger';
SHOW FULL TABLES LIKE 'inventory_onhand';
DESC productsaleitem;
DESC mainstock;
SQL
}

# ---------------------------
# Required tools
# ---------------------------
PREREQ_CMDS=(php composer)
if [[ "$APPLY_DB" == "1" ]]; then
  PREREQ_CMDS+=(mysql mysqldump)
fi
for cmd in "${PREREQ_CMDS[@]}"; do require_cmd "$cmd"; done
if (( ${#MISSING_CMDS[@]} )); then
  echo "Missing required command(s): ${MISSING_CMDS[*]}"; exit 1
fi

# ---------------------------
# Git + env checks
# ---------------------------
if [[ "$CHECK_GIT_STATUS" == "1" ]]; then
  run_step "Summarise git workspace" git_status_summary
else
  skip_step "Git workspace summary" "CHECK_GIT_STATUS=0"
fi

if [[ "$CHECK_ENV_FILE" == "1" ]]; then
  run_step "Check environment file (${ENV_FILE})" verify_env_file
else
  skip_step "Environment file check" "CHECK_ENV_FILE=0"
fi

# ---------------------------
# Infer DB name from env/bootstrap if empty
# ---------------------------
if [[ -z "$DB_NAME" || "$DB_NAME" == "shulelabs" ]]; then
  DB_NAME_FROM_ENV=$(php -r "set_include_path(get_include_path()); @include '${PROJECT_ROOT}/mvc/config/env.php'; if (function_exists('shulelabs_bootstrap_env')) { shulelabs_bootstrap_env('${PROJECT_ROOT}'); } if (function_exists('shulelabs_env')) { echo shulelabs_env('DB_NAME', shulelabs_env('DB_DATABASE', shulelabs_env('DATABASE_NAME', ''))); }") || DB_NAME_FROM_ENV=""
  [[ -n "$DB_NAME_FROM_ENV" ]] && DB_NAME="$DB_NAME_FROM_ENV"
fi
DB_NAME="${DB_NAME:-shulelabs_staging}"

MYSQL_AUTH=()
MYSQL_DB_ARGS=()
if [[ "$APPLY_DB" == "1" ]]; then
  MYSQL_AUTH=(-h "$DB_HOST" -u "$DB_USER")
  [[ -n "$DB_PORT" ]] && MYSQL_AUTH+=(-P "$DB_PORT")
  [[ -n "$DB_PASSWORD" ]] && MYSQL_AUTH+=(-p"$DB_PASSWORD")
  MYSQL_DB_ARGS=("${MYSQL_AUTH[@]}" "$DB_NAME")
fi

# ---------------------------
# Composer & QA
# ---------------------------
run_step "Install PHP dependencies (composer install)" composer install --no-interaction --prefer-dist
run_step "Optimise Composer autoloader" composer dump-autoload --optimize

# PHPUnit: run only if config exists AND bootstrap seems usable
if [[ "$RUN_TESTS" == "1" ]]; then
  if [[ -f "$PROJECT_ROOT/phpunit.xml" || -f "$PROJECT_ROOT/phpunit.xml.dist" ]]; then
    phpunit_config=""
    if [[ -f "$PROJECT_ROOT/phpunit.xml" ]]; then
      phpunit_config="$PROJECT_ROOT/phpunit.xml"
    else
      phpunit_config="$PROJECT_ROOT/phpunit.xml.dist"
    fi

    if [[ ! -x "$PROJECT_ROOT/vendor/bin/phpunit" ]]; then
      skip_step "PHPUnit test suite" "PHPUnit binary not installed (run composer install first)"
    elif grep -q "<bootstrap>" "$phpunit_config" 2>/dev/null; then
      export CI_ENVIRONMENT="${CI_ENVIRONMENT:-testing}"
      run_step "Run PHPUnit test suite" "$PROJECT_ROOT/vendor/bin/phpunit" --configuration "$phpunit_config"
    else
      skip_step "PHPUnit test suite" "No bootstrap in ${phpunit_config##*/}; to enable, add a proper bootstrap (CI4: tests/bootstrap.php)"
    fi
  else
    skip_step "PHPUnit test suite" "phpunit.xml(.dist) not found"
  fi
else
  skip_step "PHPUnit test suite" "RUN_TESTS=0"
fi

# PHPStan: default scope is CI4 only to avoid CI3 helper noise
if [[ "$RUN_STATIC" == "1" ]]; then
  if [[ -f "./vendor/bin/phpstan" ]]; then
    if [[ "$PHPSTAN_SCOPE" == "all" ]]; then
      run_step "Run PHPStan analysis (full repo)" ./vendor/bin/phpstan analyse --configuration phpstan.neon.dist --memory-limit=1G
    else
      # Try CI4 paths only
      if [[ -d "ci4/app" ]]; then
        phpstan_targets=("ci4/app")
        if [[ -d "ci4/modules" ]]; then
          phpstan_targets+=("ci4/modules")
        elif [[ -d "ci4/app/Modules" ]]; then
          phpstan_targets+=("ci4/app/Modules")
        fi
        run_step "Run PHPStan analysis (CI4 scope)" ./vendor/bin/phpstan analyse "${phpstan_targets[@]}" --configuration phpstan.neon.dist --memory-limit=1G || true
      else
        skip_step "PHPStan analysis" "No ci4/* paths; set PHPSTAN_SCOPE=all to analyse everything"
      fi
    fi
  else
    skip_step "PHPStan analysis" "phpstan not installed"
  fi
else
  skip_step "PHPStan analysis" "RUN_STATIC=0"
fi

if [[ "$RUN_PHPCS" == "1" ]]; then
  if [[ -f "./vendor/bin/phpcs" && -f "phpcs.xml.dist" ]]; then
    run_step "Run PHPCS lint (PSR-12)" ./vendor/bin/phpcs --standard=phpcs.xml.dist
  else
    skip_step "PHPCS lint" "PHPCS or phpcs.xml.dist missing"
  fi
else
  skip_step "PHPCS lint" "RUN_PHPCS=0"
fi

# ---------------------------
# Database actions
# ---------------------------
if [[ "$APPLY_DB" == "1" ]]; then
  if [[ "$DB_BACKUP" == "1" ]]; then
    run_step "Backup database ${DB_NAME}" backup_database
  else
    skip_step "Database backup" "DB_BACKUP=0"
  fi

  # CI4 first, then CI3 as fallback. It’s OK if both run when both stacks exist.
  if is_ci4; then
    run_step "CI4: Show migration status" ci4_show_migration_status
    run_step "CI4: Run pending migrations" ci4_run_migrations
    if [[ "$RUN_SEEDERS" == "1" ]]; then
      run_step "CI4: Run seeders (${SEEDER_LIST:-none})" ci4_run_seeders
    else
      skip_step "CI4: Seeders" "RUN_SEEDERS=0"
    fi
  else
    skip_step "CI4 migrations" "Not a CI4 project"
  fi

  if is_ci3; then
    run_step "CI3: Show migration status" ci3_show_migration_status
    run_step "CI3: Run pending migrations" ci3_run_migrations
    if [[ "$RUN_SEEDERS" == "1" ]]; then
      run_step "CI3: Run seeders" ci3_run_seeders
    else
      skip_step "CI3: Seeders" "RUN_SEEDERS=0"
    fi
  else
    skip_step "CI3 migrations" "Not a CI3 project"
  fi

  run_step "Apply SQL patch scripts" apply_sql_scripts

  if [[ "$VERIFY_DB" == "1" ]]; then
    run_step "Verify inventory schema objects" verify_database
  else
    skip_step "Inventory schema verification" "VERIFY_DB=0"
  fi
else
  skip_step "Database actions" "APPLY_DB=0"
fi

# ---------------------------
# Summary & exit
# ---------------------------
echo
if (( ${#FAILED_STEPS[@]} )); then
  echo "Summary: ${#FAILED_STEPS[@]} failure(s) detected."
  for item in "${FAILED_STEPS[@]}"; do echo "  - $item"; done
  if (( ${#SKIPPED_STEPS[@]} )); then
    echo "Skipped:"
    for item in "${SKIPPED_STEPS[@]}"; do echo "  - $item"; done
  fi
  echo "See $LOG_FILE for full output."
  exit 1
else
  echo "All automation steps completed successfully."
  if (( ${#SKIPPED_STEPS[@]} )); then
    echo "Skipped:"
    for item in "${SKIPPED_STEPS[@]}"; do echo "  - $item"; done
  fi
  echo "Logs written to $LOG_FILE"
  exit 0
fi
