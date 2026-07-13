# SQLite to MySQL Verification Core Collectors

Date: 2026-07-13

## Scope

This records the #872 promotion verification gate slice for the core data-equivalence collectors.

Implemented:

- Read-only SQLite/MySQL evidence collector for declared manifest tables.
- Deterministic PK-order scans.
- Exact per-table row count evidence.
- Complete primary-key tuple digest evidence.
- Complete canonical row-value digest evidence.
- Core comparison checks for `row_count`, `primary_key_set`, and `row_values`.
- BLOB verification encoding as byte length plus SHA-256, not base64 payload.
- JSON canonicalization through the existing sorted recursive representation.
- Strict scalar conversion reuse from the export path.

The collector returns only counts, stability flags, and SHA-256 digests. It does not return row payloads, credentials, DSNs, or mutable connection configuration.

## Verification Evidence

- `php -l mtool/app/sqlite_mysql_verification.php`
- `php -l tests/Integration/SqliteMysqlVerificationTest.php`
- Focused verification PHPUnit without dedicated MySQL schema:
  - `OK, but incomplete, skipped, or risky tests! Tests: 10, Assertions: 33, Skipped: 1.`
- Focused verification PHPUnit with isolated MariaDB schema `mtool_promotion_test_verify_core`:
  - `OK (10 tests, 35 assertions)`
- `make test`:
  - `OK, but incomplete, skipped, or risky tests! Tests: 575, Assertions: 14991, Skipped: 4.`

## Remaining #872 Work

Still not completed in this slice:

- Nullability check collector.
- Unique-key check collector.
- Foreign-key integrity collector.
- JSON/BLOB/timestamp focused check entries beyond the core row digest.
- Next-id / sequence check.
- Generated DBAccess smoke check.
- Final all-required-pass artifact using every required check from real collectors.

## Plan State

`docs/current-plans.md` #872 moved from `FIRST_SLICE_DONE_COLLECTORS_NEXT` to `CORE_DIGEST_COLLECTORS_DONE_REMAINING_CHECKS_NEXT`.
