#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
if [ "$#" -eq 0 ]; then
  set -- up
fi

export ADMIN_HTTP_PORT="${ADMIN_HTTP_PORT:-18191}"
export LAB_HTTP_PORT="${LAB_HTTP_PORT:-18192}"
export CONFIG_DB_HOST_PORT="${CONFIG_DB_HOST_PORT:-43191}"
export LAB_DB_HOST_PORT="${LAB_DB_HOST_PORT:-43192}"

exec "$SCRIPT_DIR/../../_pack-support/sample-pack-runner.sh" "$SCRIPT_DIR" "$@"
