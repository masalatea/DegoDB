# Sample18 Generated Runtime Transaction Support Preflight

Date: 2026-07-10
Plan: #654
Status: DONE

## Summary

#654 defines the smallest transaction support addition for generated DBAccess runtime support before any generated-submit route execution is enabled.

This is a preflight only. It does not change runtime code or enable route execution.

## Current Runtime Boundary

Generated DBAccess uses `MtoolGeneratedDbAccessRuntimeDb` through global `$mtooldb`.

Current supported surface:

- `query(string $sql)`;
- `execute(string $sql, array $params = [])`;
- `real_escape_string($value)`;
- public `errno`;
- public `error`.

Missing surface:

- `beginTransaction`;
- `commit`;
- `rollBack`;
- `inTransaction`.

The generated source is emitted from `mtool/app/project_output_db_access_generator.php`, while sample18 keeps a checked reference copy under `sample/tutorials/sample18-mini-task-board-demo/reference/DBACCESS-PHP/_support/mtool_runtime_db.php`.

## First Runtime Slice

#655 should add PDO-first transaction support to both the generator source and sample18 reference output.

Required behavior:

- `beginTransaction()` delegates to PDO `beginTransaction()` when PDO is active;
- `commit()` delegates to PDO `commit()` when PDO is active;
- `rollBack()` delegates to PDO `rollBack()` when PDO is active;
- `inTransaction()` delegates to PDO `inTransaction()` when PDO is active;
- each method clears `errno` / `error` before attempting the operation;
- thrown exceptions set `errno=1`, set `error`, and return `false`;
- unsupported mysqli transaction support should fail closed in the first slice unless a tested mysqli implementation is added at the same time.

Compatibility requirements:

- existing `query`, `execute`, and `real_escape_string` behavior must remain unchanged;
- generated DBAccess classes must keep using the same global `$mtooldb` object;
- no route execution or feature flag behavior changes in this slice.

## Test Plan

Focused coverage should prove:

- PDO begin / inTransaction / commit success;
- PDO begin / inTransaction / rollBack success;
- transaction method failure returns `false` and stable `errno` / `error`;
- existing sample18 pack tests continue passing.

## Verification

- `git diff --check`

## Next

#655 should implement the PDO-first generated runtime transaction surface and update sample18 reference output.
