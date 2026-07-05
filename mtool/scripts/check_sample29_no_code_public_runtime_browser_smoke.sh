#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

export SAMPLE_PROJECT_KEY="${SAMPLE_PROJECT_KEY:-SAMPLE29}"
export SAMPLE_PROFILE="${SAMPLE_PROFILE:-sample29}"
export SAMPLE_PACK_DIR="${SAMPLE_PACK_DIR:-sample/tutorials/sample29-no-code-support-case-demo}"
export SAMPLE_INTEGRATION_TEST="${SAMPLE_INTEGRATION_TEST:-/var/www/tests/Integration/Sample29NoCodeSupportCaseDemoTest.php}"
export SMOKE_OUTPUT_DIR="${SMOKE_OUTPUT_DIR:-output/playwright/no-code-public-runtime-sample29}"
export ADMIN_HTTP_PORT="${ADMIN_HTTP_PORT:-18301}"
export LAB_HTTP_PORT="${LAB_HTTP_PORT:-18302}"
export CONFIG_DB_HOST_PORT="${CONFIG_DB_HOST_PORT:-43301}"
export LAB_DB_HOST_PORT="${LAB_DB_HOST_PORT:-43302}"
export ADMIN_AUTH_STUB_ROLES="${ADMIN_AUTH_STUB_ROLES:-admin,config,editor}"
export ADMIN_AUTH_STUB_SCOPES="${ADMIN_AUTH_STUB_SCOPES:-support_case:write}"
export RUN_ENDPOINT_SMOKE="${RUN_ENDPOINT_SMOKE:-1}"
export RUN_OUTBOX_PROCESS_SMOKE="${RUN_OUTBOX_PROCESS_SMOKE:-1}"

exec bash "$SCRIPT_DIR/check_sample28_no_code_public_runtime_browser_smoke.sh"
