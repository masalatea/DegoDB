#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

export SAMPLE_PROJECT_KEY="${SAMPLE_PROJECT_KEY:-SAMPLE18}"
export SAMPLE_PROFILE="${SAMPLE_PROFILE:-sample18}"
export SAMPLE_PACK_DIR="${SAMPLE_PACK_DIR:-sample/tutorials/sample18-mini-task-board-demo}"
export SAMPLE_INTEGRATION_TEST="${SAMPLE_INTEGRATION_TEST:-/var/www/tests/Integration/Sample18MiniTaskBoardDemoTest.php}"
export SMOKE_OUTPUT_DIR="${SMOKE_OUTPUT_DIR:-output/playwright/no-code-public-runtime-sample18-enabled-candidate}"
export RUN_ENDPOINT_SMOKE="${RUN_ENDPOINT_SMOKE:-0}"
export RUN_OUTBOX_PROCESS_SMOKE="${RUN_OUTBOX_PROCESS_SMOKE:-0}"
export RUNTIME_ENABLED_CANDIDATE_SURFACE="${RUNTIME_ENABLED_CANDIDATE_SURFACE:-1}"

exec bash "$SCRIPT_DIR/check_sample28_no_code_public_runtime_browser_smoke.sh"
