# 2026-0711 Readiness Runtime Preview Carry-Through

Status: `FIRST_SLICE_DONE`

## Summary

#706 carries Sample18 readiness metadata from screen-definition actions into runtime preview JSON and stable HTML button markers.

The runtime preview remains read-only. It does not enable generated defaults, does not change guarded submit behavior, and keeps `can_submit=false`.

## Changes

- Runtime action render metadata now preserves `readiness_metadata`.
- Runtime preview HTML action buttons now expose stable markers:
  - `data-action-readiness-state`
  - `data-action-availability-candidate`
  - `data-action-can-submit`
  - `data-action-executor-config-status`
  - `data-action-readiness-failure-reasons` when present
- Sample18 integration coverage now asserts both runtime-preview JSON and HTML marker carry-through.

## Behavior

For default Sample18 generated-submit actions:

- `readiness_state`: `candidate_ready`
- `availability_candidate`: `true`
- `can_submit`: `false`
- `executor_config_status`: `disabled`

This makes readiness visible to runtime preview consumers without enabling real mutation.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`
  - OK: 30 tests, 1847 assertions

Push has not been performed.
