# SQLite to MySQL Verification Next ID

Date: 2026-07-13

## Scope

This records the #872 promotion verification gate slice for `next_ids`.

Implemented:

- Per-table single-column integer primary-key max-value evidence.
- Required next-id evidence as `max(primary_key) + 1`, using decimal strings rather than PHP integers.
- SQLite `sqlite_sequence` evidence when an explicit AUTOINCREMENT sequence exists.
- MySQL/MariaDB `AUTO_INCREMENT` evidence when the target table owns DB-side generation.
- Sequence safety check requiring any DB-owned next id to be greater than or equal to the required next id.
- Compare check for `next_ids`.

V1 intentionally does not infer DB-owned generation from SQLite rowid behavior or add MySQL `AUTO_INCREMENT`. It reports explicit DB-owned sequence evidence when present and otherwise verifies the copied max primary-key boundary.

## Verification Evidence

- `php -l mtool/app/sqlite_mysql_verification.php`
- `php -l tests/Integration/SqliteMysqlVerificationTest.php`
- Focused verification PHPUnit without dedicated MySQL schema:
  - `OK, but incomplete, skipped, or risky tests! Tests: 14, Assertions: 56, Skipped: 1.`
- Focused verification PHPUnit with isolated MariaDB schema `mtool_promotion_test_verify_nextid`:
  - `OK (14 tests, 61 assertions)`
- `make test`:
  - `OK, but incomplete, skipped, or risky tests! Tests: 579, Assertions: 15014, Skipped: 4.`

## Remaining #872 Work

Still not completed in this slice:

- Generated DBAccess smoke check.
- Final all-required-pass artifact using every required check from real collectors.

## Plan State

`docs/current-plans.md` #872 moved from `VALUE_COLLECTORS_DONE_NEXT_ID_DBACCESS_NEXT` to `NEXT_ID_DONE_DBACCESS_ARTIFACT_NEXT`.
