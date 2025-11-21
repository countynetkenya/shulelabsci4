#!/bin/sh
set -e

HOST="${DB_HOST:-db}"
PORT="${DB_PORT:-3306}"
USER="${DB_USERNAME:-shulelabs}"
PASS="${DB_PASSWORD:-shulelabs}"

until MYSQL_PWD="$PASS" mysqladmin ping -h "$HOST" -P "$PORT" -u"$USER" --silent; do
  echo "Waiting for MySQL at ${HOST}:${PORT}..."
  sleep 1
done

exec "$@"
