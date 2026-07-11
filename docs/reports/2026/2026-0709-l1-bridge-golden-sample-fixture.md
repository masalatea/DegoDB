# L1 Bridge Golden Sample Fixture

English companion:
This report records #546, the first golden fixture for comparing future sample18 no-code output against the existing hand-coded sample UI boundary.

## Summary

- Status: `DONE`
- Date: 2026-07-09
- Target: `sample18-mini-task-board-demo`
- Fixture: `sample/tutorials/sample18-mini-task-board-demo/golden/no-code-ui-golden.json`
- Push: not performed.

## What Was Frozen

- Route path: `/samples/sample18-task-board`
- Source table: `task_card`
- Stable seed rows:
  - `Define first demo prompt`
  - `Create TaskCard metadata`
  - `Publish reference outputs`
  - `Review demo feedback notes`
- DOM contract inputs:
  - title;
  - status filter values;
  - form fields;
  - table columns;
  - create/update/complete/reopen/delete action boundary.

## Fast Evidence

- `Sample18MiniTaskBoardDemoTest` now verifies the golden fixture against:
  - the sample18 table seed SQL;
  - the sample18 lab route source contract;
  - the no-code conversion boundary that generated route replacement and generated button execution remain disabled.

## Boundary

- No generated no-code UI is added in this slice.
- No sample18 route replacement is performed.
- No generated button execution or mutation route is enabled.
- Browser smoke remains an outer gate.

## Next

- #547 becomes `ACTIVE_NEXT`: extract readonly sample18 no-code metadata without replacing the hand-coded route.

## Verification

- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- Focused sample18 pack PHPUnit: `OK (2 tests, 57 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 377, Assertions: 11693, Skipped: 1.`
- `git diff --check`
