# Sample18 DBAccess Mutation Dry-Run Execution Preflight

Date: 2026-07-10
Plan: #603
Status: DONE

## Summary

#603 defines the first DBAccess-bound execution preflight for sample18 generated submit.

The next implementation slice must still be non-mutating. It should convert a `mutation_gate.ready` path into explicit execution-plan metadata, not execute `TaskCardDBAccess`.

## Preconditions

The dry-run executor may be called only when all inputs are present:

- normalized generated submit request is valid;
- dispatcher dry-run is `ok=true`;
- dispatcher remains `mutation_enabled=false` and `executed=false`;
- audit append status is `appended`;
- idempotency status is `recorded` and `created=true`;
- mutation gate status is `ready`;
- operation key maps to an allowlisted sample18 DBAccess method:
  - `create_task_card` -> `InsertTaskCard`;
  - `update_task_card` -> `UpdateTaskCard`;
  - `complete_task_card` -> `CompleteTaskCard`.

If any precondition fails, the executor must return blocked/failed metadata and must not open a transaction.

## DB Boundary

The first slice must not mutate rows and must not call DBAccess methods.

It may resolve the intended DBAccess class, method, data object, bound fields, and target database boundary as metadata only.

The eventual execution boundary should be:

- explicit application DB handle, not implicit global state;
- transaction starts only after mutation gate readiness has been verified;
- rollback on DBAccess exception or unexpected affected-row result;
- commit only after DBAccess success and post-execution audit/idempotency update are defined by a later plan.

## Response Shape

The non-mutating executor should return:

- `status`: `planned`, `blocked`, or `failed`;
- `ready`: boolean;
- `mutation_enabled`: always `false` for the first slice;
- `executed`: always `false` for the first slice;
- `db_access_class`;
- `db_access_function`;
- `data_object`;
- `method_arguments`;
- `transaction`: `not_opened`;
- `reasons`: list of failure/block reasons.

## Required Tests For #604

- ready gate returns `planned` metadata and still keeps `mutation_enabled=false`, `executed=false`, and `transaction=not_opened`;
- duplicate gate blocks executor planning;
- audit/idempotency failure gates block or fail executor planning;
- invalid normalized request and non-dry-run dispatcher fail closed;
- route response does not execute the dry-run executor until explicitly wired by a later route integration slice.

## Next

#604 should add the non-mutating executor helper and focused tests for the response shape above.

## Verification

- `git diff --check`
