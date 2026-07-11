# Sample18 Generated Submit Idempotency Repository Helper First Slice

Date: 2026-07-10
Plan: #593
Status: FIRST_SLICE_DONE

## Summary

#593 adds storage-backed repository/helper coverage for sample18 generated submit idempotency records.

This slice does not connect the generated submit route to idempotency persistence and does not enable DBAccess mutation.

## Implemented

- Added config DB table `sample18_generated_submit_idempotency_records`.
- Added SQLite bootstrap allowlist support for the new table.
- Added repository wrapper and PDO implementation:
  - `app_lab_sample18_generated_submit_idempotency_create_or_reuse_record()`
  - `app_lab_sample18_generated_submit_idempotency_fetch_latest_records()`
- Added create-or-reuse behavior:
  - first valid blocked request record returns `result=recorded`, `created=true`;
  - duplicate dedupe key returns `result=duplicate`, `created=false`, and increments `duplicate_count`.
- Added validation/failure behavior:
  - missing required fields fail closed without creating rows;
  - non-array metadata fails closed without creating rows;
  - unsupported result values fail closed;
  - repository connection failure returns `result=failed`.

## Boundaries Kept

- The generated submit route is not integrated with this repository yet.
- Valid generated submit requests still return HTTP 409 through the existing route.
- DBAccess mutation remains disabled.
- Duplicate audit interaction is not changed yet.

## Verification

- `php -l mtool/app/lab_sample18_generated_submit_idempotency_repository.php`
- `php -l mtool/app/lab_sample18_generated_submit_idempotency_repository_pdo.php`
- `php -l mtool/app/config_db_bootstrap.php`
- `php -l tests/Integration/Sample18GeneratedSubmitIdempotencyRepositorySqliteTest.php`
- Focused PHPUnit sample18 idempotency repository: `OK (4 tests, 52 assertions)`
- Full `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 388, Assertions: 12214, Skipped: 1.`
- `git diff --check`

## Next

#594 should close the repository/helper lane and decide whether to promote route integration preflight or duplicate audit interaction coverage next.
