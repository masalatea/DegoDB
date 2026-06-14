#!/usr/bin/env bash
set -euo pipefail

if [ "$#" -lt 2 ]; then
  echo "usage: sample-pack-runner.sh PACK_DIR {up|down|reset|ps|logs|apply-seed} [args...]" >&2
  exit 1
fi

PACK_DIR="$1"
ACTION="$2"
shift 2

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
PACK_NAME="$(basename "$PACK_DIR")"
PACK_COMPOSE="$PACK_DIR/compose.yaml"
PACK_SEED_DIR="$PACK_DIR/seed"

compose_cmd=(docker compose)
while IFS= read -r compose_file; do
  [ -n "$compose_file" ] || continue
  compose_cmd+=(-f "$compose_file")
done < <(
  bash "$REPO_ROOT/mtool/scripts/list_compose_stack_files.sh" \
    "--compose-file=$PACK_COMPOSE" \
    "--compose-file=sample/_pack-support/sample-pack-lifecycle.compose.yaml"
)

case "$ACTION" in
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
    exec bash "$REPO_ROOT/mtool/scripts/apply_config_sample_seed.sh" \
      "--compose-file=$PACK_COMPOSE" \
      "$PACK_SEED_DIR" \
      "$@"
    ;;
  *)
    echo "unknown action [$PACK_NAME]: $ACTION" >&2
    exit 1
    ;;
esac
