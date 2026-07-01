# 2026-06-29 App-local Persistence First Slice

Status: `FIRST_SLICE_DONE`

## Scope

App-local persistence first demo の最初の実装 slice として、shared contract manifest v0 から App-local SQLite schema を生成し、PDO SQLite に apply できる app-layer API を追加した。

この slice は FS の結果を product code 側へ移す最小単位であり、App-local DBAccess、runtime / harness 選定、sample27 `server read -> DTO -> app save -> app read` はまだ残す。

## Implemented

- Added `mtool/app/app_local_sqlite_schema.php`.
- Input is the formal `shared-contract-manifest-v0` shape validated by `mtool/shared/shared_contract_core.php`.
- Generates:
  - `__app_local_schema_version` table.
  - business tables from contract `entity.physical_name`.
  - business field columns from contract field type / nullable / default / key metadata.
  - reserved local metadata columns after business fields:
    - `local_updated_at`
    - `last_synced_at`
    - `sync_status`
    - `dirty`
    - `tombstone`
  - sync helper index on `sync_status, dirty`.
- Adds `app_local_sqlite_schema_apply_to_pdo()` for applying generated DDL and inspecting the resulting tables / indexes.
- Added `tests/Integration/AppLocalSqliteSchemaGeneratorTest.php`.

## Result

The implementation confirms the FS result against the formal shared contract layer:

- DataClass remains implementation-facing.
- Shared contract manifest is the source for persistence semantics.
- DTO / business field shape can remain separate from local metadata.
- Reserved local metadata collisions are rejected by shared contract validation before DDL generation.

## Remaining

- Generate App-local DBAccess / repository helpers from the same contract.
- Decide the runtime/harness for the first demo path.
- Add sample27 to prove `server read -> DTO -> app save -> app read`.
- Extend local lifecycle beyond initial dirty/sync metadata defaults.

## Verification

- `php -l mtool/app/app_local_sqlite_schema.php`
- `php -l tests/Integration/AppLocalSqliteSchemaGeneratorTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/AppLocalSqliteSchemaGeneratorTest.php`
  - `3 tests, 28 assertions`
- `make test`
  - `275 tests, 9310 assertions, Skipped: 1`
