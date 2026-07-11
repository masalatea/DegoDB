# 2026-0711 Readiness Metadata Screen-Definition Carry-Through

Status: `FIRST_SLICE_DONE`

## Summary

#705 carries Sample18 generated-submit readiness metadata into `screen-definition.json` action metadata and submit binding metadata.

The carry-through remains read-only. It does not enable generated submit, does not dispatch mutation, and keeps `can_submit=false` for generated action metadata.

## Changes

- `no_code_screen_definition.php` now attaches the Sample18 generated-submit readiness snapshot to Sample18 task card contract metadata.
- Managed actions expose `readiness_metadata` with:
  - `action_key`
  - `operation_key`
  - `route_compatible`
  - `readiness_state`
  - `availability_candidate`
  - `can_submit`
  - `failure_reasons`
  - `executor_config_status`
- Submit binding metadata now carries:
  - `readiness_state`
  - `availability_candidate`
  - `can_submit`
  - `executor_config_status`

## Behavior

For the default Sample18 generated submit config:

- route-compatible managed actions are `candidate_ready`
- `availability_candidate=true`
- `can_submit=false`
- `executor_config_status=disabled`

This keeps UI metadata useful while preserving the current fail-closed behavior.

## Verification

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`
  - OK: 30 tests, 1817 assertions
- Focused `NoCodeScreenDefinitionTest`
  - OK: 8 tests, 195 assertions

Push has not been performed.
