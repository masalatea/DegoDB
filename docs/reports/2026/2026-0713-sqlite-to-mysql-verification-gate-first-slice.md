# SQLite-to-MySQL Verification Gate First Slice

Status: `FIRST_SLICE_DONE_COLLECTORS_NEXT`

## Result

`mtool/app/sqlite_mysql_verification.php` now provides the provider-neutral `sqlite-mysql-promotion-verification-v1` acceptance gate.

- Eleven required checks are registered centrally.
- Cutover readiness is derived only when every required check is present and passed.
- Failed, missing, skipped, unsupported, and warning-only required checks all block.
- Missing checks are materialized explicitly rather than disappearing from the artifact.
- Invalid context digests block readiness.
- Check ordering does not change the normalized artifact.
- The artifact is mutation-free and accepts only three hash identities as context, preventing credentials or DSNs from entering this layer.

## Remaining #872 work

Implement read-only SQLite/MySQL collectors for complete row counts, PK digests, and canonical full-row digests, then add constraint, nullability, JSON/BLOB/timestamp, next-ID, and DBAccess smoke evidence. The gate remains active until every registered check has live evidence.

## Verification

- Focused PHPUnit: `OK (7 tests, 16 assertions)`.
- Full `make test`: `Tests: 572, Assertions: 14974, Skipped: 3`.
- PHP syntax and `git diff --check` passed.
