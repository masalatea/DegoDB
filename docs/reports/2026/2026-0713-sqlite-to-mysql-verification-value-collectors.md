# SQLite to MySQL Verification Value Collectors

Date: 2026-07-13

## Scope

This records the #872 promotion verification gate slice for value-specific collectors.

Implemented:

- Per-table `json_values` evidence with canonical JSON conversion reused from export.
- Per-table `blob_values` evidence with byte length plus SHA-256 representation.
- Per-table `timestamp_values` evidence with strict DATETIME string normalization.
- PK-bound value-class digests scanned in deterministic primary-key order.
- Compare checks for `json_values`, `blob_values`, and `timestamp_values`.

These checks complement the full-row digest. They make the required acceptance checks explicit while still avoiding row payloads in verification artifacts.

## Verification Evidence

- `php -l mtool/app/sqlite_mysql_verification.php`
- `php -l tests/Integration/SqliteMysqlVerificationTest.php`
- Focused verification PHPUnit without dedicated MySQL schema:
  - `OK, but incomplete, skipped, or risky tests! Tests: 12, Assertions: 46, Skipped: 1.`
- Focused verification PHPUnit with isolated MariaDB schema `mtool_promotion_test_verify_values`:
  - `OK (12 tests, 50 assertions)`
- `make test`:
  - `OK, but incomplete, skipped, or risky tests! Tests: 577, Assertions: 15004, Skipped: 4.`

## Remaining #872 Work

Still not completed in this slice:

- Next-id / sequence check.
- Generated DBAccess smoke check.
- Final all-required-pass artifact using every required check from real collectors.

## Plan State

`docs/current-plans.md` #872 moved from `SCHEMA_COLLECTORS_DONE_REMAINING_VALUE_NEXT` to `VALUE_COLLECTORS_DONE_NEXT_ID_DBACCESS_NEXT`.
