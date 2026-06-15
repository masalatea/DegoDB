#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

usage() {
  cat <<'EOF'
usage: run_sample_pack_phpunit_test.sh --compose-file=FILE --run-script=FILE --phpunit-target=PATH [options]

Run a sample-pack-backed PHPUnit check and tear the stack down afterwards by default.

Options:
  --compose-file=FILE     Sample pack compose override file relative to repo root
  --run-script=FILE       Sample pack run.sh relative to repo root
  --phpunit-target=PATH   PHPUnit target path inside the container
  --apply-pack-seed       Run PACK run.sh apply-seed before PHPUnit
  --extra-seed=PATH       Additional seed directory/file to apply via apply_config_sample_seed.sh
  --help                  Show this help

Environment:
  KEEP_SAMPLE_STACK_RUNNING=1
      Skip automatic `down -v` cleanup after the check. Useful for debugging.
EOF
}

compose_file=""
run_script=""
phpunit_target=""
apply_pack_seed=0
extra_seed_paths=()

for argument in "$@"; do
  case "$argument" in
    --compose-file=*)
      compose_file="${argument#--compose-file=}"
      ;;
    --run-script=*)
      run_script="${argument#--run-script=}"
      ;;
    --phpunit-target=*)
      phpunit_target="${argument#--phpunit-target=}"
      ;;
    --apply-pack-seed)
      apply_pack_seed=1
      ;;
    --extra-seed=*)
      extra_seed_paths+=("${argument#--extra-seed=}")
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

if [ -z "$compose_file" ] || [ -z "$run_script" ] || [ -z "$phpunit_target" ]; then
  usage >&2
  exit 1
fi

compose_file_abs="$compose_file"
if [ "${compose_file_abs#/}" = "$compose_file_abs" ]; then
  compose_file_abs="$REPO_ROOT/$compose_file_abs"
fi

run_script_abs="$run_script"
if [ "${run_script_abs#/}" = "$run_script_abs" ]; then
  run_script_abs="$REPO_ROOT/$run_script_abs"
fi

if [ ! -f "$compose_file_abs" ]; then
  echo "compose file not found: $compose_file_abs" >&2
  exit 1
fi

if [ ! -f "$run_script_abs" ]; then
  echo "run script not found: $run_script_abs" >&2
  exit 1
fi

compose_cmd=(docker compose)
while IFS= read -r resolved_compose_file; do
  [ -n "$resolved_compose_file" ] || continue
  compose_cmd+=(-f "$resolved_compose_file")
done < <(
  bash "$REPO_ROOT/mtool/scripts/list_compose_stack_files.sh" \
    "--compose-file=$compose_file_abs"
)

cleanup() {
  if [ "${KEEP_SAMPLE_STACK_RUNNING:-0}" = "1" ]; then
    echo "keeping sample stack running: $run_script"
    return 0
  fi

  "$run_script_abs" reset >/dev/null 2>&1 || true
}

wait_for_config_db() {
  local attempts="${1:-90}"
  local attempt

  for attempt in $(seq 1 "$attempts"); do
    if "${compose_cmd[@]}" exec -T db-config sh -lc \
      'mariadb-admin ping -h 127.0.0.1 -uroot -p"$MARIADB_ROOT_PASSWORD" --silent' >/dev/null 2>&1; then
      return 0
    fi

    sleep 1
  done

  echo "db-config did not become ready after ${attempts}s" >&2
  "${compose_cmd[@]}" ps >&2 || true
  "${compose_cmd[@]}" logs --tail=80 db-config >&2 || true
  return 1
}

trap cleanup EXIT

"${compose_cmd[@]}" build web-admin web-lab
"$run_script_abs" up
wait_for_config_db

if [ "$apply_pack_seed" -eq 1 ]; then
  "$run_script_abs" apply-seed
fi

# Bash 3.2 on macOS treats an empty array expansion as unbound under `set -u`.
set +u
for extra_seed_path in "${extra_seed_paths[@]}"; do
  bash "$REPO_ROOT/mtool/scripts/apply_config_sample_seed.sh" \
    "--compose-file=$compose_file" \
    "$extra_seed_path"
done
set -u

"${compose_cmd[@]}" exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml "$phpunit_target"
