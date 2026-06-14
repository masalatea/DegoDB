#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

DEFAULT_PACK_NAMES=(
  sample51-runtime-sql-server
)

usage() {
  cat <<'EOF'
usage: check_sample_pack_runtime_smoke.sh [--pack=PACK_NAME] [--all]

Run a heavier representative smoke for active runtime sample packs by starting the
pack, applying its seed, checking /health on both sites, and verifying that the
pack project row exists in db-config. Resource-bearing packs also validate the
runtime language resource file catalog loader from inside web-admin.

Options:
  --pack=PACK_NAME   Validate only one runtime pack from app_sample_pack_runtime_pack_names()
  --all              Validate every active runtime pack
  --help             Show this help
EOF
}

selected_pack=""
run_all=0

for argument in "$@"; do
  case "$argument" in
    --pack=*)
      selected_pack="${argument#--pack=}"
      ;;
    --all)
      run_all=1
      ;;
    --help)
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

if [ -n "$selected_pack" ] && [ "$run_all" -eq 1 ]; then
  echo "--pack and --all cannot be used together" >&2
  exit 1
fi

if ! command -v php >/dev/null 2>&1; then
  echo "php is required to read the sample pack catalog" >&2
  exit 1
fi

current_pack=""
current_relative_path=""
current_project_key=""
current_has_resources="0"
current_admin_port=""
current_lab_port=""
current_config_db_port=""
current_lab_db_port=""

build_current_compose_cmd() {
  current_compose_cmd=(
    env
    "ADMIN_HTTP_PORT=$current_admin_port"
    "LAB_HTTP_PORT=$current_lab_port"
    "CONFIG_DB_HOST_PORT=$current_config_db_port"
    "LAB_DB_HOST_PORT=$current_lab_db_port"
    docker
    compose
  )

  while IFS= read -r compose_file; do
    [ -n "$compose_file" ] || continue
    current_compose_cmd+=(-f "$compose_file")
  done < <(
    bash "$REPO_ROOT/mtool/scripts/list_compose_stack_files.sh" \
      "--compose-file=sample/$current_relative_path/compose.yaml" \
      "--compose-file=sample/_pack-support/sample-pack-lifecycle.compose.yaml"
  )
}

cleanup_current_pack() {
  if [ -z "$current_pack" ] || [ -z "$current_relative_path" ]; then
    return 0
  fi

  build_current_compose_cmd
  "${current_compose_cmd[@]}" down -v >/dev/null 2>&1 || true

  current_pack=""
  current_relative_path=""
  current_project_key=""
  current_has_resources="0"
  current_admin_port=""
  current_lab_port=""
  current_config_db_port=""
  current_lab_db_port=""
}

trap cleanup_current_pack EXIT

runtime_pack_names() {
  cd "$REPO_ROOT"
  php -r '
require "mtool/app/sample_pack_catalog.php";
foreach (app_sample_pack_runtime_pack_names() as $packName) {
    echo $packName, PHP_EOL;
}
'
}

resolve_pack_info() {
  local pack_name="$1"
  local output

  output="$(
    cd "$REPO_ROOT"
    php -r '
require "mtool/app/sample_pack_catalog.php";
$packName = $argv[1];
if (!in_array($packName, app_sample_pack_runtime_pack_names(), true)) {
    fwrite(STDERR, "runtime pack not found: " . $packName . PHP_EOL);
    exit(1);
}
$projectKey = app_sample_pack_project_key($packName);
$relativePath = app_sample_pack_relative_path($packName);
$hasResources = is_dir(app_sample_pack_resource_root($packName)) ? "1" : "0";
echo $projectKey, "\t", $relativePath, "\t", $hasResources, PHP_EOL;
' "$pack_name"
  )"

  IFS=$'\t' read -r current_project_key current_relative_path current_has_resources <<< "$output"
}

set_current_ports() {
  local pack_name="$1"
  local pack_number="${pack_name#sample}"
  pack_number="${pack_number%%-*}"
  if ! [[ "$pack_number" =~ ^[0-9]+$ ]]; then
    echo "failed to derive sample number from pack name: $pack_name" >&2
    exit 1
  fi

  current_admin_port="$((18100 + pack_number * 10 + 1))"
  current_lab_port="$((18100 + pack_number * 10 + 2))"
  current_config_db_port="$((43100 + pack_number * 10 + 1))"
  current_lab_db_port="$((43100 + pack_number * 10 + 2))"
}

compose_cmd() {
  build_current_compose_cmd
  "${current_compose_cmd[@]}" "$@"
}

