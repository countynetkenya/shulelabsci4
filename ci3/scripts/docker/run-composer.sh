#!/usr/bin/env bash
set -euo pipefail

if [[ $# -lt 2 ]]; then
  echo "Usage: $0 <working_dir> <composer-args...>" >&2
  exit 1
fi

workdir="$1"
shift

if [[ ! -d "$workdir" ]]; then
  echo "Directory '$workdir' does not exist; skipping composer $*" >&2
  exit 0
fi

if [[ ! -f "$workdir/composer.json" ]]; then
  echo "composer.json not found in '$workdir'; skipping composer $*" >&2
  exit 0
fi

pushd "$workdir" >/dev/null
trap 'popd >/dev/null' EXIT

echo "Running composer $* in $workdir"
composer "$@"
