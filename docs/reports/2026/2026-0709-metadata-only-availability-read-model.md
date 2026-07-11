# Metadata-Only Availability Read Model

Date: 2026-07-09

Status: `DONE`

## Summary

#540 adds a metadata-only availability read model to no-code custom operations and extension slot action items. The model exposes availability state and route-boundary context for #541 UI preview work without enabling generated button execution.

## Changes

- Added `availability_read_model` to normalized custom operations.
- Added the same read model to extension slot action items derived from custom operations.
- Exposed:
  - `operation_availability`;
  - `availability_state`;
  - `preflight_result`;
  - `availability_reason`;
  - `execution_mode`;
  - `route_boundary`;
  - `generated_button_enabled`.
- Kept `generated_button_enabled` fixed to `false`.
- Added dogfooding inspection summary fields for availability states and execution modes.
- Added contract/runtime assertions to `NoCodeScreenDefinitionTest`.
- Added the missing `English companion:` marker to the durable no-code UI testing design note so permanent docs satisfy the docs entrance contract.

## Boundary

- This is metadata-only.
- Runtime HTML buttons remain disabled.
- No generated button execution route is enabled.
- No publish request execution is enabled.
- `plan_only_ready` remains a readiness state for later lanes, not a button enablement signal.
- Push is not performed.

## Verification

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- `php -l tests/Integration/NoCodeScreenDefinitionTest.php`
- Focused PHPUnit: `OK (8 tests, 183 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 376, Assertions: 11631, Skipped: 1.`
- `git diff --check`
