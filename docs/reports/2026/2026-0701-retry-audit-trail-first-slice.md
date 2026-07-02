# Retry audit trail first slice

Status: `FIRST_SLICE_DONE`

Date: 2026-07-01

## Summary

Added a focused audit trail for operator retry requeue mutations on sync outbox detail.

## Changes

- Added `sync_outbox.retry_requeued` audit event input builder.
- Appended an audit event after successful retry requeue from the sync outbox detail page.
- Included before/after status, attempts, last error, operation key, operation type, and contract key in audit metadata.
- Added retry notice audit trail state: `recorded`, `failed`, or `not reported`.
- Covered the event shape and route page markers in integration contract tests.

## Boundary

In scope:

- operator retry requeue audit event;
- existing `audit_events` repository;
- detail page notice wording;
- focused tests.

Out of scope:

- new audit storage tables;
- retry processing behavior changes;
- scheduler or transport;
- conflict resolution;
- broader operator workflow redesign.

## Verification

- `php -l mtool/app/project_sync_outbox_detail_page.php`
- `php -l tests/Integration/NoCodeOperatorSyncInspectionTest.php`
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`
- `make test` (`310 tests, 10330 assertions, skipped 1`)
