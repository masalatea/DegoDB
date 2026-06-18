#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
DEFAULT_SAMPLE_ROOT="$REPO_ROOT/sample"
DEFAULT_ACTIVE_SAMPLE_ROOTS=(
  "$DEFAULT_SAMPLE_ROOT/tutorials"
  "$DEFAULT_SAMPLE_ROOT/internal-patterns"
  "$DEFAULT_SAMPLE_ROOT/legacy-projects"
)
DEFAULT_TEST_SCENARIO_ROOT="$REPO_ROOT/tests/scenarios"

usage() {
  cat <<'TEXT'
Usage:
  ./mtool/scripts/apply_config_sample_seed.sh
  ./mtool/scripts/apply_config_sample_seed.sh mtool-single-proxy
  ./mtool/scripts/apply_config_sample_seed.sh sample01-simple-table-runtime
  ./mtool/scripts/apply_config_sample_seed.sh --scenario=01_mtool sample/legacy-projects/sample51-runtime-sql-server/seed
  ./mtool/scripts/apply_config_sample_seed.sh --compose-file=tests/scenarios/mtool-single-proxy/compose.yaml tests/scenarios/mtool-single-proxy/seed

Options:
  --scenario=NAME       Compose scenario: base, 01_mtool
  --compose-file=FILE   Additional compose override file relative to repo root
  --help                Show this help

Notes:
  - Applies optional sample/test SQL seeds to the running `db-config` container.
  - If no arguments are given, all `sample/{tutorials,internal-patterns,legacy-projects}/*/seed/*.sql` and `tests/scenarios/*/seed/*.sql` files are applied in sorted order.
  - Directory arguments are supported. Pass a pack root or its `seed/` directory.
TEXT
}

if [ ! -d "$DEFAULT_SAMPLE_ROOT" ]; then
  echo "sample root not found: $DEFAULT_SAMPLE_ROOT" >&2
  exit 1
fi

if [ ! -d "$DEFAULT_TEST_SCENARIO_ROOT" ]; then
  echo "test scenario root not found: $DEFAULT_TEST_SCENARIO_ROOT" >&2
  exit 1
fi

active_sample_roots=()
for sample_root in "${DEFAULT_ACTIVE_SAMPLE_ROOTS[@]}"; do
  if [ -d "$sample_root" ]; then
    active_sample_roots+=("$sample_root")
  fi
done

if [ "${#active_sample_roots[@]}" -eq 0 ]; then
  active_sample_roots=("$DEFAULT_SAMPLE_ROOT")
fi

find_sample_pack_dir_by_name() {
  local pack_name="$1"
  local sample_root
  local matches=()

  for sample_root in "${active_sample_roots[@]}"; do
    if [ ! -d "$sample_root" ]; then
      continue
    fi

    while IFS= read -r dir_path; do
      matches+=("$dir_path")
    done < <(find "$sample_root" -mindepth 1 -maxdepth 1 -type d -name "$pack_name" | sort)
  done

  if [ "${#matches[@]}" -gt 1 ]; then
    echo "multiple sample pack directories found: $pack_name" >&2
    exit 1
  fi

  if [ "${#matches[@]}" -eq 1 ]; then
    printf '%s\n' "${matches[0]}"
    return 0
  fi

  return 1
}

scenario="base"
compose_file=""
seed_args=()
for argument in "$@"; do
  case "$argument" in
    --help|-h)
      usage
      exit 0
      ;;
    --scenario=*)
      scenario="${argument#--scenario=}"
      ;;
    --compose-file=*)
      compose_file="${argument#--compose-file=}"
      ;;
    *)
      seed_args+=("$argument")
      ;;
  esac
done

if [ "$scenario" != "base" ] && [ -n "$compose_file" ]; then
  echo "--scenario and --compose-file cannot be used together" >&2
  exit 1
fi

if [ -n "$compose_file" ]; then
  if [ "${compose_file#/}" = "$compose_file" ]; then
    compose_file="$REPO_ROOT/$compose_file"
  fi
fi

compose_stack_args=()
compose_lane="${SAMPLE_PACK_COMPOSE_LANE:-local}"
case "$scenario" in
  base)
    ;;
  01_mtool)
    compose_stack_args+=("--compose-file=$REPO_ROOT/mtool/docker/compose/${scenario}.compose.yaml")
    ;;
  *)
    echo "unsupported scenario: $scenario" >&2
    exit 1
    ;;
esac

if [ -n "$compose_file" ]; then
  compose_stack_args+=("--compose-file=$compose_file")
fi

compose_cmd=(docker compose)
while IFS= read -r resolved_compose_file; do
  [ -n "$resolved_compose_file" ] || continue
  compose_cmd+=(-f "$resolved_compose_file")
done < <(
  bash "$REPO_ROOT/mtool/scripts/list_compose_stack_files.sh" "--lane=$compose_lane" "${compose_stack_args[@]}"
)

