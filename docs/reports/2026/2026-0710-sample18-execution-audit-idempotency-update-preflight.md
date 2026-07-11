# Sample18 Execution Audit Idempotency Update Preflight

Date: 2026-07-10
Plan: #615
Status: DONE

## Summary

#615 defines the post-execution audit and idempotency update contract for sample18 generated-submit execution.

This is a preflight only. No DBAccess execution, audit update, idempotency update, or transaction opening is enabled.

## Execution Audit Event

When guarded execution is eventually enabled, execution should append a second audit event linked to the request audit event.

Planned shape:

- `event_type`: `sample18.generated_submit.executed`;
- `project_key`: `SAMPLE18`;
- `target_type`: `sample18_task_card`;
- `target_key`: same dedupe key as the request;
- `result`: `executed`, `failed`, or `rolled_back`;
- metadata:
  - `request_audit_event_key`;
  - `dedupe_key`;
  - `operation_key`;
  - `payload_fingerprint`;
  - `db_access_class`;
  - `db_access_function`;
  - `transaction_status`;
  - `execution_status`;
  - `affected_rows` or `inserted_id` when available;
  - `rollback_reason` when applicable.

The request audit event should remain immutable.

## Idempotency Update

The idempotency record should be updated only after the execution outcome is known.

Planned update fields:

- `execution_status`: `planned`, `executed`, `failed`, or `rolled_back`;
- `execution_result_code`: stable machine-readable result;
- `completed_at`: execution completion timestamp;
- `transaction_status`: `committed`, `rolled_back`, or `not_opened`;
- metadata merge:
  - `db_access_class`;
  - `db_access_function`;
  - `execution_audit_event_key`;
  - `affected_rows` or `inserted_id`;
  - `rollback_reason`.

Duplicate replays must not update execution state.

## Response Contract

Future guarded execution responses should expose:

- existing request/audit/idempotency metadata;
- `execution_update_plan`;
- `execution_audit_update`;
- `idempotency_execution_update`;
- `transaction_plan`;
- final `execution_status`.

For the first implementation slice, these remain metadata plans only:

- `will_write_audit=false`;
- `will_update_idempotency=false`;
- `will_execute=false`;
- `transaction` remains not opened.

## Required Tests For #616

- planned transaction metadata derives a post-execution update plan without writing rows;
- duplicate, blocked, failed, and unsafe transaction plans fail closed;
- update plan carries request audit event key and dedupe key when present;
- update plan distinguishes audit append store and idempotency store;
- route response remains unwired to execution update plan until a later route integration slice.

## Next

#616 should add a non-mutating execution update-plan helper and focused tests.

## Verification

- `git diff --check`
