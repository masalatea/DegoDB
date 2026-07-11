# Sample18 DB Backed Transaction Binding Coverage Preflight

Date: 2026-07-10
Plan: #657
Status: DONE

## Summary

#657 defines the first DB-backed transaction binding coverage before generated-submit route execution is enabled.

This is a preflight only. It does not enable the generated-submit HTTP route, default-on executor behavior, or post-commit recording.

## Coverage Target

The first DB-backed slice should prove these pieces work together:

- generated runtime `$mtooldb` PDO transaction support;
- `app_lab_sample18_task_board_generated_submit_transaction_binding_callables`;
- `app_lab_sample18_task_board_generated_submit_transaction_adapter`;
- generated `TaskCardDBAccess`;
- generated `TaskCardData` DTO construction;
- sample18 generated `task_card` schema.

## Test Shape

#658 should use an isolated SQLite file and the sample18 generated reference classes:

- set `MTOOL_RUNTIME_SQLITE_PATH` to a temporary SQLite path;
- require sample18 reference `TaskCardData` and `TaskCardDBAccess`;
- create a SQLite-compatible `task_card` table matching the generated DBAccess SQL;
- build the existing normalized / dispatcher / guard / coordination metadata chain without route wiring;
- create transaction binding callables using `MtoolGeneratedDbAccessRuntimeDb`;
- run the existing transaction adapter.

Required success coverage:

- `InsertTaskCard` through transaction binding returns committed success;
- the inserted row remains visible after commit;
- the transaction result still reports `recording_status=planned_not_written`;
- no HTTP route response is involved.

Required rollback coverage:

- a DBAccess failure inside the transaction returns user-facing failure;
- rollback executes;
- no failed mutation remains visible after rollback;
- post-commit recording remains deferred.

## Boundary

Still not enabled:

- generated-submit route execution;
- default-on executor behavior;
- route feature flag integration;
- execution audit append from the route;
- idempotency execution outcome update from the route.

## Verification

- `git diff --check`

## Next

#658 should implement the DB-backed route-unwired coverage with focused sample18 PHPUnit.
