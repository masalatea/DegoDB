#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

SAMPLE_PROJECT_KEY="${SAMPLE_PROJECT_KEY:-SAMPLE28}"
SAMPLE_PROFILE="${SAMPLE_PROFILE:-sample28}"
SAMPLE_PACK_DIR="${SAMPLE_PACK_DIR:-sample/tutorials/sample28-no-code-data-app-mvp}"
SAMPLE_INTEGRATION_TEST="${SAMPLE_INTEGRATION_TEST:-/var/www/tests/Integration/Sample28NoCodeDataAppMvpTest.php}"
SMOKE_OUTPUT_DIR="${SMOKE_OUTPUT_DIR:-output/playwright/no-code-public-runtime}"
RUN_ENDPOINT_SMOKE="${RUN_ENDPOINT_SMOKE:-1}"
RUN_OUTBOX_PROCESS_SMOKE="${RUN_OUTBOX_PROCESS_SMOKE:-1}"
RUNTIME_FILTER_DOM_ONLY="${RUNTIME_FILTER_DOM_ONLY:-0}"
ADMIN_HTTP_PORT="${ADMIN_HTTP_PORT:-18291}"
LAB_HTTP_PORT="${LAB_HTTP_PORT:-18292}"
CONFIG_DB_HOST_PORT="${CONFIG_DB_HOST_PORT:-43291}"
LAB_DB_HOST_PORT="${LAB_DB_HOST_PORT:-43292}"
ADMIN_AUTH_STUB_ROLES="${ADMIN_AUTH_STUB_ROLES:-}"
ADMIN_AUTH_STUB_SCOPES="${ADMIN_AUTH_STUB_SCOPES:-}"
BASE_URL="${BASE_URL:-http://127.0.0.1:${ADMIN_HTTP_PORT}}"
ALIAS_KEY="${ALIAS_KEY:-stable}"
KEEP_STACK_RUNNING="${KEEP_SAMPLE_STACK_RUNNING:-0}"

compose_cmd=(
  env
  "ADMIN_HTTP_PORT=${ADMIN_HTTP_PORT}"
  "LAB_HTTP_PORT=${LAB_HTTP_PORT}"
  "CONFIG_DB_HOST_PORT=${CONFIG_DB_HOST_PORT}"
  "LAB_DB_HOST_PORT=${LAB_DB_HOST_PORT}"
  docker
  compose
)

if [ -n "$ADMIN_AUTH_STUB_ROLES" ]; then
  compose_cmd=(env "ADMIN_AUTH_STUB_ROLES=${ADMIN_AUTH_STUB_ROLES}" "${compose_cmd[@]:1}")
fi

if [ -n "$ADMIN_AUTH_STUB_SCOPES" ]; then
  compose_cmd=(env "ADMIN_AUTH_STUB_SCOPES=${ADMIN_AUTH_STUB_SCOPES}" "${compose_cmd[@]:1}")
fi

while IFS= read -r compose_file; do
  [ -n "$compose_file" ] || continue
  compose_cmd+=(-f "$compose_file")
done < <(
  bash "$REPO_ROOT/mtool/scripts/list_compose_stack_files.sh" \
    "--compose-file=${SAMPLE_PACK_DIR}/compose.yaml" \
    "--compose-file=sample/_pack-support/sample-pack-lifecycle.compose.yaml"
)

cleanup() {
  if [ "$KEEP_STACK_RUNNING" = "1" ]; then
    return 0
  fi

  "${compose_cmd[@]}" down -v >/dev/null 2>&1 || true
}

trap cleanup EXIT

wait_for_admin_health() {
  local attempt
  local last_output=""

  for attempt in $(seq 1 30); do
    if last_output="$("${compose_cmd[@]}" exec -T web-admin sh -lc 'curl -fsS http://127.0.0.1/health' 2>&1)"; then
      echo "admin health ok"
      return 0
    fi
    sleep 2
  done

  echo "admin health failed" >&2
  echo "$last_output" >&2
  exit 1
}

json_field() {
  php -r '
$payload = json_decode(stream_get_contents(STDIN), true);
if (!is_array($payload)) {
    fwrite(STDERR, "invalid JSON payload" . PHP_EOL);
    exit(1);
}
$key = $argv[1] ?? "";
$value = $payload[$key] ?? "";
if (!is_scalar($value)) {
    fwrite(STDERR, "missing scalar JSON field: " . $key . PHP_EOL);
    exit(1);
}
echo (string) $value;
' "$1"
}

assert_cache_control() {
  local path="$1"
  local expected="$2"
  local actual

  actual="$(curl -fsS -D - -o /dev/null "${BASE_URL}${path}" | awk 'BEGIN{IGNORECASE=1} /^Cache-Control:/ {sub(/\r$/, ""); sub(/^Cache-Control:[[:space:]]*/, ""); print; exit}')"
  if [ "$actual" != "$expected" ]; then
    echo "unexpected Cache-Control for ${path}: [${actual}], expected [${expected}]" >&2
    exit 1
  fi
}

cd "$REPO_ROOT"

cleanup
"${compose_cmd[@]}" up -d --build
wait_for_admin_health

bash "$REPO_ROOT/mtool/scripts/apply_config_sample_seed.sh" \
  "--compose-file=${SAMPLE_PACK_DIR}/compose.yaml" \
  "${SAMPLE_PACK_DIR}/seed"

