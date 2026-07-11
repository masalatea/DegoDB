# Sample18 DB Backed Transaction Binding Coverage First Slice

Date: 2026-07-10
Plan: #658
Status: FIRST_SLICE_DONE

## Summary

#658 adds route-unwired DB-backed transaction binding coverage for sample18 generated-submit execution.

The generated-submit route still does not execute DBAccess. This slice proves the generated runtime, transaction binding callables, transaction adapter, and DBAccess invocation can work against an isolated SQLite/PDO database.

## Added Coverage

- Loads sample18 generated `TaskCardData` and `TaskCardDBAccess` reference classes.
- Uses a temporary SQLite file through `MTOOL_RUNTIME_SQLITE_PATH`.
- Creates a SQLite-compatible generated `task_card` table.
- Builds normalized / dispatcher / guard / coordination metadata without route wiring.
- Runs `app_lab_sample18_task_board_generated_submit_transaction_binding_callables`.
- Runs `app_lab_sample18_task_board_generated_submit_transaction_adapter`.

## Covered Behavior

- Generated `TaskCardDBAccess::InsertTaskCard` commits through the binding helper and leaves a visible row.
- Transaction result reports `recording_status=planned_not_written`.
- A DBAccess-compatible failure after an insert rolls back and leaves no visible row.
- Failure path returns `transaction_status=rolled_back`, `dbaccess_status=failed`, and `failure_code=dbaccess_failed`.

## Boundary

Still not enabled:

- generated-submit route execution;
- default-on executor behavior;
- route feature flag integration;
- execution audit append from the route;
- idempotency execution outcome update from the route.

## Verification

- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`
- `make test`
- `git diff --check`

## Next

#659 should close this lane and decide whether the next promoted work is route feature-flag integration preflight, post-commit recording DB-backed coverage, or recovery/repair preflight.
