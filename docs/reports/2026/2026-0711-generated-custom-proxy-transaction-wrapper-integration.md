# 2026-0711 Generated Custom Proxy Transaction Wrapper Integration

Status: `FIRST_SLICE_DONE`

## Problem

Generated Custom Proxy already supported ordered DBAccess steps plus `in_transaction=1`, but its transaction implementation still assumed that global `$mtooldb` was a native mysqli object. Current generated DBAccess output uses `MtoolGeneratedDbAccessRuntimeDb`, so the configured transaction path rejected the actual runtime wrapper.

The insert-id response helper had the same native-mysqli assumption.

## Change

- Require the shared runtime transaction method contract instead of a native mysqli type.
- Start with `beginTransaction()`.
- Commit only after every required proxy step completes.
- Roll back an active transaction after a required-step exception.
- Surface begin, commit, and rollback failures as endpoint failures.
- Add driver-neutral `lastInsertId()` to the generated runtime wrapper while retaining native mysqli fallback in the proxy runtime.

Generated DBAccess classes remain unchanged and transaction-unaware.

## Focused Coverage

The generated Custom Proxy runtime contract test evaluates the generated endpoint base class with a fake shared runtime wrapper and verifies:

- success order: begin, callback, commit;
- required failure order: begin, callback, rollback;
- the original required-step exception is rethrown after successful rollback;
- obsolete native `autocommit(false)` usage is absent;
- shared transaction and last-insert-id methods are present in generated output.

## Next

#716 must add a real generated Custom Proxy mutation fixture. Contract-level callback coverage is not sufficient by itself; the fixture must execute multiple required generated DBAccess mutations against a real transactional database and prove endpoint-level all-commit and all-rollback behavior.
