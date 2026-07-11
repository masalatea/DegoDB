# Sample18 Generated Action Surface Metadata First Slice

Plan item: #562 sample18 generated action surface metadata first slice

Status: DONE

## Summary

Promoted the inventoried sample18 create/update/complete subset into generated managed action metadata while keeping all generated mutation disabled.

## Changes

- Added sample18 managed operation seed metadata for `create_task_card`, `update_task_card`, and `complete_task_card`.
- Added managed action field metadata for key/client input boundaries.
- Kept `reopen_task_card` and `delete_task_card` as curated-route-only dry-run custom operations.
- Split sample18 golden expectations into dry-run extension actions and generated managed actions.
- Extended the sample18 checker and PHPUnit coverage to assert managed action keys, disabled availability, and action field counts.

## Boundary

This slice does not enable generated submit, does not replace `/samples/sample18-task-board`, and does not add a dispatch route. The generated action surface is metadata-only and fail-closed by policy.

## Verification

- `php -l mtool/scripts/lib/sample18_mini_task_board_demo_check.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- JSON decode check for sample18 no-code golden/checklist fixtures
- `make sample18-pack-runtime-test`
- `make test`
- `git diff --check`

## Next

#563 should prove the public runtime exposes the disabled managed action surface without enabling submit, before any dispatch or mutation work is promoted.
