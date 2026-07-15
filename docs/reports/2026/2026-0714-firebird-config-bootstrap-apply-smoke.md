# Firebird Config Bootstrap Apply Smoke / Firebird config bootstrap apply smoke

Status: `F100_4_CONFIG_BOOTSTRAP_APPLY_SMOKE_DONE`

This report records the F100-4 checkpoint where the formal config bootstrap path was exercised against the Docker Firebird proof database through the normal `migrate_config_db.php` apply entry point.

この report は、正式な config bootstrap 経路を `migrate_config_db.php` の通常 apply entry point 経由で Docker Firebird proof DB に対して実行した F100-4 checkpoint を記録します。

## What changed / 変更点

- Fixed Firebird metadata helper SQL in `mtool/app/sql_dialect.php` so `RDB$GET_CONTEXT` and `RDB$DATABASE` are not broken by PHP variable interpolation.
- Improved `app_config_db_bootstrap_apply()` diagnostics by reporting the SQL file and statement excerpt when a bootstrap statement fails.
- Adjusted Firebird config bootstrap conversion:
  - `TEXT` / `MEDIUMTEXT` now map to `BLOB SUB_TYPE TEXT` to avoid Firebird row-size overflow.
  - Firebird BLOB text columns drop MySQL-style default literals that are not valid for BLOB columns.
  - `MEDIUMTEXT` no longer double-converts into `BLOB SUB_TYPE BLOB SUB_TYPE TEXT`.
  - explicit nullable markers such as `BIGINT UNSIGNED NULL` are normalized to nullable Firebird columns without the `NULL` token.
- Added a reusable Docker smoke service and Make target:
  - `firebird-config-bootstrap-apply`
  - `make firebird-config-bootstrap-apply-docker`

## Verification / 検証

- `php -l mtool/app/sql_dialect.php`
- `php -l mtool/app/config_db_bootstrap.php`
- `php -l mtool/scripts/firebird_config_schema_first_slice.php`
- `php -l tests/Integration/ConfigDbExternalizationContractTest.php`
- Direct full bootstrap probe:
  - result: `ok: true`
  - Firebird version: `5.0.4`
  - `schema_current: true`
  - `missing_tables: []`
  - `missing_columns: []`
  - `unexpected_legacy_columns: []`
- Reusable make target:
  - `make firebird-config-bootstrap-apply-docker`
  - result: `ok: true`
  - `schema_current: true`
- Full regression:
  - `make test`
  - result: passed
  - summary: `Tests: 642, Assertions: 15427, Skipped: 5.`

The proof DB was not reset during this checkpoint. Therefore `applied_file_count` reflects incremental apply against the existing Docker proof DB state, not a clean-database count. The important completion signal for this slice is that the normal bootstrap apply path reaches `schema_current=true` through the Firebird profile.

## Still open / 未完了

F100-4 is not yet fully complete. The next work should verify runtime repository behavior on the Firebird config store:

1. Repository SQL audit for MySQL-specific expressions, quoting, `LAST_INSERT_ID`, `LIMIT`, and update/insert behavior.
2. Read/write behavior for `BLOB SUB_TYPE TEXT` columns used by metadata JSON, notes, and large text fields.
3. A fresh-DB bootstrap run may be added as a stricter smoke once the test harness can safely reset only the Firebird proof volume.
4. After repository behavior is proven, F100-4 can move toward completion and F100-5 SQLite -> Firebird migration path can begin.
