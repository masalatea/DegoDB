# SQLite to MySQL Verification Final Gate

Date: 2026-07-13

## Scope

This records the #872 completion slice for DBAccess smoke and final verification artifact assembly.

Implemented:

- Versioned `sqlite-mysql-dbaccess-smoke-v1` smoke artifact.
- DBAccess smoke check requiring:
  - MySQL target driver.
  - no mutation performed.
  - at least one operation.
  - every operation passed.
  - SHA-256 result digest for every operation.
  - no secret-bearing fields.
- Required-check assembly from real collectors:
  - `row_count`
  - `primary_key_set`
  - `row_values`
  - `nullability`
  - `unique_keys`
  - `foreign_keys`
  - `json_values`
  - `blob_values`
  - `timestamp_values`
  - `next_ids`
  - `dbaccess_smoke`
- Final artifact builder that passes the assembled checks to the existing all-required-pass gate.

The verification layer still does not guess generated DBAccess method names. A generated output or app-specific smoke runner must provide the versioned DBAccess smoke artifact. The gate verifies the artifact and fails closed when it is missing, mutating, failed, malformed, or secret-bearing.

## Verification Evidence

- `php -l mtool/app/sqlite_mysql_verification.php`
- `php -l tests/Integration/SqliteMysqlVerificationTest.php`
- Focused verification PHPUnit without dedicated MySQL schema:
  - `OK, but incomplete, skipped, or risky tests! Tests: 16, Assertions: 64, Skipped: 1.`
- Focused verification PHPUnit with isolated MariaDB schema `mtool_promotion_test_verify_final`:
  - `OK (16 tests, 69 assertions)`
- `make test`:
  - `OK, but incomplete, skipped, or risky tests! Tests: 581, Assertions: 15022, Skipped: 4.`

## Plan State

`docs/current-plans.md` #872 moved from `NEXT_ID_DONE_DBACCESS_ARTIFACT_NEXT` to `DONE_V1_FULL_GATE`.
