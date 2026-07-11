# Sample18 Idempotency Execution Outcome Persistence First Slice

Date: 2026-07-10
Plan: #626
Status: FIRST_SLICE_DONE

## Summary

#626 adds repository-level execution outcome update support for existing sample18 generated-submit idempotency records.

This is still pre-DBAccess work. No transaction is opened, no DBAccess method is called, no execution audit row is written, and no route executor is wired.

## Implemented

- Added `app_lab_sample18_generated_submit_idempotency_update_execution_outcome`.
- Added PDO-backed update support for existing `sample18_generated_submit_idempotency_records` rows by `dedupe_key`.
- Preserved request identity, duplicate count, and existing request metadata.
- Stored execution outcome under `metadata.execution` while updating stable table-level `result` and `failure_code`.
- Covered executed outcome persistence and latest-record filtering by `result=executed`.
- Failed closed for missing record, invalid execution status, invalid execution metadata, and duplicate replay.

## Boundaries Kept

- No DBAccess mutation is executed.
- No transaction is opened.
- No execution audit row is written.
- No generated-submit route executor is wired.
- Existing create/reuse idempotency behavior remains unchanged.

## Verification

- `php -l mtool/app/lab_sample18_generated_submit_idempotency_repository.php`
- `php -l mtool/app/lab_sample18_generated_submit_idempotency_repository_pdo.php`
- `php -l tests/Integration/Sample18GeneratedSubmitIdempotencyRepositorySqliteTest.php`
- Focused repository PHPUnit via `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample18-mini-task-board-demo/compose.yaml --run-script=./sample/tutorials/sample18-mini-task-board-demo/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/Sample18GeneratedSubmitIdempotencyRepositorySqliteTest.php`: `OK (6 tests, 86 assertions)`
- `make sample18-pack-runtime-test`: `OK (12 tests, 848 assertions)`
- Full `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 396, Assertions: 12690, Skipped: 1.`
- `git diff --check`

## Next

#627 should close the idempotency execution outcome persistence lane and decide whether execution audit append persistence, route integration metadata, or guarded executor implementation should be promoted next.
