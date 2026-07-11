# Sample18 Enabled-Candidate Browser Smoke First Slice

Date: 2026-07-10

Status: `FIRST_SLICE_DONE`

## Summary

#699 adds a separate sample18 enabled-candidate browser smoke target. The smoke uses a browser-side enabled-candidate overlay and a generated-submit fetch stub so it can verify candidate UI state without real DB mutation or generated default-state changes.

## Changes

- Added `--runtime-enabled-candidate-surface` to `check_no_code_runtime_preview_ui_smoke.js`.
- Added an enabled-candidate browser probe that verifies:
  - `create_task_card`, `update_task_card`, and `complete_task_card` are surfaced as enabled candidates;
  - `data-action-availability="enabled"` and `data-action-enabled="true"` are present after the browser-side overlay;
  - no disabled reasons or policy failed checks remain on enabled candidates;
  - `reopen_task_card` and `delete_task_card` are not enabled candidates;
  - guarded generated-submit feedback renders blocked with `generated_submit_disabled` through a fetch stub.
- Added `check_sample18_no_code_public_runtime_enabled_candidate_smoke.sh`.
- Added `make sample18-no-code-public-runtime-enabled-candidate-smoke`.
- Updated the shared public runtime smoke wrapper to support the enabled-candidate short-circuit.

## Verification

- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `bash -n mtool/scripts/check_sample18_no_code_public_runtime_enabled_candidate_smoke.sh`
- `bash -n mtool/scripts/check_sample28_no_code_public_runtime_browser_smoke.sh`
- `git diff --check`
- `make sample18-no-code-public-runtime-enabled-candidate-smoke`
  - PHPUnit fixture phase: `OK (28 tests, 1685 assertions)`
  - Browser probe: desktop and mobile enabled candidate surfaces passed; guarded generated-submit feedback returned `generated_submit_disabled`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 412, Assertions: 13540, Skipped: 1.`

## Decision

Accept the first UI-only enabled-candidate browser smoke. Do not enable real mutation or change generated defaults from this slice. Promote lane closure next to choose route/config readiness browser coverage, real guarded execution smoke, or server-generated availability overlay design.