wait_for_health() {
  local service_name="$1"
  local expected_site="$2"
  local last_output=""
  local last_error=""
  local attempt=0

  for ((attempt = 1; attempt <= 30; attempt++)); do
    last_error=""
    if last_output="$(compose_cmd exec -T "$service_name" sh -lc 'curl -fsS http://127.0.0.1/health' 2>&1)"; then
      if printf '%s' "$last_output" | php -r '
$payload = json_decode(stream_get_contents(STDIN), true);
if (!is_array($payload)) {
    fwrite(STDERR, "health payload is not valid JSON" . PHP_EOL);
    exit(1);
}
if (($payload["ok"] ?? false) !== true) {
    fwrite(STDERR, "health payload ok=false" . PHP_EOL);
    exit(1);
}
if (($payload["site"] ?? "") !== $argv[1]) {
    fwrite(STDERR, "unexpected health site: " . ($payload["site"] ?? "") . PHP_EOL);
    exit(1);
}
if (($payload["request"]["path"] ?? "") !== "/health") {
    fwrite(STDERR, "unexpected health path" . PHP_EOL);
    exit(1);
}
if (($payload["database"]["ok"] ?? false) !== true) {
    fwrite(STDERR, "database not healthy" . PHP_EOL);
    exit(1);
}
' "$expected_site"; then
        echo "health ok [$current_pack] $service_name"
        return 0
      fi
      last_error="health payload validation failed"
    else
      last_error="$last_output"
    fi

    sleep 2
  done

  echo "health check failed for [$current_pack] $service_name after $attempt attempts" >&2
  if [ -n "$last_error" ]; then
    echo "$last_error" >&2
  fi
  if [ -n "$last_output" ]; then
    echo "$last_output" >&2
  fi
  exit 1
}

query_db_config_single_value() {
  local sql="$1"

  compose_cmd exec -T db-config sh -lc \
    "mariadb -N -B -uroot -p\"\$MARIADB_ROOT_PASSWORD\" \"\$MARIADB_DATABASE\" -e \"$sql\""
}

apply_current_pack_seed() {
  env \
    "ADMIN_HTTP_PORT=$current_admin_port" \
    "LAB_HTTP_PORT=$current_lab_port" \
    "CONFIG_DB_HOST_PORT=$current_config_db_port" \
    "LAB_DB_HOST_PORT=$current_lab_db_port" \
    bash "$REPO_ROOT/mtool/scripts/apply_config_sample_seed.sh" \
      "--compose-file=sample/$current_relative_path/compose.yaml" \
      "sample/$current_relative_path/seed"
}

check_project_seed_loaded() {
  local project_slug
  local source_output_count

  project_slug="$(query_db_config_single_value "SELECT slug FROM projects WHERE project_key = '$current_project_key' LIMIT 1")"
  if [ "$project_slug" != "$current_pack" ]; then
    echo "unexpected project slug for [$current_pack]: [$project_slug]" >&2
    exit 1
  fi

  source_output_count="$(query_db_config_single_value "SELECT COUNT(*) FROM project_source_outputs WHERE project_id = (SELECT id FROM projects WHERE project_key = '$current_project_key' LIMIT 1)")"
  if ! [[ "$source_output_count" =~ ^[0-9]+$ ]] || [ "$source_output_count" -lt 1 ]; then
    echo "source output seed did not load for [$current_pack]: [$source_output_count]" >&2
    exit 1
  fi

  echo "seed ok [$current_pack] project_key=$current_project_key source_outputs=$source_output_count"
}

check_resource_loader_if_present() {
  if [ "$current_has_resources" != "1" ]; then
    return 0
  fi

  compose_cmd exec -T web-admin php -r '
require "/var/www/mtool/app/project_language_resource_catalog_loader.php";
$projectKey = $argv[1];
$result = app_project_language_resource_load_file_catalog($projectKey);
if (($result["exists"] ?? false) !== true || ($result["ok"] ?? false) !== true) {
    fwrite(STDERR, json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL);
    exit(1);
}
$manifest = is_array($result["manifest"] ?? null) ? $result["manifest"] : [];
if (($manifest["project_key"] ?? "") !== $projectKey) {
    fwrite(STDERR, "unexpected manifest project_key" . PHP_EOL);
    exit(1);
}
' "$current_project_key"

  echo "resource loader ok [$current_pack] project_key=$current_project_key"
}

run_pack_smoke() {
  local pack_name="$1"

  cleanup_current_pack
  current_pack="$pack_name"
  resolve_pack_info "$pack_name"
  set_current_ports "$pack_name"

  echo "starting runtime smoke [$current_pack] [$current_relative_path]"
  compose_cmd up -d

  wait_for_health web-admin admin
  wait_for_health web-lab lab

  apply_current_pack_seed
  check_project_seed_loaded
  check_resource_loader_if_present
}

pack_names=()
if [ -n "$selected_pack" ]; then
  pack_names=("$selected_pack")
elif [ "$run_all" -eq 1 ]; then
  while IFS= read -r pack_name; do
    [ -n "$pack_name" ] || continue
    pack_names+=("$pack_name")
  done < <(runtime_pack_names)
else
  pack_names=("${DEFAULT_PACK_NAMES[@]}")
fi

validated_count=0
for pack_name in "${pack_names[@]}"; do
  run_pack_smoke "$pack_name"
  validated_count=$((validated_count + 1))
done

cleanup_current_pack
echo "validated runtime sample pack smoke: $validated_count pack(s)"
