# Operator Sync Retry Action First Slice

Date: 2026-06-30
Status: `FIRST_SLICE_DONE`

## Summary

Added the first operator retry mutation for managed operation sync outbox items.

Eligible failed items can now be requeued to `pending` from the project-scoped sync outbox detail page. The action clears `last_error` and keeps `attempts` unchanged until the existing processor claims the item again. It does not process the item inline, schedule background work, transport data, resolve conflicts, or add a retry audit table.

## Implemented

- Added `app_pdo_requeue_failed_managed_operation_sync_outbox_item()`.
- Reuses the existing status update behavior to move `failed` -> `pending`.
- Rejects missing items and non-`failed` items fail-closed.
- Added a CSRF-protected POST action to `/projects/{project}/sync-outbox/{dedupe_key}`.
- Shows retry success and error messages on the operator detail page.
- Kept retry eligibility delegated to `app_no_code_operator_sync_retry_eligibility()`.
- Added repository lifecycle coverage for requeue behavior.
- Added route/source contract assertions for CSRF and retry action wiring.

## Boundary

In scope:

- eligible failed item -> `pending`
- clear `last_error`
- keep `attempts` unchanged until processor claim
- project-scoped operator POST action
- CSRF protection
- focused repository/operator contract tests

Out of scope:

- immediate processing
- background scheduler
- remote transport
- conflict resolution
- broad dashboard
- retry audit table

## Verification

- `php -l mtool/app/managed_operation_sync_outbox_repository_pdo.php`
- `php -l mtool/app/project_sync_outbox_detail_page.php`
- `php -l tests/Integration/ManagedOperationLayerFoundationTest.php`
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`
- `git diff --check`
- `make test`

## Next

Run a short post-retry-action product goal replan before choosing the next implementation slice.
