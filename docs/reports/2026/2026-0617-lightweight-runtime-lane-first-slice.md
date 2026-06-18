# 2026-06-17 Lightweight Runtime Lane First Slice

## Summary

folder-backed SQLite config store を使い、`db-config` service を起動しない Mtool lightweight lane を追加した。

目的は、ユーザーが config DB server を準備せず、保存フォルダだけで DegoDB / Mtool の設計メタデータを永続化できる起動導線を作ることである。

## Changes

- `mtool/docker/compose/01_mtool-lite.compose.yaml`
  - `mtool-scenario-01-mtool-lite` compose project を追加した。
  - `web-admin` / `web-lab` の `APP_WORK_ROOT` を lite scenario 用に分離した。
  - `db-config` service は含めない。
- `Makefile`
  - `COMPOSE_MTOOL_LITE` を追加した。
  - `up-mtool-lite` / `start-mtool-lite` / `stop-mtool-lite` / `down-mtool-lite` / `reset-mtool-lite` を追加した。
  - `ps-mtool-lite` / `logs-mtool-lite` / `health-mtool-lite` を追加した。
  - `config-db-preflight-mtool-lite` / `db-config-migrate-mtool-lite` を追加した。
  - lite lane の default `APP_CONFIG_STORE_DIR` は `work/config-store` とした。
- `mtool/app/config.php`
  - admin site で SQLite config store を使い、admin default DB target が compose default `db-config` のままなら、`db` も SQLite config store に寄せるようにした。
  - これにより admin health が config DB server なしでも SQLite config store を probe できる。
- `mtool/app/database.php`
  - DB probe の version query を dialect helper 経由にした。
  - SQLite profile では `SELECT sqlite_version()` 相当で health detail を返す。
- `tests/Integration/ConfigStoreProfileTest.php`
  - admin default DB が SQLite config store に寄ることを固定した。
  - 明示的な external `APP_DB_HOST` は上書きしないことを固定した。
- docs
  - `docs/quickstart.md`
  - `docs/start-here.md`
  - `docs/common-tasks.md`
  - `docs/reports/2026/2026-0617-lightweight-sqlite-persistence-plan.md`

## User-Facing Shape

```bash
APP_CONFIG_STORE_DIR=work/config-store make up-mtool-lite
make health-mtool-lite
make config-db-preflight-mtool-lite
make down-mtool-lite
```

`db-lab` は user / Lab DB として残る。DegoDB 自身の設計メタデータだけが SQLite file store に保存される。

## Smoke

```bash
APP_CONFIG_STORE_DIR=work/config-store-lite-smoke \
ADMIN_HTTP_PORT=18091 \
LAB_HTTP_PORT=18092 \
LAB_DB_HOST_PORT=43092 \
LAB_DB_UI_HTTP_PORT=18093 \
make up-mtool-lite

APP_CONFIG_STORE_DIR=work/config-store-lite-smoke \
ADMIN_HTTP_PORT=18091 \
LAB_HTTP_PORT=18092 \
LAB_DB_HOST_PORT=43092 \
LAB_DB_UI_HTTP_PORT=18093 \
make health-mtool-lite

APP_CONFIG_STORE_DIR=work/config-store-lite-smoke \
ADMIN_HTTP_PORT=18091 \
LAB_HTTP_PORT=18092 \
LAB_DB_HOST_PORT=43092 \
LAB_DB_UI_HTTP_PORT=18093 \
make config-db-preflight-mtool-lite
```

Result:

- `db-config` service is not present in the lite compose service list.
- admin health returns SQLite version `3.45.1`.
- lab health returns MariaDB version from `db-lab`.
- config DB preflight returns `schema_current: true`.
- `admin_db_matches_config_db: true`.

## Boundary

- MTOOL core seed is not yet loaded into SQLite config store.
- tutorial sample dual-profile gates are complete for `sample01`, `sample10`, and `sample11`.
- sample runner detects SQLite config store and applies supported sample seed SQL to SQLite.
- SQLite file backup / restore / rotation is available through Makefile targets.

## Follow-up

