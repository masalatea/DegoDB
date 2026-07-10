# Sample18 Transaction Binding Helper First Slice

Date: 2026-07-10
Plan: #652
Status: FIRST_SLICE_DONE

## Summary

#652 adds a route-unwired transaction binding helper for sample18 generated-submit execution.

The generated-submit route still does not execute DBAccess. The new helper adapts a transaction-capable runtime object into the existing transaction adapter callables.

## Added Capability

- `app_lab_sample18_task_board_generated_submit_transaction_binding_callables` returns begin / commit / rollback / DBAccess callables.
- The begin callable allows only `sample18_application_db` and `sample18_application_db_only`.
- The begin callable requires a transaction-capable runtime object with `beginTransaction` and `inTransaction`.
- The DBAccess callable requires an active transaction before constructing the TaskCard object and invoking the supplied DBAccess object.
- The commit and rollback callables normalize unsupported or failed runtime operations into stable failure codes.

## Covered Behavior

- Successful execution begins the transaction, invokes DBAccess, and commits.
- DBAccess failure rolls back and returns user-facing failure metadata.
- Wrong DB target fails before calling the runtime transaction begin method.

## Boundary

Still not enabled:

- generated-submit route execution;
- default-on executor behavior;
- generated runtime global transaction support;
- real DB-backed generated `TaskCardDBAccess` execution from the HTTP route;
- post-commit recording from the HTTP route.

## Verification

- `php -l mtool/app/lab_sample18_task_board_page.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`
- `make test`
- `git diff --check`

## Next

#653 should close this lane and decide whether the next promoted work is generated runtime transaction support, route feature-flag integration preflight, or recovery/repair preflight.
