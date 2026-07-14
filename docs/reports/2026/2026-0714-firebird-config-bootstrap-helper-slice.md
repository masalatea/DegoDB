# Firebird Config Bootstrap Helper Slice / Firebird config bootstrap helper スライス

Status: `F100_4_BOOTSTRAP_HELPER_SLICE_DONE`

This report records the next F100-4 slice after Firebird config/profile recognition. The goal was to move the Firebird config schema conversion out of a standalone feasibility script and into the formal `config_db_bootstrap.php` helper layer, without yet claiming full Mtool Firebird config-store completion.

この report は、Firebird config/profile 認識後の F100-4 次スライスを記録します。目的は、Firebird config schema 変換を standalone feasibility script だけのものから、正式な `config_db_bootstrap.php` helper layer に移すことです。ただし、まだ Mtool Firebird config-store 完了宣言ではありません。

## What changed / 変更点

- Added Firebird config bootstrap DDL helpers to `mtool/app/config_db_bootstrap.php`.
- Firebird CREATE TABLE conversion now handles:
  - MySQL `AUTO_INCREMENT` -> Firebird identity column
  - `UNSIGNED` integer normalization
  - `TINYINT(1)` -> `SMALLINT`
  - `TEXT` / `MEDIUMTEXT` -> `BLOB SUB_TYPE TEXT`
  - `DATETIME` -> `TIMESTAMP`
  - MySQL `ON UPDATE CURRENT_TIMESTAMP` removal
  - MySQL table options removal
  - dropping MySQL index / unique / foreign-key entries from inline CREATE TABLE conversion
- Firebird guarded ALTER support was added for `ALTER TABLE ... ADD COLUMN IF NOT EXISTS ...` by checking metadata before emitting `ALTER TABLE ... ADD ...`.
- Legacy cleanup statements such as `DROP INDEX IF EXISTS` and `ALTER TABLE ... DROP COLUMN IF EXISTS ...` are skipped in Firebird bootstrap preparation.
- `app_config_db_bootstrap_apply()` now routes Firebird config-initdb SQL through the Firebird preparation helper instead of executing MariaDB SQL directly.
- `mtool/scripts/firebird_config_schema_first_slice.php` now delegates column conversion to the formal bootstrap helper so the feasibility script does not drift from runtime/bootstrap behavior.

## Verification / 検証

- `php -l mtool/app/config_db_bootstrap.php`
- `php -l mtool/scripts/firebird_config_schema_first_slice.php`
- `php -l tests/Integration/ConfigDbExternalizationContractTest.php`
- `make test`
  - result: passed before the script-only helper delegation cleanup
  - summary: `Tests: 640, Assertions: 15425, Skipped: 5.`
- `make firebird-config-schema-first-slice-docker`
  - result: passed
  - result shape: `ok: true`
  - observed plan count: `5`
  - observed apply result on the already-prepared disposable DB: `applied: 0`, `skipped: 5`, `errors: []`

## Current boundary / 現在の境界

This is a bootstrap-helper slice, not a full config-store slice.

このスライスは bootstrap helper の入口であり、config-store 全体完了ではありません。

Still open:

1. Prove full config-initdb application against a fresh Firebird proof DB.
2. Prove repository read/write behavior for Firebird `BLOB SUB_TYPE TEXT` columns.
3. Add Firebird handling for any remaining non-DDL statements needed by config-initdb.
4. Audit Mtool repository SQL for Firebird-specific behavior.
5. Only after the above, close F100-4 and move to SQLite -> Firebird migration path.
