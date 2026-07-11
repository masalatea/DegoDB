# Sample18 Transaction Adapter Helper First Slice

Date: 2026-07-10
Plan: #640
Status: FIRST_SLICE_DONE

## Summary

#640 adds a route-unwired sample18 transaction adapter helper.

The helper uses fake transaction callables and the existing injected DBAccess callable boundary. It returns all-success-or-failure metadata without calling real `TaskCardDBAccess`, opening a real transaction, mutating TaskCard rows, or wiring generated-submit route execution.

## Implemented

- Added `app_lab_sample18_task_board_generated_submit_transaction_adapter`.
- The helper validates allowed `execution_guard` and planned `executor_coordination_plan` before transaction begin.
- It calls fake begin, DBAccess adapter, fake rollback, and fake commit in explicit order.
- Begin failure fails before DBAccess is called.
- DBAccess failure / exception triggers rollback and returns failure.
- Rollback failure returns `transaction_status=rollback_failed`.
- Commit failure returns failure with `recovery_required=true`.
- Successful begin -> DBAccess -> commit returns `status=executed` and `success=true`.
- All paths expose `transaction_status`, `dbaccess_status`, `recording_status`, `rolled_back`, `recovery_required`, `failure_code`, dedupe key, and request audit event key.

## Boundaries Kept

- No generated-submit route execution.
- No real app DB transaction.
- No real `TaskCardDBAccess` invocation.
- No TaskCard mutation.
- No execution audit append.
- No idempotency execution outcome update.
- `recording_status` remains `planned_not_written`.

## Verification

- `php -l mtool/app/lab_sample18_task_board_page.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`: `OK (16 tests, 1087 assertions)`
- Full `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 400, Assertions: 12932, Skipped: 1.`
- `git diff --check`

## Next

#641 should close the route-unwired transaction adapter helper lane and decide whether post-commit recording policy hardening, route integration preflight, or real DBAccess invocation adapter should be promoted next.
