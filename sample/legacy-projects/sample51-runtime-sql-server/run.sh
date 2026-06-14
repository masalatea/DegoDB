#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
if [ "$#" -eq 0 ]; then
  set -- up
fi
exec "$SCRIPT_DIR/../../_pack-support/sample-pack-runner.sh" "$SCRIPT_DIR" "$@"
