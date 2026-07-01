# 2026-06-29 App-local Persistence Sample27 Demo

Status: `COMPLETED`

## Scope

App-local persistence first demo の round trip proof として、`sample27-app-local-persistence-demo` を追加した。

この sample は、generated TypeScript / browser runtime ではなく PHP PDO harness で先に end-to-end data path を固定する。

## Implemented

- Added tutorial sample pack:
  - `sample/tutorials/sample27-app-local-persistence-demo`
- Added seed project `SAMPLE27` with server fixture table `app_local_task`.
- Added `mtool/scripts/lib/sample27_app_local_persistence_demo_check.php`.
- Added `tests/Integration/Sample27AppLocalPersistenceDemoTest.php`.
- Registered sample27 in:
  - `Makefile`
  - `tests/bootstrap.php`
  - `tests/Integration/SamplePackCatalogTest.php`
  - `sample/tutorials/README.md`

## Result

The sample verifies:

```text
server row
-> shared contract DTO
-> App-local SQLite schema apply
-> App-local DBAccess save
-> App-local DBAccess read
-> DTO compare
```

The read DTO matches the server DTO, and local metadata remains separate with `dirty = 1`, `sync_status = dirty`, and `tombstone = 0`.

## Remaining

- Promote App-local schema / DBAccess into generated Source Output artifacts.
- Decide whether the first generated runtime target is PHP/PDO, TypeScript/Node, or browser SQLite.
- Add dirty/synced lifecycle helpers after the artifact boundary is stable.

## Verification

- `php -l mtool/scripts/lib/sample27_app_local_persistence_demo_check.php`
- `php -l tests/Integration/Sample27AppLocalPersistenceDemoTest.php`
- `make sample27-pack-runtime-test`
  - `1 test, 7 assertions`
- `make test`
  - `279 tests, 9376 assertions, Skipped: 1`
