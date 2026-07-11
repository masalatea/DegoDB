# First Sample UI Readonly No-Code Preview

English companion:
This report records #548, the first sample18 readonly no-code runtime preview comparison against the golden fixture.

## Summary

- Status: `DONE`
- Date: 2026-07-09
- Target: `sample18-mini-task-board-demo`
- Push: not performed.

## What Changed

- Added sample18 `task_card` preview rows to the no-code runtime generator.
- Compared generated `runtime-preview.json` list rows against the sample18 golden fixture seed rows.
- Compared generated `runtime-preview.html` against golden row titles and readonly form field markers.
- Kept the generated preview readonly and separate from the hand-coded `/samples/sample18-task-board` route.

## Boundary

- No generated route replacement.
- No generated button execution.
- No action dry-run metadata yet.
- Browser smoke remains an outer gate; the comparison added here is fast JSON/HTML contract coverage.

## Next

- #549 becomes `ACTIVE_NEXT`: describe sample18 actions as disabled/dry-run custom operations with route boundaries before enabling mutation.

## Verification

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `php -l mtool/scripts/lib/sample18_mini_task_board_demo_check.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- Focused sample18 pack PHPUnit: `OK (2 tests, 64 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 377, Assertions: 11702, Skipped: 1.`
- `git diff --check`