- Add `sample01` SQLite config store gate. done.
- Extend `sample10` / `sample11` after `sample01` is stable.
- Add SQLite-safe backup / restore / rotation. done.

## Follow-up Result: sample01 SQLite Gate

Added after the lane first slice:

- `mtool/scripts/apply_config_sample_seed_sqlite.php`
  - Applies first-slice sample seed SQL to a SQLite config store.
  - Handles sample seed variables, stripped MySQL upsert tails, simple MySQL delete alias syntax, and current DDL conversion.
- `mtool/scripts/apply_config_sample_seed.sh`
  - Detects SQLite config store and delegates to the SQLite seed applier.
- `mtool/scripts/run_sample_pack_phpunit_test.sh`
  - Waits for SQLite config store preflight instead of `db-config` when the pack uses SQLite.
- `sample/_pack-support/sample-pack-runner.sh`
  - Allows base lane / lifecycle overlay control for packs that intentionally omit `db-config`.
- `sample/tutorials/sample01-simple-table-runtime/compose.sqlite-config.yaml`
- `sample/tutorials/sample01-simple-table-runtime/run-sqlite-config.sh`
- `Makefile`
  - Adds `sample01-pack-runtime-test-sqlite`.
- `mtool/app/project_table_import_source.php`
  - Adds SQLite live schema introspection via `sqlite_schema` / `PRAGMA table_info`.
- `mtool/app/project_table_import_service.php`
  - Quotes the legacy `IsNull` column when importing table metadata through SQLite.

Verification:

```bash
make sample01-pack-runtime-test-sqlite
```

Result:

- `OK (1 test, 12 assertions)`

## Follow-up Result: sample10 / sample11 SQLite Gates

Added after the sample01 gate:

- `sample/tutorials/sample10-dbaccess-mini-crud-flow/compose.sqlite-config.yaml`
- `sample/tutorials/sample10-dbaccess-mini-crud-flow/run-sqlite-config.sh`
- `sample/tutorials/sample11-html-template-output/compose.sqlite-config.yaml`
- `sample/tutorials/sample11-html-template-output/run-sqlite-config.sh`
- `Makefile`
  - Adds `sample10-pack-runtime-test-sqlite`.
  - Adds `sample11-pack-runtime-test-sqlite`.
- `mtool/scripts/apply_config_sample_seed_sqlite.php`
  - Handles MySQL `DELETE alias FROM ... JOIN ... WHERE ...` seed cleanup statements.
  - Handles `SET @var = LAST_INSERT_ID()`.

Verification:

```bash
make sample10-pack-runtime-test-sqlite
make sample11-pack-runtime-test-sqlite
```

Result:

- `sample10`: `OK (1 test, 26 assertions)`
- `sample11`: `OK (1 test, 6 assertions)`

## Follow-up Result: SQLite Backup / Restore

Added after the sample gates:

- `mtool/scripts/config_store_sqlite_backup.php`
  - Creates SQLite config store backups with `VACUUM INTO`.
  - Writes `.manifest.json` next to each backup.
  - Supports keep-days / keep-count rotation.
  - Requires `CONFIRM_RESTORE=yes` for restore.
  - Creates a pre-restore backup and runs `PRAGMA integrity_check` after restore.
- `Makefile`
  - Adds `backup-config-db-sqlite`.
  - Adds `backup-config-db-sqlite-rotate`.
  - Adds `restore-config-db-sqlite`.
  - Adds `backup-config-db-mtool-lite` aliases for the lightweight lane.

Smoke:

```bash
APP_CONFIG_STORE_DIR=work/tmp/config-store-backup-smoke \
CONFIG_DB_BACKUP_DIR=work/tmp/config-store-backup-smoke-backups \
make backup-config-db-sqlite

APP_CONFIG_STORE_DIR=work/tmp/config-store-backup-smoke \
CONFIG_DB_BACKUP_DIR=work/tmp/config-store-backup-smoke-backups \
make restore-config-db-sqlite BACKUP_FILE=... CONFIRM_RESTORE=yes

APP_CONFIG_STORE_DIR=work/tmp/config-store-backup-smoke \
CONFIG_DB_BACKUP_DIR=work/tmp/config-store-backup-smoke-backups \
CONFIG_DB_BACKUP_KEEP_COUNT=1 \
CONFIG_DB_BACKUP_KEEP_DAYS=0 \
make backup-config-db-sqlite-rotate
```

