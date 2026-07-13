# SQLite-to-MySQL Target Schema First Slice

Status: `FIRST_SLICE_DONE_LIVE_APPLY_NEXT`

## Result

#869 now has a bounded target-schema preparation module in `mtool/app/sqlite_mysql_target_schema.php`.

- A ready promotion manifest produces deterministic parent-first MySQL DDL.
- Only allowlisted identifiers, target types, charset, collation, defaults, primary/unique keys, and foreign keys are emitted.
- The plan carries a schema SHA-256 and per-table statement hashes.
- Target inspection is read-only and requires a MySQL PDO connection.
- Any existing base table returns `target_not_empty`.
- Apply refuses to inspect or mutate before explicit approval.
- MySQL DDL implicit-commit behavior is explicit: a failure reports `partial_failure`, the already-created tables, and whether mutation occurred. It is not represented as transactional.
- No application data rows are imported.

## Remaining #869 qualification

Run the apply helper against a dedicated empty MySQL/MariaDB test schema, then re-inspect `information_schema` and compare the resulting columns, keys, FKs, charset/collation, and schema digest evidence. Also prove non-empty rejection without modifying existing objects. This must use an isolated schema rather than Mtool's config or lab database.

#870 does not start until this live target qualification closes #869.

## Verification

- PHP syntax checks passed.
- Focused PHPUnit: `OK (3 tests, 13 assertions)`.
- Full `make test`: `Tests: 557, Assertions: 14932, Skipped: 1`.
- `git diff --check` passed.
