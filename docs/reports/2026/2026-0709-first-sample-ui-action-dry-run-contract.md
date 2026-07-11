# First Sample UI Action Dry-Run Contract

Status: `DONE`

Plan item: #549 first sample UI action dry-run contract

## Summary

`sample18-mini-task-board-demo` now carries no-code dry-run action metadata for the existing task board mutations without enabling generated mutation buttons or replacing the curated route.

## Scope

- Added sample18 `task_card` custom operation metadata for:
  - `create_task_card`
  - `update_task_card`
  - `complete_task_card`
  - `reopen_task_card`
  - `delete_task_card`
- Declared each operation as `availability=disabled` with `generated_button_enabled=false` through the existing availability read model.
- Declared the route boundary as `POST /samples/sample18-task-board`, guarded by `web_lab_login`, with CSRF and route failure modes recorded.
- Rendered the action metadata through the generated no-code runtime operator action panel as disabled dry-run buttons.
- Kept the existing sample18 hand-coded page as the only mutation owner.

## Verification

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l mtool/scripts/lib/sample18_mini_task_board_demo_check.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`

## Boundary

This slice only records and verifies action metadata. It does not add a no-code mutation dispatcher, does not submit POSTs from generated UI, and does not replace `/samples/sample18-task-board`.

## Next

#550 should decide whether sample18 is credible enough to count as the first L1 existing sample UI no-code entry, then record the remaining gaps before moving to a dedicated test harness or another sample.
