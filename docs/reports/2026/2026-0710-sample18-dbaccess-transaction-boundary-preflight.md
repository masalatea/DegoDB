# Sample18 DBAccess Transaction Boundary Preflight

Date: 2026-07-10
Plan: #610
Status: DONE

## Summary

#610 defines the transaction boundary required before sample18 generated-submit DBAccess execution can be enabled.

This is a preflight only. No DBAccess execution is enabled, and no transaction is opened by this plan.

## Preconditions Before Transaction

A transaction may be opened only after all of these are true:

- HTTP method, CSRF, and request validation succeeded.
- Audit append succeeded with `status=appended`.
- Idempotency create succeeded with `status=recorded` and `created=true`.
- `mutation_gate.status=ready`.
- `dbaccess_execution_plan.status=planned`.
- Execution plan uses an allowlisted DBAccess method for the operation.
- The selected application DB handle is explicit and points at the sample18 lab/application database, not the config DB audit/idempotency store.

Duplicate, failed, skipped, disabled, invalid, and non-dry-run states must not open a transaction.

## Transaction Boundary

When execution is eventually enabled:

- open transaction after readiness and allowlist checks;
- call exactly one DBAccess method inside the transaction;
- rollback on DBAccess exception;
- rollback on unexpected affected-row or missing-result contract;
- commit only after DBAccess success and after post-execution audit/idempotency update policy is satisfied;
- response must include transaction outcome metadata for success, rollback, and failure.

## Post-Execution Metadata

Execution success should eventually update:

- audit metadata:
  - original request audit event key;
  - executed result;
  - DBAccess class/function;
  - transaction status;
  - affected row or inserted id metadata where available.
- idempotency metadata:
  - execution status;
  - execution result code;
  - completed timestamp;
  - transaction status.

The first implementation slice should not write these updates. It should describe them as metadata plans only.

## Response Shape

Transaction planning metadata should include:

- `status`: `planned`, `blocked`, or `failed`;
- `transaction`: `planned_not_opened`;
- `db_handle`: explicit boundary name;
- `will_execute`: false for the first helper slice;
- `will_update_audit`: false for the first helper slice;
- `will_update_idempotency`: false for the first helper slice;
- `rollback_policy`;
- `post_execution_audit_update`;
- `post_execution_idempotency_update`;
- `reasons`.

## Required Tests For #611

- planned execution-plan response creates transaction-plan metadata without opening a transaction;
- disabled, duplicate, failed, invalid, and non-dry-run plans remain blocked/failed and do not mark `will_execute`;
- transaction plan identifies the sample18 application DB boundary separately from audit/idempotency config DB metadata;
- route response remains non-mutating until a later route integration slice explicitly wires transaction-plan metadata.

## Next

#611 should add a non-mutating transaction-plan helper and focused tests.

## Verification

- `git diff --check`
