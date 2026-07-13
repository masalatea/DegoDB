# SQLite-to-MySQL Import First Slice

Status: `DONE_FIRST_SLICE_MYSQL_LIVE`

## Result

#871 added `mtool/app/sqlite_mysql_import.php`.

- Import requires explicit approval, a ready promotion manifest, a MySQL target, and a valid export chunk.
- The chunk row count and SHA-256 are re-derived before any transaction starts.
- Rows are inserted only; there is no upsert, replace, or silent overwrite path.
- One chunk owns one MySQL transaction.
- JSON and BLOB envelopes are decoded only after strict shape and byte-length validation.
- A checkpoint is returned only after commit.
- Replaying a checkpointed chunk with the same digest is a mutation-free `already_committed` result.
- A checkpoint digest mismatch fails closed.
- Replaying committed rows without the checkpoint hits the target constraint and rolls the whole chunk back.
- A failed chunk returns the caller's prior checkpoint unchanged.

## Live qualification

An isolated `mtool_promotion_test_import` MariaDB schema proved a two-row chunk commit, JSON/BLOB restoration, checkpoint retry no-op, duplicate rollback, and unchanged target row count after failure. The temporary schema was dropped after the test.

The live test remains opt-in through a guarded `PROMOTION_MYSQL_TEST_DB` name. Normal tests do not create or mutate a target schema.

## Verification

- PHP syntax checks passed.
- opt-in live focused PHPUnit: `OK (3 tests, 15 assertions)`.
- Full `make test`: `Tests: 565, Assertions: 14958, Skipped: 3`.
- `git diff --check` passed.

#872, the promotion verification gate, is active next.
