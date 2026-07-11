# Sample18 Safe Action Input Mapping Inventory

Plan item: #561 sample18 safe action-input mapping inventory

Status: DONE

## Summary

Recorded the sample18 action-input mapping boundary before promoting any generated action surface or mutation path.

## Changes

- Added `action_input_mapping_inventory` to the sample18 no-code fast checklist.
- Split the inventory by `create_task_card`, `update_task_card`, `complete_task_card`, `reopen_task_card`, and `delete_task_card`.
- Captured which operations already have DBAccess functions (`InsertTaskCard`, `UpdateTaskCard`, `CompleteTaskCard`) and which remain curated-route-only (`reopen`, `delete`).
- Added PHPUnit assertions that compare the inventory against the curated sample18 route source and DBAccess seed.

## Boundary

This slice does not enable generated buttons, does not replace `/samples/sample18-task-board`, and does not submit POSTs from generated no-code UI. It only freezes the action/key/input/fixed/server-managed field mapping needed before the generated action surface can grow safely.

## Verification

- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- JSON decode check for `sample/tutorials/sample18-mini-task-board-demo/golden/no-code-fast-contract-checklist.json`
- `make sample18-pack-runtime-test`
- `make test`
- `git diff --check`

## Next

#562 should promote the create/update/complete subset into generated action metadata first, with generated buttons still disabled and the curated route remaining the only mutation owner.
