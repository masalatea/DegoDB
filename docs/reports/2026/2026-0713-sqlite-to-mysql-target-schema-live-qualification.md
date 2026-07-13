# SQLite-to-MySQL Target Schema Live Qualification

Status: `DONE_MYSQL_MARIADB_LIVE`

## Result

#869 is complete for the V1 MySQL/MariaDB boundary.

An isolated temporary schema named `mtool_promotion_test_live` was created on the local MariaDB test server. The test used the normal application DB user only inside that dedicated schema.

The qualification proved:

- initial read-only inspection reported an empty target;
- an approved schema plan created parent then child tables;
- primary, composite unique, and foreign-key DDL was accepted by MariaDB;
- reinspection returned exactly `parent` and `record`;
- the FK exists in `information_schema.REFERENTIAL_CONSTRAINTS`;
- a second apply failed closed with `target_not_empty` and did not alter existing objects;
- test cleanup removed created tables;
- the temporary database was dropped after the test.

The live test is opt-in through `PROMOTION_MYSQL_TEST_DB` and requires a name matching `mtool_promotion_test_*`. The normal suite skips it so ordinary tests never create a target schema unexpectedly.

## Verification

- opt-in live focused PHPUnit: `OK (4 tests, 22 assertions)`;
- full `make test`: `Tests: 558, Assertions: 14932, Skipped: 2`;
- the extra skip is the intentionally disabled live-schema test;
- `git diff --check` passed.

#870, deterministic SQLite export and value conversion, is now the active next work unit.
