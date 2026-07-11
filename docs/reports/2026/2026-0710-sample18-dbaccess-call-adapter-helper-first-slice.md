# Sample18 DBAccess Call Adapter Helper First Slice

Date: 2026-07-10
Plan: #636
Status: FIRST_SLICE_DONE

## Summary

#636 adds a route-unwired sample18 DBAccess call adapter helper.

The helper validates allowed execution metadata and invokes only an injected callable. Tests use a fake callable, so no real `TaskCardDBAccess` method is called, no app DB transaction is opened, no generated-submit route is made executable, and no TaskCard row is mutated.

## Implemented

- Added `app_lab_sample18_task_board_generated_submit_dbaccess_call_adapter`.
- The helper accepts normalized request metadata, dispatcher metadata, `execution_guard`, `executor_coordination_plan`, an explicit executor flag, and an injected DBAccess invoker.
- It returns stable `executed`, `failed`, or `skipped` metadata.
- It carries operation key, DBAccess class/function, data object, dedupe key, and request audit event key into the adapter result.
- It copies optional `rows_affected` and `insert_id` metadata from successful fake invoker results.
- It fails closed before invoking the callable for disabled executor flag, blocked guard, blocked coordination plan, unsupported operation, DBAccess allowlist mismatch, malformed payload, missing dedupe key, and missing request audit event key.
- It classifies failed fake results and thrown exceptions as stable failed adapter metadata.

## Boundaries Kept

- No generated-submit route calls the adapter helper.
- No real `TaskCardDBAccess` class or method is invoked.
- No transaction is opened.
- No execution audit row is written.
- No idempotency execution outcome is updated.
- No TaskCard row is mutated.

## Verification

- `php -l mtool/app/lab_sample18_task_board_page.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`: `OK (15 tests, 1043 assertions)`
- Full `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 399, Assertions: 12885, Skipped: 1.`
- `git diff --check`

## Next

#637 should close the route-unwired adapter helper lane and decide whether transaction adapter preflight, real DBAccess invocation hardening, or route integration preflight should be promoted next.
