# Generated Runtime Transaction Support First Slice

Date: 2026-07-10
Plan: #655
Status: FIRST_SLICE_DONE

## Summary

#655 adds PDO-first transaction support to generated DBAccess runtime support.

The generated-submit route still does not execute DBAccess. This slice only extends the generated runtime `$mtooldb` surface and updates sample18 reference output.

## Added Capability

- `MtoolGeneratedDbAccessRuntimeDb::beginTransaction()`.
- `MtoolGeneratedDbAccessRuntimeDb::commit()`.
- `MtoolGeneratedDbAccessRuntimeDb::rollBack()`.
- `MtoolGeneratedDbAccessRuntimeDb::inTransaction()`.

PDO behavior:

- delegates to the underlying PDO transaction methods;
- clears `errno` / `error` before each operation;
- catches exceptions, sets `errno=1`, sets `error`, and returns `false`.

Unsupported connection behavior:

- returns `false`;
- sets `errno=1`;
- sets a stable unsupported transaction message.

## Updated Files

- `mtool/app/project_output_db_access_generator.php`
- `sample/tutorials/sample18-mini-task-board-demo/reference/DBACCESS-PHP/_support/mtool_runtime_db.php`
- `tests/Integration/Sample18MiniTaskBoardDemoTest.php`

## Covered Behavior

- Generator source and sample18 reference output both contain the transaction methods.
- SQLite/PDO runtime can begin, commit, and report transaction state.
- SQLite/PDO runtime can begin, roll back, and preserve only committed rows.
- Commit without an active transaction fails with stable `errno` / `error`.
- Existing sample18 generated output digest check passes after reference update.

## Boundary

Still not enabled:

- generated-submit route execution;
- default-on executor behavior;
- DB-backed generated-submit HTTP route mutation;
- post-commit recording from the HTTP route.

## Verification

- `php -l mtool/app/project_output_db_access_generator.php`
- `php -l sample/tutorials/sample18-mini-task-board-demo/reference/DBACCESS-PHP/_support/mtool_runtime_db.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`
- `make test`
- `git diff --check`

## Next

#656 should close this lane and decide whether the next promoted work is DB-backed transaction binding coverage, route feature-flag integration preflight, or recovery/repair preflight.
