#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
RUN="$ROOT/sample/tutorials/sample18-mini-task-board-demo/run.sh"
RUN_ID="${SAMPLE18_TRANSACTION_SMOKE_RUN_ID:-$(date +%Y%m%d%H%M%S)-$$}"
if [[ ! "$RUN_ID" =~ ^[A-Za-z0-9_-]+$ ]]; then
  echo "SAMPLE18_TRANSACTION_SMOKE_RUN_ID contains unsupported characters." >&2
  exit 2
fi
export COMPOSE_PROJECT_NAME="sample18-transaction-smoke-${RUN_ID}"
export ADMIN_HTTP_PORT="${ADMIN_HTTP_PORT:-18371}"
export LAB_HTTP_PORT="${LAB_HTTP_PORT:-18372}"
export CONFIG_DB_HOST_PORT="${CONFIG_DB_HOST_PORT:-43371}"
export LAB_DB_HOST_PORT="${LAB_DB_HOST_PORT:-43372}"
export MTOOL_SAMPLE18_GENERATED_SUBMIT_MUTATION_ENABLED=1
export MTOOL_SAMPLE18_GENERATED_SUBMIT_EXECUTOR_ENABLED=1

FIXTURE_REL="work/tmp/sample18-failure-runtime-${RUN_ID}"
FIXTURE_HOST="$ROOT/$FIXTURE_REL"
FIXTURE_CONTAINER="/var/www/$FIXTURE_REL"
DB_CONTAINER="${COMPOSE_PROJECT_NAME}-db-config-1"

cleanup() {
  "$RUN" reset >/dev/null 2>&1 || true
  rm -rf "$FIXTURE_HOST"
}
trap cleanup EXIT

wait_for_lab() {
  for _attempt in $(seq 1 60); do
    if curl -fsS "http://127.0.0.1:${LAB_HTTP_PORT}/health" >/dev/null 2>&1; then
      return
    fi
    sleep 1
  done
  echo "Sample18 lab did not become healthy." >&2
  return 1
}

wait_for_config_db() {
  for _attempt in $(seq 1 60); do
    if docker exec "$DB_CONTAINER" sh -lc 'mariadb-admin ping -h 127.0.0.1 -uroot -p"$MARIADB_ROOT_PASSWORD" --silent' >/dev/null 2>&1; then
      return
    fi
    sleep 1
  done
  echo "Sample18 config DB did not become healthy." >&2
  return 1
}

db_count_title() {
  local title="$1"
  docker exec "$DB_CONTAINER" sh -lc \
    "mariadb -N -B -u\"\$MARIADB_USER\" -p\"\$MARIADB_PASSWORD\" \"\$MARIADB_DATABASE\" -e \"SELECT COUNT(*) FROM task_card WHERE title = '${title}'\""
}

db_insert_pending_title() {
  local title="$1"
  docker exec "$DB_CONTAINER" sh -lc \
    "mariadb -N -B -u\"\$MARIADB_USER\" -p\"\$MARIADB_PASSWORD\" \"\$MARIADB_DATABASE\" -e \"INSERT INTO task_card (title, body, status, assigned_to, priority, due_date, updated_at) VALUES ('${title}', 'Sample18 lifecycle smoke.', 'doing', 'Transaction Smoke', 17, CURDATE(), NOW()); SELECT LAST_INSERT_ID();\""
}

db_count_completed_title() {
  local title="$1"
  docker exec "$DB_CONTAINER" sh -lc \
    "mariadb -N -B -u\"\$MARIADB_USER\" -p\"\$MARIADB_PASSWORD\" \"\$MARIADB_DATABASE\" -e \"SELECT COUNT(*) FROM task_card WHERE title = '${title}' AND status = 'done' AND completed_at IS NOT NULL\""
}

db_delete_title() {
  local title="$1"
  docker exec "$DB_CONTAINER" sh -lc \
    "mariadb -u\"\$MARIADB_USER\" -p\"\$MARIADB_PASSWORD\" \"\$MARIADB_DATABASE\" -e \"DELETE FROM task_card WHERE title = '${title}'\"" >/dev/null
}

run_probe() {
  local expected="$1"
  local title="$2"
  php "$ROOT/mtool/scripts/probe_sample18_guarded_submit_http.php" \
    "$expected" "$title" \
    "--lab-base-url=http://127.0.0.1:${LAB_HTTP_PORT}" \
    "--lab-user=${LAB_AUTH_STUB_USER:-lab-local}" \
    "--lab-password=${LAB_AUTH_STUB_PASSWORD:-change-this-lab-password}"
}

php "$ROOT/mtool/scripts/create_sample18_failure_runtime_reference.php" \
  "$ROOT/sample/tutorials/sample18-mini-task-board-demo/reference" \
  "$FIXTURE_HOST" >/dev/null

COMMIT_TITLE="Transaction smoke committed ${RUN_ID}"
COMPLETE_TITLE="Transaction smoke completed ${RUN_ID}"
ROLLBACK_TITLE="Transaction smoke rolled back ${RUN_ID}"

unset MTOOL_SAMPLE18_GENERATED_SUBMIT_RUNTIME_REFERENCE_DIR
"$RUN" up
wait_for_lab
wait_for_config_db
"$RUN" apply-seed
run_probe committed "$COMMIT_TITLE"
test "$(db_count_title "$COMMIT_TITLE")" = "1"
db_delete_title "$COMMIT_TITLE"
COMPLETE_ID="$(db_insert_pending_title "$COMPLETE_TITLE")"
SAMPLE18_TRANSACTION_SMOKE_OPERATION_KEY=complete_task_card \
SAMPLE18_TRANSACTION_SMOKE_TASK_ID="$COMPLETE_ID" \
  run_probe committed "$COMPLETE_TITLE"
test "$(db_count_completed_title "$COMPLETE_TITLE")" = "1"
db_delete_title "$COMPLETE_TITLE"
"$RUN" reset

export MTOOL_SAMPLE18_GENERATED_SUBMIT_RUNTIME_REFERENCE_DIR="$FIXTURE_CONTAINER"
"$RUN" up
wait_for_lab
wait_for_config_db
"$RUN" apply-seed
run_probe rolled_back "$ROLLBACK_TITLE"
test "$(db_count_title "$ROLLBACK_TITLE")" = "0"

printf '{"ok":true,"commit_row_count":1,"rollback_row_count":0,"cleanup":"scheduled"}\n'
