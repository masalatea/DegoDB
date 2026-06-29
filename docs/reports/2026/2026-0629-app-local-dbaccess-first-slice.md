# 2026-06-29 App-local DBAccess First Slice

Status: `FIRST_SLICE_DONE`

## Scope

App-local persistence first demo の次 slice として、formal shared contract manifest v0 を使い、DTO-shaped row を App-local SQLite schema へ保存・読み戻しする generic DBAccess helper を追加した。

この slice は generated TypeScript / browser runtime をまだ選ばず、manifest-backed の保存・読み戻し境界を app-layer API として固定する。

## Implemented

- Added `mtool/app/app_local_sqlite_dbaccess.php`.
- Added `tests/Integration/AppLocalSqliteDbAccessTest.php`.
- `app_local_sqlite_dbaccess_save_dto()`:
  - resolves DTO `generated_name` fields to SQLite `physical_name` columns.
  - converts shared contract types to SQLite storage values.
  - upserts by contract key fields.
  - writes local metadata defaults: `dirty = 1`, `sync_status = dirty`, `tombstone = 0`.
- `app_local_sqlite_dbaccess_read_dto()`:
  - reads by DTO key.
  - converts SQLite values back to DTO shape.
  - returns local metadata separately from `dto`.
- Missing DTO contract fields fail closed before SQL execution.

## Result

This turns the DTO Save/Read FS result into reusable product code:

- DTO shape is driven by shared contract `generated_name`.
- SQLite schema shape is driven by shared contract `physical_name`.
- local metadata remains outside DTO shape.
- save/read is reused by sample27 and can later be promoted into generated App-local DBAccess source output.

## Remaining

- Publish App-local schema / DBAccess as Source Output artifacts.
- Decide whether the first generated runtime target is PHP/PDO, TypeScript/Node, or browser SQLite.
- Add dirty/synced lifecycle helpers after the round trip is pinned.

## Verification

- `php -l mtool/app/app_local_sqlite_dbaccess.php`
- `php -l tests/Integration/AppLocalSqliteDbAccessTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/AppLocalSqliteDbAccessTest.php`
  - `3 tests, 26 assertions`
- `make test`
  - `278 tests, 9336 assertions, Skipped: 1`
