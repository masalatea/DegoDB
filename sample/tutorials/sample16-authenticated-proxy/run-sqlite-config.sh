#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
if [ "$#" -eq 0 ]; then
  set -- up
fi

export ADMIN_HTTP_PORT="${ADMIN_HTTP_PORT:-18261}"
export LAB_HTTP_PORT="${LAB_HTTP_PORT:-18262}"
export LAB_DB_HOST_PORT="${LAB_DB_HOST_PORT:-43262}"
export APP_CONFIG_STORE_DIR="${APP_CONFIG_STORE_DIR:-work/config-store-sample16-sqlite}"
export SAMPLE_PACK_COMPOSE_FILE="$SCRIPT_DIR/compose.sqlite-config.yaml"
export SAMPLE_PACK_COMPOSE_LANE=base
export SAMPLE_PACK_INCLUDE_LIFECYCLE=0

exec "$SCRIPT_DIR/../../_pack-support/sample-pack-runner.sh" "$SCRIPT_DIR" "$@"
