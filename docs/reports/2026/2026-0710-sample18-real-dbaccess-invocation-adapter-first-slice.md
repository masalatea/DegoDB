# Sample18 Real DBAccess Invocation Adapter First Slice

Date: 2026-07-10
Plan: #649
Status: FIRST_SLICE_DONE

## Summary

#649 adds a route-unwired real-compatible DBAccess invocation adapter for sample18 generated-submit execution.

The generated-submit route still does not execute DBAccess. The new helper is covered through focused PHPUnit using a fake object with the same method names as `TaskCardDBAccess`.

## Added Capability

- `app_lab_sample18_task_board_generated_submit_task_card_data_object` builds a `TaskCardData` object when that class is loaded, otherwise a compatible object for isolated tests.
- The DTO construction copies only known TaskCard fields from dispatcher `TaskCardObj` metadata into the real DBAccess property names.
- `app_lab_sample18_task_board_generated_submit_normalize_real_dbaccess_result` converts real DBAccess-like returns into the existing adapter result shape.
- `app_lab_sample18_task_board_generated_submit_real_dbaccess_invocation_adapter` requires explicit `in_transaction=true`, validates through the existing DBAccess call adapter, invokes only the expected method on a supplied object, and leaves route execution unwired.

## Covered Behavior

- `create_task_card` constructs a task card object and calls `InsertTaskCard`.
- `update_task_card` constructs a task card object and calls `UpdateTaskCard`.
- `complete_task_card` constructs a task card object and calls `CompleteTaskCard`.
- Missing transaction context skips invocation with `dbaccess_transaction_not_active`.
- Missing DBAccess method fails closed with `dbaccess_method_missing`.
- DB error-like object results normalize to `dbaccess_failed`.

## Boundary

Still not enabled:

- generated-submit route execution;
- default-on executor behavior;
- real app DB transaction begin / commit / rollback binding;
- post-commit execution audit or idempotency writes from the HTTP route.

## Verification

- `php -l mtool/app/lab_sample18_task_board_page.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`
- `make test`
- `git diff --check`

## Next

#650 should close this lane and decide whether the next promoted work is route feature-flag integration, real transaction binding, or recovery/repair preflight.
