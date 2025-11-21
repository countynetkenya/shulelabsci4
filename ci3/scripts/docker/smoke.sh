#!/usr/bin/env bash
set -euo pipefail

COMPOSE_BIN=${COMPOSE_BIN:-"docker compose"}
BASE_URL="${BASE_URL:-${1:-http://localhost:8080}}"
BASE_URL="${BASE_URL%/}"

REQUIRED_SERVICES=(app web db)
WAIT_ATTEMPTS=${WAIT_ATTEMPTS:-24}
WAIT_DELAY=${WAIT_DELAY:-5}
CURL_TIMEOUT=${CURL_TIMEOUT:-15}
APP_START_ATTEMPTS=${APP_START_ATTEMPTS:-12}

for binary in docker curl python3; do
  if ! command -v "$binary" >/dev/null 2>&1; then
    echo "$binary is required to run the smoke checks." >&2
    exit 127
  fi
done

compose_cmd() {
  # shellcheck disable=SC2086
  ${COMPOSE_BIN} "$@"
}

run_composer_install() {
  if compose_cmd ps >/dev/null 2>&1; then
    if [[ ! -f vendor/autoload.php ]] || [[ composer.lock -nt vendor/autoload.php ]]; then
      echo "==> Ensuring PHP dependencies are installed" >&2
      COMPOSER_MEMORY_LIMIT=-1 compose_cmd exec -T app composer install --no-interaction --prefer-dist --no-progress >&2
    fi
  else
    echo "docker compose project is not running; start the stack before running smoke checks." >&2
    exit 1
  fi
}

services_healthy() {
  local json
  if ! json=$(compose_cmd ps --format json 2>/dev/null); then
    return 1
  fi

  python3 - "$json" "${REQUIRED_SERVICES[@]}" <<'PY'
import json
import sys

try:
    data = json.loads(sys.argv[1])
except Exception:
    sys.exit(1)

required = sys.argv[2:]
status = {item["Service"]: item for item in data}

for service in required:
    info = status.get(service)
    if not info:
        sys.exit(1)
    state = info.get("State", "").lower()
    health = (info.get("Health") or "").lower()
    if state != "running":
        sys.exit(1)
    if health and health != "healthy":
        sys.exit(1)

sys.exit(0)
PY
}

wait_for_services() {
  local attempt=1
  while (( attempt <= WAIT_ATTEMPTS )); do
    if services_healthy; then
      return 0
    fi
    sleep "$WAIT_DELAY"
    ((attempt++))
  done
  return 1
}

service_running() {
  local json service="$1"
  if ! json=$(compose_cmd ps --format json 2>/dev/null); then
    return 1
  fi

  python3 - "$json" "$service" <<'PY'
import json
import sys

try:
    data = json.loads(sys.argv[1])
except Exception:
    sys.exit(1)

service = sys.argv[2]
for item in data:
    if item.get("Service") == service:
        if item.get("State", "").lower() == "running":
            sys.exit(0)
        break

sys.exit(1)
PY
}

wait_for_app() {
  local attempt=1
  while (( attempt <= APP_START_ATTEMPTS )); do
    if service_running app; then
      return 0
    fi
    sleep "$WAIT_DELAY"
    ((attempt++))
  done
  return 1
}

check_http() {
  local url="$BASE_URL/index.php"
  echo "==> Checking HTTP 200 for ${url}" >&2
  local code
  code=$(curl -sS -o /dev/null -w '%{http_code}' --max-time "$CURL_TIMEOUT" "$url" || true)
  if [[ "$code" != "200" ]]; then
    echo "Request to ${url} returned HTTP ${code:-'n/a'}" >&2
    return 1
  fi
  echo "HTTP 200 received" >&2
}

main() {
  echo "==> Running docker smoke checks against ${BASE_URL}" >&2

  echo "==> Waiting for the PHP-FPM container" >&2
  if ! wait_for_app; then
    echo "The app container did not start in time." >&2
    compose_cmd ps >&2 || true
    exit 1
  fi

  run_composer_install

  echo "==> Waiting for services: ${REQUIRED_SERVICES[*]}" >&2
  if ! wait_for_services; then
    echo "Services did not become healthy in time." >&2
    compose_cmd ps >&2 || true
    exit 1
  fi

  check_http

  echo "All required containers are healthy and responding." >&2
}

main "$@"
