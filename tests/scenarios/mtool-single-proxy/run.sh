#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../../.." && pwd)"

if [ "$#" -eq 0 ]; then
  set -- up
fi

action="$1"
shift

compose_cmd=(
  docker
  compose
  -f "$REPO_ROOT/compose.yaml"
  -f "$SCRIPT_DIR/compose.yaml"
)

case "$action" in
  up)
    exec "${compose_cmd[@]}" up -d "$@"
    ;;
  down)
    exec "${compose_cmd[@]}" down "$@"
    ;;
  reset)
    exec "${compose_cmd[@]}" down -v "$@"
    ;;
  ps)
    exec "${compose_cmd[@]}" ps "$@"
    ;;
  logs)
    exec "${compose_cmd[@]}" logs -f "$@"
    ;;
  apply-seed)
    exec "$REPO_ROOT/mtool/scripts/apply_config_sample_seed.sh" \
      "--compose-file=$SCRIPT_DIR/compose.yaml" \
      "$SCRIPT_DIR/seed" \
      "$@"
    ;;
  *)
    echo "unknown action: $action" >&2
    exit 1
    ;;
esac
