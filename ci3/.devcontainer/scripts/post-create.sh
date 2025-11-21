#!/usr/bin/env bash
set -euo pipefail

cd /workspace/shulelabs

composer install --no-interaction --prefer-dist

if [ -f spark ]; then
    php spark migrate --all || true
fi

mkdir -p application/cache application/logs
chmod -R 0775 application/cache application/logs || true

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi
