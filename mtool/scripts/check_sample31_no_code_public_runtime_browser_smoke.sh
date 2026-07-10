#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

export SAMPLE_PROJECT_KEY="${SAMPLE_PROJECT_KEY:-SAMPLE31}"
export SAMPLE_PROFILE="${SAMPLE_PROFILE:-sample31}"
export SAMPLE_PACK_DIR="${SAMPLE_PACK_DIR:-sample/tutorials/sample31-no-code-inventory-request-demo}"
export SAMPLE_INTEGRATION_TEST="${SAMPLE_INTEGRATION_TEST:-/var/www/tests/Integration/Sample31NoCodeInventoryRequestDemoTest.php}"
export SMOKE_OUTPUT_DIR="${SMOKE_OUTPUT_DIR:-output/playwright/no-code-public-runtime-sample31}"
export ADMIN_HTTP_PORT="${ADMIN_HTTP_PORT:-18311}"
export LAB_HTTP_PORT="${LAB_HTTP_PORT:-18312}"
export CONFIG_DB_HOST_PORT="${CONFIG_DB_HOST_PORT:-43311}"
export LAB_DB_HOST_PORT="${LAB_DB_HOST_PORT:-43312}"
export ADMIN_AUTH_STUB_ROLES="${ADMIN_AUTH_STUB_ROLES:-admin,config,editor}"
export ADMIN_AUTH_STUB_SCOPES="${ADMIN_AUTH_STUB_SCOPES:-inventory_request:write}"
export RUN_ENDPOINT_SMOKE="${RUN_ENDPOINT_SMOKE:-1}"
export RUN_OUTBOX_PROCESS_SMOKE="${RUN_OUTBOX_PROCESS_SMOKE:-1}"

exec bash "$SCRIPT_DIR/check_sample28_no_code_public_runtime_browser_smoke.sh"