seed_files=()
if [ "${#seed_args[@]}" -eq 0 ]; then
  while IFS= read -r file_path; do
    seed_files+=("$file_path")
  done < <(
    {
      for sample_root in "${active_sample_roots[@]}"; do
        if [ -d "$sample_root" ]; then
          find "$sample_root" -mindepth 2 -maxdepth 3 -type f -path '*/seed/*.sql'
        fi
      done
      find "$DEFAULT_TEST_SCENARIO_ROOT" -mindepth 2 -maxdepth 3 -type f -path '*/seed/*.sql'
    } | sort
  )
else
  for argument in "${seed_args[@]}"; do
    if [ -f "$argument" ]; then
      seed_files+=("$argument")
      continue
    fi

    if [ -d "$argument" ]; then
      if [ -d "$argument/seed" ]; then
        while IFS= read -r file_path; do
          seed_files+=("$file_path")
        done < <(find "$argument/seed" -type f -name '*.sql' | sort)
        continue
      fi

      while IFS= read -r file_path; do
        seed_files+=("$file_path")
      done < <(find "$argument" -type f -name '*.sql' | sort)
      continue
    fi

    if [ -f "$DEFAULT_SAMPLE_ROOT/$argument" ]; then
      seed_files+=("$DEFAULT_SAMPLE_ROOT/$argument")
      continue
    fi

    if [ -f "$DEFAULT_TEST_SCENARIO_ROOT/$argument" ]; then
      seed_files+=("$DEFAULT_TEST_SCENARIO_ROOT/$argument")
      continue
    fi

    sample_pack_dir="$(find_sample_pack_dir_by_name "$argument" || true)"
    if [ -n "$sample_pack_dir" ] && [ -d "$sample_pack_dir/seed" ]; then
      while IFS= read -r file_path; do
        seed_files+=("$file_path")
      done < <(find "$sample_pack_dir/seed" -type f -name '*.sql' | sort)
      continue
    fi

    if [ -d "$DEFAULT_SAMPLE_ROOT/$argument/seed" ]; then
      while IFS= read -r file_path; do
        seed_files+=("$file_path")
      done < <(find "$DEFAULT_SAMPLE_ROOT/$argument/seed" -type f -name '*.sql' | sort)
      continue
    fi

    if [ -d "$DEFAULT_TEST_SCENARIO_ROOT/$argument/seed" ]; then
      while IFS= read -r file_path; do
        seed_files+=("$file_path")
      done < <(find "$DEFAULT_TEST_SCENARIO_ROOT/$argument/seed" -type f -name '*.sql' | sort)
      continue
    fi

    if [ -d "$DEFAULT_SAMPLE_ROOT/$argument" ]; then
      while IFS= read -r file_path; do
        seed_files+=("$file_path")
      done < <(find "$DEFAULT_SAMPLE_ROOT/$argument" -type f -name '*.sql' | sort)
      continue
    fi

    if [ -d "$DEFAULT_TEST_SCENARIO_ROOT/$argument" ]; then
      while IFS= read -r file_path; do
        seed_files+=("$file_path")
      done < <(find "$DEFAULT_TEST_SCENARIO_ROOT/$argument" -type f -name '*.sql' | sort)
      continue
    fi

    echo "sample seed file not found: $argument" >&2
    exit 1
  done
fi

if [ "${#seed_files[@]}" -eq 0 ]; then
  echo "no sample seed files found" >&2
  exit 1
fi

config_store_driver="$("${compose_cmd[@]}" exec -T web-admin php -r 'require "/var/www/mtool/app/config.php"; $app = app_load_config(); echo $app["config_db"]["driver"] ?? "mysql";' 2>/dev/null || echo mysql)"
if [ "$config_store_driver" = "sqlite" ]; then
  container_seed_files=()
  for seed_file in "${seed_files[@]}"; do
    seed_file_abs="$seed_file"
    if [ "${seed_file_abs#/}" = "$seed_file_abs" ]; then
      seed_file_abs="$REPO_ROOT/$seed_file_abs"
    fi

    case "$seed_file_abs" in
      "$REPO_ROOT"/*)
        container_seed_files+=("/var/www/${seed_file_abs#"$REPO_ROOT"/}")
        ;;
      *)
        echo "SQLite sample seed must be under repo root: $seed_file" >&2
        exit 1
        ;;
    esac
  done

  printf 'applying sample seed [sqlite:%s]: %s\n' "$scenario" "${container_seed_files[*]}"
  "${compose_cmd[@]}" exec -T web-admin php /var/www/mtool/scripts/apply_config_sample_seed_sqlite.php \
    --requested-by=apply_config_sample_seed.sh \
    "${container_seed_files[@]}"
  echo "applied ${#seed_files[@]} sample seed file(s)"
  exit 0
fi

for seed_file in "${seed_files[@]}"; do
  echo "applying sample seed [$scenario]: $seed_file"
  "${compose_cmd[@]}" exec -T db-config sh -lc 'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" "$MARIADB_DATABASE"' < "$seed_file"
done

echo "applied ${#seed_files[@]} sample seed file(s)"
