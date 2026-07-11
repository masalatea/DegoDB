# Sample18 Guarded Executor Implementation Preflight

Date: 2026-07-10
Plan: #625
Status: DONE

## Summary

#625 defines the implementation boundary for the first sample18 guarded executor work.

This is a preflight only. No DBAccess method is called, no transaction is opened, no route is made executable, and no execution outcome persistence is added by this plan.

## Current Baseline

- The generated-submit route reaches `execution_guard` for valid requests.
- Ready/planned route metadata can report `execution_guard.status=allowed`.
- All execution/write intent flags remain false.
- HTTP 409 `generated_submit_disabled` is still returned.
- `sample18_generated_submit_idempotency_records` currently stores request result and `metadata_json`, but has no dedicated execution status columns.
- The idempotency repository currently supports create/reuse and latest fetch, not execution outcome updates.
- sample18 DBAccess methods return the raw `$mtooldb->execute(...)` result from generated DBAccess classes.

## Smallest Mutating Executor Boundary

The first executor implementation must not be route-wide by default. It should be entered only when:

- request/auth/CSRF/validation succeeded;
- audit append succeeded;
- idempotency create returned `recorded` and `created=true`;
- `mutation_gate`, `dbaccess_execution_plan`, `transaction_plan`, `execution_update_plan`, and `execution_guard` are all ready;
- a new explicit executor feature flag is enabled;
- DBAccess class/function and DB handle remain allowlisted immediately before execution.

The executor code boundary should separate:

- route wrapper and response assembly;
- final execution guard;
- transaction adapter;
- DBAccess call adapter;
- execution audit append;
- idempotency execution outcome update.

## Required Persistence Before DBAccess Execution

Before the first DBAccess call is implemented, add idempotency execution outcome persistence.

First slice recommendation:

- update an existing idempotency record by `dedupe_key`;
- preserve duplicate count and request identity fields;
- set stable request `result` / `failure_code` only if that is the accepted table-level representation;
- merge execution metadata into `metadata_json`;
- expose decoded fields in repository fetch output;
- fail closed for missing dedupe key, missing existing record, invalid execution status, invalid metadata, and duplicate replay;
- do not call DBAccess and do not open transactions.

Execution audit append can use the existing audit event append path, but its exact coupling to idempotency update should remain deferred until the persistence helper is covered.

## Future Executor Response Shape

Future mutating responses should include:

- `execution_guard`;
- `execution_result`;
- `transaction_result`;
- `execution_audit_update`;
- `idempotency_execution_update`;
- final `execution_status`;
- stable failure code for DBAccess failure, transaction rollback, audit update failure, idempotency update failure, duplicate replay, and guard failure.

## Required Tests Before Calling DBAccess

- idempotency execution outcome update succeeds for an existing created record;
- update fails closed for missing record and duplicate replay;
- invalid execution status/result code/metadata fails closed;
- metadata merge preserves request metadata and adds execution metadata;
- route remains non-executing until a later executor integration slice.

## Decision

Promote idempotency execution outcome persistence first.

Reason:

- The route-visible guard is complete enough to identify allowed execution candidates.
- DBAccess execution would be unsafe without a covered way to persist final execution outcome.
- The current repository lacks execution update support, so that is the next smallest independent slice.

## Next

#626 should add repository-level execution outcome update support for sample18 generated-submit idempotency records without opening transactions, calling DBAccess, or wiring the route executor.

## Verification

- `git diff --check`
