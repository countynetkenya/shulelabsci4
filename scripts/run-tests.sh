#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CI4_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
MONOREPO_ROOT="$(cd "${CI4_ROOT}/.." && pwd 2>/dev/null || echo "")"

find_phpunit() {
  local candidates=(
    "$CI4_ROOT/vendor/bin/phpunit"
  )
  if [[ -n "$MONOREPO_ROOT" ]]; then
    candidates+=("$MONOREPO_ROOT/vendor/bin/phpunit")
  fi
  for candidate in "${candidates[@]}"; do
    if [[ -x "$candidate" ]]; then
      echo "$candidate"
      return 0
    fi
  done
  return 1
}

ensure_dependencies() {
  local composer_root=""
  if [[ -f "$CI4_ROOT/composer.json" ]]; then
    composer_root="$CI4_ROOT"
  elif [[ -n "$MONOREPO_ROOT" && -f "$MONOREPO_ROOT/composer.json" ]]; then
    composer_root="$MONOREPO_ROOT"
  fi

  if [[ -z "$composer_root" ]]; then
    echo "Unable to locate composer.json to install dependencies." >&2
    return 1
  fi

  (cd "$composer_root" && composer install --no-interaction --prefer-dist)
}

PHPUNIT_BIN=""
if ! PHPUNIT_BIN="$(find_phpunit)"; then
  echo "PHPUnit binary not found; installing composer dependencies..." >&2
  ensure_dependencies
  PHPUNIT_BIN="$(find_phpunit)"
fi

if [[ -z "$PHPUNIT_BIN" ]]; then
  echo "Unable to locate vendor/bin/phpunit even after composer install." >&2
  exit 1
fi

CONFIG_FILE=""
if [[ -f "$CI4_ROOT/phpunit.ci4.xml" ]]; then
  CONFIG_FILE="$CI4_ROOT/phpunit.ci4.xml"
elif [[ -f "$CI4_ROOT/phpunit.xml" ]]; then
  CONFIG_FILE="$CI4_ROOT/phpunit.xml"
else
  echo "Unable to find a PHPUnit configuration file in $CI4_ROOT." >&2
  exit 1
fi

"$PHPUNIT_BIN" --configuration="$CONFIG_FILE" "$@"
