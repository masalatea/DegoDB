# Retry audit display follow-up first slice

Status: `FIRST_SLICE_DONE`

Date: 2026-07-01

## Summary

Surfaced recent retry audit events directly on the sync outbox detail page.

## Changes

- Added `target_key` filtering to latest audit event fetch.
- Loaded recent `sync_outbox.retry_requeued` events for the current sync outbox item.
- Added `Recent Retry Audit` section to sync outbox detail.
- Displayed audit created time, actor, result, status transition, attempts transition, and message.
- Covered the audit filter and page markers in integration contract tests.

## Boundary

In scope:

- recent retry audit display;
- existing `audit_events` repository;
- sync outbox detail page;
- focused tests.

Out of scope:

- new audit storage tables;
- audit search UI;
- retry processing behavior changes;
- scheduler or transport;
- conflict resolution.

## Verification

- `php -l mtool/app/audit_log_repository_pdo.php`
- `php -l mtool/app/project_sync_outbox_detail_page.php`
- `php -l tests/Integration/AuditLogRepositorySqliteTest.php`
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`
- `make test` (`310 tests, 10335 assertions, skipped 1`)
