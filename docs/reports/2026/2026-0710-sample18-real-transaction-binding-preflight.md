# Sample18 Real Transaction Binding Preflight

Date: 2026-07-10
Plan: #651
Status: DONE

## Summary

#651 defines how sample18 generated-submit execution should bind the route-unwired transaction adapter to a real generated DBAccess runtime boundary.

This is a preflight only. It does not enable generated-submit route execution, default-on executor behavior, or real HTTP route mutation.

## Findings

The generated DBAccess reference uses the legacy-compatible global `$mtooldb` surface:

- `connect_mtooldb_if_not_yet()`;
- `reconnect_mtooldb_if_necessary()`;
- `$mtooldb->execute(...)`;
- `$mtooldb->errno`;
- `$mtooldb->error`.

The current generated runtime support can use PDO or mysqli, but it does not expose transaction methods yet.

The execution target must be the generated sample18 application schema:

- generated DBAccess metadata targets `task_card`;
- the hand-coded sample page uses `TaskCard`;
- route execution must not accidentally mix the hand-coded UI PDO table with generated DBAccess metadata.

## Transaction Boundary

Future real binding must provide a transaction-capable generated DB runtime object with these minimal operations:

- `beginTransaction` or equivalent begin operation;
- `commit`;
- `rollBack`;
- `inTransaction` or an explicit transaction context marker;
- access to the same DB handle used by `TaskCardDBAccess`.

The binding helper should adapt that runtime object into the already-tested transaction adapter callables:

- begin callable returns `ok=true` only after the app DB transaction is active;
- DBAccess callable creates or receives a `TaskCardDBAccess` instance bound to the same runtime DB context;
- rollback callable is used for DBAccess failure or exception;
- commit callable is used only after DBAccess success;
- every failure returns the stable all-success-or-failure metadata shape.

## Failure Policy

Required failure behavior:

- begin failure returns `transaction_begin_failed` or `transaction_begin_exception`;
- DBAccess failure triggers rollback;
- DBAccess exception triggers rollback;
- rollback failure returns user-facing failure with `transaction_status=rollback_failed`;
- commit failure returns user-facing failure with recovery metadata;
- no post-commit recording is attempted until the app DB transaction has committed.

## First Slice

#652 should add a route-unwired transaction binding helper using fake transaction-capable objects first.

It should not yet modify generated DBAccess runtime support globally unless the helper boundary proves it needs a shared method. The first test should prove ordering and context binding:

1. begin generated app DB transaction;
2. create or receive DBAccess object bound to the same context;
3. invoke `app_lab_sample18_task_board_generated_submit_real_dbaccess_invocation_adapter`;
4. rollback on DBAccess failure;
5. commit on DBAccess success.

## Verification

- `git diff --check`
