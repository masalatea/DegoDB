# Sample18 Post Commit Execution Recording Preflight

Date: 2026-07-10
Plan: #642
Status: DONE

## Summary

#642 defines post-commit execution recording as a required sample18 generated-submit execution step.

Under the shared execution success policy, app DB commit alone is not enough for user-facing success. Execution audit append and idempotency execution outcome update must also succeed. If either recording step fails after commit, the route result must be failure with recovery metadata.

## Required Recording Steps

After app DB transaction commit succeeds, future route execution must require:

1. execution audit append succeeds;
2. idempotency execution outcome update succeeds.

Only after both succeed may the route return user-facing success.

## Failure Contract

- Execution audit append failure returns failure.
- Idempotency execution outcome update failure returns failure.
- If app DB commit already succeeded, recording failure must set `recovery_required=true`.
- The failure metadata must include `recording_status=failed`.
- The failure metadata must preserve `transaction_status=committed`.
- The failure metadata must preserve dedupe key and request audit event key.
- Duplicate retry must remain fail-closed unless a dedicated repair/replay path is designed later.

## First Helper Slice

#643 should add a route-unwired helper that consumes committed transaction metadata and fake recording callables.

Inputs:

- transaction adapter result with `success=true`, `transaction_status=committed`, and `dbaccess_status=executed`;
- execution update plan;
- execution guard;
- fake execution audit append callable;
- fake idempotency outcome update callable.

Outputs:

- `status`: `recorded` or `failed`;
- `success`: boolean;
- `recording_status`: `recorded`, `failed`, or `skipped`;
- `execution_audit_status`;
- `idempotency_update_status`;
- `recovery_required`;
- stable `failure_code`;
- dedupe key and request audit event key.

## Boundaries Kept

- No generated-submit route execution.
- No real DBAccess mutation.
- No real transaction.
- No real audit append or idempotency outcome update in the first helper slice.

## Verification

- `git diff --check`

## Next

#643 should implement the route-unwired recording helper using fake callables and focused tests.
