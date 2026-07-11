# Sample18 Generated Availability-State Fast Contract First Slice

Date: 2026-07-10

Status: `FIRST_SLICE_DONE`

## Summary

#696 adds a fast availability-state contract for sample18 generated no-code actions before any generated defaults change. The runtime HTML now exposes stable action availability markers, and the sample18 integration test verifies both disabled-default and enabled-candidate states.

## Changes

- Added `data-action-availability` to generated runtime action buttons.
- Added `data-action-policy-failed-checks` when policy failed checks are present.
- Extended `Sample18MiniTaskBoardDemoTest` to:
  - keep default generated runtime preview actions at `data-action-availability="disabled"`;
  - apply an in-memory policy overlay where only `create_task_card`, `update_task_card`, and `complete_task_card` become enabled candidates;
  - prove those enabled candidates render with `data-action-enabled="true"`;
  - keep selected-key and missing-input fail-closed assertions in the same fast contract.
- Updated `docs/no-code-ui-testing.md` and `docs/current-plans.md`.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`
  - `OK (28 tests, 1685 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 412, Assertions: 13540, Skipped: 1.`

## Decision

Accept the first fast availability-state contract. Do not change generated defaults from this slice. Promote lane closure next to choose between enabled-candidate browser smoke, route/config readiness hardening, or the first generated default-state change.
