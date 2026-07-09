# First Sample UI Metadata Extraction Spike

English companion:
This report records #547, the first readonly no-code metadata extraction for the selected existing sample UI target, `sample18-mini-task-board-demo`.

## Summary

- Status: `DONE`
- Date: 2026-07-09
- Target: `sample18-mini-task-board-demo`
- Push: not performed.

## What Changed

- Added readonly `task_card` shared contract metadata for sample18.
- Added `NO-CODE-RUNTIME` source output definition for sample18.
- Extended the sample18 pack checker to generate and publish the no-code runtime artifact separately from the existing reference-output comparison.
- Added assertions for:
  - `no-code-screen-definition-v0`;
  - `no-code-runtime-v0`;
  - `task_card` contract key;
  - list/detail/form screen types;
  - readonly field keys matching the golden fixture data shape.

## Boundary

- The existing `/samples/sample18-task-board` route is not replaced.
- The existing `DATACLASS-PHP`, `DBACCESS-PHP`, `HTML-PAGE`, and `OPENAPI-JSON` reference comparison remains unchanged.
- The first sample18 no-code metadata slice is readonly: no generated action execution, route mutation, or button enablement is added.
- Browser smoke remains an outer gate for later comparison.

## Next

- #548 becomes `ACTIVE_NEXT`: render the sample18 readonly no-code preview and compare it against the golden fixture.

## Verification

- `php -l mtool/scripts/lib/sample18_mini_task_board_demo_check.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- Focused sample18 pack PHPUnit: `OK (2 tests, 62 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 377, Assertions: 11700, Skipped: 1.`
- `git diff --check`
