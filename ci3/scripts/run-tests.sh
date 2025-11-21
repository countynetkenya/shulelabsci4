#!/usr/bin/env bash
set -euo pipefail

if [[ ! -f vendor/bin/phpunit ]]; then
  echo "Installing Composer dependencies required for the test suite..." >&2
  composer install --no-progress --no-interaction --ansi >/dev/null
fi

vendor/bin/phpunit --configuration=phpunit.ci4.xml "$@"