"${compose_cmd[@]}" exec -T -e MTOOL_GENERATED_NAME_POLICY=physical-logical-v1 web-admin phpunit \
  --configuration /var/www/tests/phpunit.xml \
  "$SAMPLE_INTEGRATION_TEST"

artifact_key="$("${compose_cmd[@]}" exec -T -e "SAMPLE_PROJECT_KEY=${SAMPLE_PROJECT_KEY}" web-admin php -r '
require "/var/www/mtool/app/bootstrap.php";
require "/var/www/mtool/app/project_output_service.php";
$projectKey = getenv("SAMPLE_PROJECT_KEY") ?: "SAMPLE28";
$result = app_project_output_list(app_bootstrap(), $projectKey, "NO-CODE-RUNTIME");
if (!($result["ok"] ?? false) || empty($result["items"][0]["artifact_key"])) {
    fwrite(STDERR, json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL);
    exit(1);
}
echo $result["items"][0]["artifact_key"];
')"

create_revision_args=(
  "--project-key=${SAMPLE_PROJECT_KEY}" \
  "--artifact-key=${artifact_key}" \
  "--alias-key=${ALIAS_KEY}" \
  --requested-by=public-runtime-browser-smoke
)
if [ "$RUNTIME_FILTER_DOM_ONLY" = "1" ]; then
  create_revision_args+=(--allow-empty-action-surface-for-dom-preflight)
fi

public_json="$("${compose_cmd[@]}" exec -T -e "SAMPLE_PROJECT_KEY=${SAMPLE_PROJECT_KEY}" web-admin php /var/www/mtool/scripts/create_no_code_public_runtime_smoke_revision.php "${create_revision_args[@]}")"

artifact_path="$(printf '%s' "$public_json" | json_field artifact_url)"
current_path="$(printf '%s' "$public_json" | json_field current_url)"
alias_path="$(printf '%s' "$public_json" | json_field alias_url)"

assert_cache_control "$artifact_path" "public, max-age=31536000, immutable"
assert_cache_control "$current_path" "no-store"
assert_cache_control "$alias_path" "no-store"

if [ "$RUNTIME_FILTER_DOM_ONLY" = "1" ]; then
  node mtool/scripts/check_no_code_runtime_preview_ui_smoke.js \
    "--profile=${SAMPLE_PROFILE}" \
    "--url=${BASE_URL}${current_path}" \
    --execution-binding=required \
    --execution-url-contains=/current/execute.json \
    --submit-probe=enabled-real-fetch \
    --runtime-filter-dom-only \
    "--output-dir=${SMOKE_OUTPUT_DIR}"

  printf '%s\n' "$public_json"
  exit 0
fi

node mtool/scripts/check_no_code_runtime_preview_ui_smoke.js \
  "--profile=${SAMPLE_PROFILE}" \
  "--url=${BASE_URL}${artifact_path}" \
  --execution-binding=none \
  "--output-dir=${SMOKE_OUTPUT_DIR}"

node mtool/scripts/check_no_code_runtime_preview_ui_smoke.js \
  "--profile=${SAMPLE_PROFILE}" \
  "--url=${BASE_URL}${current_path}" \
  --execution-binding=required \
  --execution-url-contains=/current/execute.json \
  --submit-probe=enabled-real-fetch \
  "--output-dir=${SMOKE_OUTPUT_DIR}"

node mtool/scripts/check_no_code_runtime_preview_ui_smoke.js \
  "--profile=${SAMPLE_PROFILE}" \
  "--url=${BASE_URL}${current_path}" \
  --execution-binding=required \
  --execution-url-contains=/current/execute.json \
  --submit-probe=enabled-fetch-stub \
  --status-probe=stub-done \
  "--output-dir=${SMOKE_OUTPUT_DIR}"

node mtool/scripts/check_no_code_runtime_preview_ui_smoke.js \
  "--profile=${SAMPLE_PROFILE}" \
  "--url=${BASE_URL}${current_path}" \
  --execution-binding=required \
  --execution-url-contains=/current/execute.json \
  --submit-probe=enabled-fetch-stub \
  --status-probe=stub-failed \
  "--output-dir=${SMOKE_OUTPUT_DIR}"

node mtool/scripts/check_no_code_runtime_preview_ui_smoke.js \
  "--profile=${SAMPLE_PROFILE}" \
  "--url=${BASE_URL}${alias_path}" \
  --execution-binding=required \
  "--execution-url-contains=/alias/${ALIAS_KEY}/execute.json" \
  --submit-probe=enabled-real-fetch \
  "--output-dir=${SMOKE_OUTPUT_DIR}"

if [ "$RUN_ENDPOINT_SMOKE" = "1" ]; then
  php mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php \
    "--profile=${SAMPLE_PROFILE}" \
    "--base-url=${BASE_URL}" \
    "--current-path=${current_path}" \
    "--alias-path=${alias_path}" \
    --pretty
fi

if [ "$RUN_OUTBOX_PROCESS_SMOKE" = "1" ]; then
  "${compose_cmd[@]}" exec -T web-admin php /var/www/mtool/scripts/check_sample28_no_code_runtime_outbox_process_smoke.php \
    "--profile=${SAMPLE_PROFILE}" \
    --pretty
fi

printf '%s\n' "$public_json"
