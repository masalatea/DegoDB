# SQLite to MySQL Verification Schema Collectors

Date: 2026-07-13

## Scope

This records the #872 promotion verification gate slice for schema and integrity collectors.

Implemented:

- Actual DB nullability evidence from SQLite `PRAGMA table_info` and MySQL/MariaDB `information_schema.COLUMNS`.
- Actual primary/unique key evidence from SQLite `PRAGMA index_list` / `PRAGMA index_info` and MySQL/MariaDB `information_schema.STATISTICS`.
- Actual foreign-key shape evidence from SQLite `PRAGMA foreign_key_list` and MySQL/MariaDB `information_schema.KEY_COLUMN_USAGE`.
- FK orphan-count verification using declared manifest foreign keys.
- Manifest-expected schema summaries for `nullability`, `unique_keys`, and `foreign_keys`.
- Compare checks that require both source and target evidence to match the manifest expectation.

The checks ignore driver-specific constraint names for equivalence and compare stable shapes: kind, ordered columns, referenced table, and ordered referenced columns.

## Verification Evidence

- `php -l mtool/app/sqlite_mysql_verification.php`
- `php -l tests/Integration/SqliteMysqlVerificationTest.php`
- Focused verification PHPUnit without dedicated MySQL schema:
  - `OK, but incomplete, skipped, or risky tests! Tests: 11, Assertions: 38, Skipped: 1.`
- Focused verification PHPUnit with isolated MariaDB schema `mtool_promotion_test_verify_schema`:
  - `OK (11 tests, 41 assertions)`
- `make test`:
  - `OK, but incomplete, skipped, or risky tests! Tests: 576, Assertions: 14996, Skipped: 4.`

## Remaining #872 Work

Still not completed in this slice:

- JSON/BLOB/timestamp focused check entries beyond the full row digest.
- Next-id / sequence check.
- Generated DBAccess smoke check.
- Final all-required-pass artifact using every required check from real collectors.

## Plan State

`docs/current-plans.md` #872 moved from `CORE_DIGEST_COLLECTORS_DONE_REMAINING_CHECKS_NEXT` to `SCHEMA_COLLECTORS_DONE_REMAINING_VALUE_NEXT`.
