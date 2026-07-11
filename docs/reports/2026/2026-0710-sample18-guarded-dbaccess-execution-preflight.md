# Sample18 Guarded DBAccess Execution Preflight

Date: 2026-07-10
Plan: #620
Status: DONE

## Summary

#620 defines the first guarded DBAccess execution contract for sample18 generated submit.

This is a preflight only. No DBAccess execution, transaction opening, execution audit write, or idempotency execution update is enabled by this plan.

## Final Enablement Inputs

DBAccess execution may be considered only when all of these are true:

- HTTP method, CSRF, request validation, and operation allowlist succeeded.
- Request audit append returned `status=appended`.
- Idempotency create returned `status=recorded` and `created=true`.
- Duplicate replay is absent.
- `mutation_gate.status=ready` and `mutation_gate.ready=true`.
- `dbaccess_execution_plan.status=planned` and `dbaccess_execution_plan.ready=true`.
- `transaction_plan.status=planned` and `transaction_plan.ready=true`.
- `execution_update_plan.status=planned` and `execution_update_plan.ready=true`.
- Every current metadata plan still has `will_execute=false`, `will_write_audit=false`, and `will_update_idempotency=false` until the explicit executor slice flips those flags.
- The target DBAccess class/function pair is allowlisted for the normalized operation.
- The selected DB handle is the sample18 application DB boundary, not the config DB audit/idempotency store.

Any missing, skipped, duplicate, failed, blocked, unsafe, or non-planned state must block execution.

## First Guard Helper Shape

The next implementation slice should add a final non-executing guard helper.

Planned response shape:

- `status`: `allowed`, `blocked`, or `failed`;
- `ready`: boolean;
- `will_open_transaction`: false for the first helper slice;
- `will_call_dbaccess`: false for the first helper slice;
- `will_write_execution_audit`: false for the first helper slice;
- `will_update_idempotency_execution`: false for the first helper slice;
- `db_handle`;
- `db_access_class`;
- `db_access_function`;
- `operation_key`;
- `dedupe_key`;
- `request_audit_event_key`;
- `reasons`.

The helper must be metadata-only and must not be wired as an executable route.

## Future Execution Sequence

When an explicit later executor slice is enabled, the sequence should be:

- re-check the final guard immediately before execution;
- open a transaction on the sample18 application DB;
- call exactly one allowlisted DBAccess method;
- rollback on DBAccess exception or invalid result contract;
- append execution audit metadata for success or failure;
- update idempotency execution state after outcome is known;
- commit only after DBAccess success and required post-execution update policy succeeds;
- return a final response with transaction, execution audit, idempotency update, and execution status metadata.

## Failure Matrix

The guard must fail closed for:

- method/CSRF/validation/unknown-operation failures;
- audit append failed or skipped;
- idempotency failed, skipped, or duplicate;
- mutation gate disabled, blocked, or failed;
- execution plan blocked, failed, non-planned, or unsafe;
- transaction plan blocked, failed, non-planned, or unsafe;
- execution update-plan blocked, failed, non-planned, or unsafe;
- DBAccess class/function mismatch;
- wrong DB handle;
- missing dedupe key or request audit event key.

## Required Tests For #621

- planned route-ready metadata produces an `allowed` guard response while still reporting all `will_*` flags false;
- disabled, duplicate, failed, and missing-link inputs block or fail closed with stable reasons;
- unsafe metadata that already claims execution/write intent fails closed;
- DBAccess class/function and DB handle mismatches fail closed;
- route response remains non-executing until a later route integration slice explicitly exposes guard metadata.

## Next

#621 should add the final non-executing guarded execution gate helper and focused tests.

## Verification

- `git diff --check`
