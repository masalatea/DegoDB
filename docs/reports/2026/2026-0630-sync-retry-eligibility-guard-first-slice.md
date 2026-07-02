# Sync Retry Eligibility Guard First Slice

Date: 2026-06-30
Status: `FIRST_SLICE_DONE`

## Summary

Added a fail-closed retry eligibility guard for sync outbox items before adding retry/requeue mutation.

The guard is a pure helper. It does not mutate outbox state, trigger processing, schedule background work, transport data, or resolve conflicts.

## Implemented

- Added `app_no_code_operator_sync_retry_eligibility()`.
- Allows retry only when the item is `failed` and has `dedupe_key`, `operation_key`, and non-empty `last_error`.
- Returns `allowed`, `state`, `label`, `action_label`, and fail-closed `reasons`.
- Shows the read-only eligibility decision on the operator sync outbox detail page.
- Added focused eligibility test coverage.

## Boundary

In scope:

- pure retry eligibility decision
- failed outbox items
- existing status / attempts / last_error fields
- read-only operator visibility

Out of scope:

- retry/requeue mutation
- background scheduler
- remote transport
- conflict resolution
- broad dashboard

## Verification

- `php -l mtool/app/no_code_operator_sync_inspection.php`
- `php -l mtool/app/project_sync_outbox_detail_page.php`
- `php -l tests/Integration/NoCodeOperatorSyncInspectionTest.php`
- `git diff --check`
- `make test`

## Result

`make test` passed with 307 tests, 10114 assertions, and 1 skipped test.

## Next

Run the post-sync retry eligibility guard no-code product-goal replan before choosing the next implementation slice.
