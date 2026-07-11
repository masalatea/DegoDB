# Sample18 Route Execution Failure/Recovery Coverage First Slice

Date: 2026-07-10
Status: FIRST_SLICE_DONE
Plan: #666

## Summary

Added route-level coverage for explicit-executor failure outcomes after the first route execution success path.

This keeps the all-success-or-failure route contract concrete: success is only returned when transaction execution and post-commit recording both succeed; otherwise the route returns a failure payload with the appropriate transaction and recovery metadata.

## Coverage

Added coverage for:

- Missing executor transaction callable:
  - HTTP 500.
  - `failure_code=executor_transaction_callable_missing`.
  - No recovery required.
- DBAccess failure after transaction begin:
  - begin -> DBAccess -> rollback ordering.
  - HTTP 500.
  - `failure_code=dbaccess_failed`.
  - `transaction_status=rolled_back`.
  - post-commit recording is skipped.
- Post-commit idempotency outcome failure:
  - transaction commits first.
  - execution audit is recorded.
  - idempotency outcome update fails.
  - HTTP 500.
  - `failure_code=idempotency_update_failed`.
  - `recovery_required=true`.
  - `recovery_reason=post_commit_recording_failed`.

Duplicate non-execution remains covered by #664.

## Verification

- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`
  - OK: 25 tests, 1352 assertions.
- `make test`
  - OK, but incomplete, skipped, or risky tests.
  - Tests: 409, Assertions: 13197, Skipped: 1.

## Next

Close this lane in #667 and decide whether to promote real sample runtime default binding, UI success/error rendering, or additional commit-unknown recovery coverage.
