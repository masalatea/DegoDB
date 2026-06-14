#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

usage() {
  cat <<'EOF'
usage: list_compose_stack_files.sh [--lane=local|base] [--compose-file=FILE ...]

Print absolute Docker Compose file paths in merge order, one path per line.

Options:
  --lane=LANE          Compose lane to expand: local, base (default: local)
  --compose-file=FILE  Extra compose override file relative to repo root or absolute
  --help               Show this help
EOF
}

lane="local"
compose_files=()

for argument in "$@"; do
  case "$argument" in
    --lane=*)
      lane="${argument#--lane=}"
      ;;
    --compose-file=*)
      compose_files+=("${argument#--compose-file=}")
      ;;
    --help|-h)
      usage
      exit 0
      ;;
    *)
      echo "unsupported argument: $argument" >&2
      usage >&2
      exit 1
      ;;
  esac
done

resolved_files=("$REPO_ROOT/compose.yaml")

case "$lane" in
  local)
    resolved_files+=("$REPO_ROOT/compose.local-db-config.yaml")
    ;;
  base)
    ;;
  *)
    echo "unsupported compose lane: $lane" >&2
    exit 1
    ;;
esac

for compose_file in "${compose_files[@]}"; do
  if [ "${compose_file#/}" = "$compose_file" ]; then
    compose_file="$REPO_ROOT/$compose_file"
  fi

  resolved_files+=("$compose_file")
done

for compose_file in "${resolved_files[@]}"; do
  if [ ! -f "$compose_file" ]; then
    echo "compose file not found: $compose_file" >&2
    exit 1
  fi

  printf '%s\n' "$compose_file"
done
