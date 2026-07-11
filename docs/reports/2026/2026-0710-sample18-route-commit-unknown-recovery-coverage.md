# Sample18 Route Commit-Unknown Recovery Coverage

Date: 2026-07-10
Status: FIRST_SLICE_DONE
Plan: #671

## Summary

Added route-level coverage for transaction commit failure and commit exception outcomes.

This completes the current route-level recovery matrix before UI rendering work: rollback failures, post-commit recording failures, and commit-unknown outcomes now have explicit route coverage.

## Coverage

Added coverage for:

- Commit callable returning failure:
  - HTTP 500.
  - `failure_code=transaction_commit_failed`.
  - `transaction_status=commit_failed`.
  - `dbaccess_status=executed`.
  - `recovery_required=true`.
  - `recovery_reason=commit_status_unknown`.
  - post-commit recording is skipped.
- Commit callable throwing exception:
  - HTTP 500.
  - `failure_code=transaction_commit_exception`.
  - `transaction_status=commit_failed`.
  - `recovery_required=true`.
  - `recovery_reason=commit_status_unknown`.
  - post-commit recording is skipped.

## Verification

- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`
  - OK: 26 tests, 1384 assertions.
- `make test`
  - OK, but incomplete, skipped, or risky tests.
  - Tests: 410, Assertions: 13229, Skipped: 1.

## Next

Close this lane in #672 and decide whether UI success/error rendering, production runtime config hardening, or route response status refinement should be promoted next.