Result:

- backup created `.sqlite` and `.manifest.json`.
- restore returned `integrity_check: ok`.
- rotation deleted older SQLite backups and manifests.

## Follow-up Result: sample12 SQLite Gate

Added after backup / restore:

- `sample/tutorials/sample12-external-db-source-import/compose.sqlite-config.yaml`
- `sample/tutorials/sample12-external-db-source-import/run-sqlite-config.sh`
- `Makefile`
  - Adds `sample12-pack-runtime-test-sqlite`.

This gate keeps `db-lab` as the external / user DB and stores only DegoDB design metadata in the SQLite config store.

Verification:

```bash
make sample12-pack-runtime-test-sqlite
```

Result:

- `OK (1 test, 8 assertions)`

## Follow-up Result: sample13 SQLite Gate

Added after sample12:

- `sample/tutorials/sample13-openapi-api-surface/compose.sqlite-config.yaml`
- `sample/tutorials/sample13-openapi-api-surface/run-sqlite-config.sh`
- `Makefile`
  - Adds `sample13-pack-runtime-test-sqlite`.

Verification:

```bash
make sample13-pack-runtime-test-sqlite
```

Result:

- `OK (1 test, 13 assertions)`

## Follow-up Result: sample14 SQLite Gate

Added after sample13:

- `sample/tutorials/sample14-custom-proxy-runtime/compose.sqlite-config.yaml`
- `sample/tutorials/sample14-custom-proxy-runtime/run-sqlite-config.sh`
- `Makefile`
  - Adds `sample14-pack-runtime-test-sqlite`.

Verification:

```bash
make sample14-pack-runtime-test-sqlite
```

Result:

- `OK (1 test, 16 assertions)`

## Follow-up Result: sample15 SQLite Gate

Added after sample14:

- `sample/tutorials/sample15-project-metadata-export-import/compose.sqlite-config.yaml`
- `sample/tutorials/sample15-project-metadata-export-import/run-sqlite-config.sh`
- `Makefile`
  - Adds `sample15-pack-runtime-test-sqlite`.
- `mtool/app/project_metadata_bundle.php`
  - Quotes the legacy `IsNull` column when importing bundle table metadata through SQLite.
- `mtool/scripts/lib/sample15_project_metadata_export_import_check.php`
  - Keeps exact bundle section comparison for MySQL / MariaDB.
  - Uses profile-aware section comparison for SQLite because live schema type names differ.

Verification:

```bash
make sample15-pack-runtime-test-sqlite
```

Result:

- `OK (1 test, 8 assertions)`

## Follow-up Result: sample16 SQLite Gate

Added after sample15:

- `sample/tutorials/sample16-authenticated-proxy/compose.sqlite-config.yaml`
- `sample/tutorials/sample16-authenticated-proxy/run-sqlite-config.sh`
- `Makefile`
  - Adds `sample16-pack-runtime-test-sqlite`.

Verification:

```bash
make sample16-pack-runtime-test-sqlite
```

Result:

- `OK (1 test, 31 assertions)`

## Follow-up Result: sample17 SQLite Gate

Added after sample16:

- `sample/tutorials/sample17-multi-output-project/compose.sqlite-config.yaml`
- `sample/tutorials/sample17-multi-output-project/run-sqlite-config.sh`
- `Makefile`
  - Adds `sample17-pack-runtime-test-sqlite`.

Verification:

```bash
make sample17-pack-runtime-test-sqlite
```

Result:

- `OK (1 test, 7 assertions)`

## Follow-up Result: Generated DBAccess SQLite Runtime Adapter

Added after the sample17 SQLite gate:

- `mtool/app/project_output_db_access_generator.php`
  - Adds generated `_support/mtool_runtime_db.php`.
  - Keeps the legacy `$mtooldb` surface while allowing `MTOOL_RUNTIME_DB_DSN=sqlite:/path/app.sqlite`.
