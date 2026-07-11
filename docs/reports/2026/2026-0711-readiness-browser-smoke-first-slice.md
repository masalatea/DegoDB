# 2026-0711 Readiness Browser Smoke First Slice

Status: `FIRST_SLICE_DONE`

## Summary

#708 extends the existing Sample18 enabled-candidate browser smoke to verify read-only readiness markers before the smoke temporarily enables candidate actions.

The smoke still uses the existing stubbed generated-submit probe. It does not enable real mutation.

## Changes

- Captured initial readiness markers from browser DOM buttons:
  - `data-action-readiness-state`
  - `data-action-availability-candidate`
  - `data-action-can-submit`
  - `data-action-executor-config-status`
- Captured matching `readiness_metadata` from `window.__noCodeRuntimePreview`.
- Asserted desktop and mobile readiness for `complete_task_card`, `create_task_card`, and `update_task_card`.

## Expected Readiness

For the existing enabled-candidate smoke:

- `readiness_state`: `candidate_ready`
- `availability_candidate`: `true`
- `can_submit`: `false`
- `executor_config_status`: `disabled`

After those read-only checks, the smoke continues to temporarily enable the candidate UI and verifies the stubbed blocked submit response.

## Verification

- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample18-no-code-public-runtime-enabled-candidate-smoke`
  - OK, including desktop and mobile readiness marker probes

Push has not been performed.
