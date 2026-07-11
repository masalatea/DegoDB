# Sample18 Generated Submit Request Contract Preflight

Plan item: #565 sample18 generated submit request contract preflight

Status: DONE

## Summary

Defined the sample18 generated submit request payload, normalization, ignored-field, and validation failure contract before adding any HTTP route or mutation dispatch.

## Changes

- Added pure sample18 generated submit contract helpers for `create_task_card`, `update_task_card`, and `complete_task_card`.
- Normalized title/body/assignee trimming, priority clamping, status fallback, due-date validation, fixed fields, derived `completed_at`, and server-managed `updated_at`.
- Recorded the contract in `no-code-fast-contract-checklist.json` with valid and invalid request fixtures.
- Added focused PHPUnit coverage that checks valid payloads, ignored client-managed fields, validation errors, unknown operation failure, and alignment with the existing action-input inventory.

## Boundary

This slice does not add a generated submit route, enable generated buttons, call DBAccess, enqueue outbox work, or replace the curated sample18 page. It only defines the request contract that a later blocked route wrapper can call.

## Verification

- `php -l mtool/app/lab_sample18_task_board_page.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- JSON parse check for `sample/tutorials/sample18-mini-task-board-demo/golden/no-code-fast-contract-checklist.json`
- `make sample18-pack-runtime-test`
- `make test`
- `git diff --check`

## Next

#566 should add a narrow HTTP wrapper for generated sample18 submit requests that validates payloads and returns blocked before any mutation dispatch.