- `mtool/app/project_db_access_bootstrap_service.php`
  - Emits the same runtime DB support file for bootstrap-generated DBAccess trees.
- `mtool/reference/source-templates/canonical-dbaccess-php/base.php.tpl`
  - Requires the runtime DB support file from generated base classes.
- `tests/Integration/ProjectDbAccessBootstrapRuntimeContractTest.php`
  - Verifies a generated DBAccess-style class can insert and select through a SQLite DSN.

Verification:

```bash
make test
make sample17-pack-runtime-test-sqlite
```

Result:

- `make test`: `OK (173 tests, 7083 assertions)`
- `sample17-pack-runtime-test-sqlite`: `OK (1 test, 7 assertions)`

## Follow-up Result: Generated DBAccess Write Prepared Statements

Added after the runtime adapter:

- `mtool/app/project_output_db_access_generator.php`
  - Adds `execute($sql, $params)` to generated runtime DB support.
- `mtool/app/project_output_runtime_sql_generator.php`
  - Emits prepared `INSERT` / `UPDATE` / `DELETE` generated DBAccess methods.
  - At this slice, generated `SELECT` methods still used the existing query path until the later read prepared slice.
- sample DBACCESS-PHP references
  - Updated generated write methods to use `?` placeholders and bound parameter arrays.

Verification:

```bash
make test
make sample17-pack-runtime-test-sqlite
```

Result:

- `make test`: `OK (173 tests, 7083 assertions)`
- `sample17-pack-runtime-test-sqlite`: `OK (1 test, 7 assertions)`

## Follow-up Result: Generated DBAccess SELECT Prepared Statements

Added after the write prepared slice:

- `mtool/app/project_output_runtime_sql_generator.php`
  - Emits prepared `SELECT` generated DBAccess methods using static SQL, `?` placeholders, and bound parameter arrays.
  - Handles `WHERE`, `HAVING`, and argument `LIMIT` parameters through the same runtime `execute($sql, $params)` surface.
  - Delegates raw argument data type instead of inlining user-provided SQL fragments.
- `mtool/app/project_output_db_access_generator.php`
  - Fixes generated base-class support loading to use `__DIR__ . '/../_support/mtool_runtime_db.php'`.
- sample DBACCESS-PHP references
  - Updated `sample01` / `sample05` / `sample06` / `sample07` / `sample08` / `sample09` / `sample10` / `sample17` read methods to use prepared SELECT output.

Verification:

```bash
make test
make sample17-pack-runtime-test-sqlite
```

Result:

- `make test`: `OK (173 tests, 7083 assertions)`
- `sample17-pack-runtime-test-sqlite`: `OK (1 test, 7 assertions)`

## Follow-up Result: Bootstrap DBAccess Prepared Statements And Proxy Bundle Smoke

Added after generated DBAccess SELECT prepared statements:

- `mtool/app/project_db_access_bootstrap_service.php`
  - Emits bootstrap-generated `SELECT` / `INSERT` / `UPDATE` / `DELETE` methods with static SQL, `?` placeholders, and `execute($sql, $params)`.
  - Keeps the generated runtime DB support file in bootstrap runtime trees.
- `mtool/scripts/lib/sample16_authenticated_proxy_check.php`
  - Compares generated proxy bundle `_support/runtime_dbclasses` files, including `_support/mtool_runtime_db.php` and the bootstrap-generated DBAccess base.
- `tests/Integration/Sample16AuthenticatedProxyTest.php`
  - Verifies the generated proxy bundle includes the runtime DB adapter and prepared bootstrap SELECT.
- `sample/tutorials/sample16-authenticated-proxy/reference/AUTH-PROXY-SERVER`
  - Updated reference output for the runtime dbclasses support tree.

Verification:

```bash
make test
make sample16-pack-runtime-test-sqlite
make sample17-pack-runtime-test-sqlite
```

Result:

- `make test`: `OK (173 tests, 7106 assertions)`
- `sample16-pack-runtime-test-sqlite`: `OK (1 test, 31 assertions)`
- `sample17-pack-runtime-test-sqlite`: `OK (1 test, 7 assertions)`
