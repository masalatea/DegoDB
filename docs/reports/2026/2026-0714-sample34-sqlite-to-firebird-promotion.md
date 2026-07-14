# Sample34 SQLite-to-Firebird Promotion / sample34 SQLiteからFirebird promotion

Status: `F100_5_DONE_SQLITE_TO_FIREBIRD_PATH`

This report records the first F100-5 implementation slice after closing the representative F100-4 Mtool Firebird profile boundary. It was then extended from a contract-only chain into a side-effect-free SQLite export / Firebird import rehearsal artifact, and then into an opt-in live Firebird import smoke.

この report は、代表 F100-4 Mtool Firebird profile boundary を close した後の F100-5 first implementation slice を記録します。

## What changed / 変更点

- Added `sample/tutorials/sample34-sqlite-to-firebird-promotion`.
- Added `reference/promotion-contract-input.json` as a deterministic fixture containing:
  - canonical table metadata;
  - SQLite inspection snapshot;
  - Firebird target identity option;
  - expected table order and verification keys.
- Added `mtool/app/sqlite_firebird_promotion_rehearsal.php`.
- Added `mtool/scripts/validate_sample34_firebird_promotion.php`.
- Added `mtool/scripts/check_sample34_firebird_promotion_smoke.php`.
- Added `make sample34-firebird-promotion-smoke-docker` and compose service `sample34-firebird-promotion-smoke`.
- Added `tests/Integration/Sample34SqliteFirebirdPromotionTest.php`.
- Registered `sample34` as a `promotion-tutorial-sample` in the sample pack catalog.
- Updated tutorial/sample/proof docs to include the SQLite-to-Firebird promotion lane.

## Boundary / 境界

This slice does not switch the application profile yet. It turns the existing side-effect-free SQLite -> Firebird contract shape into a user-facing, tested tutorial artifact, exercises deterministic SQLite row export into an import rehearsal package, and proves the live Firebird import path against the opt-in Docker Firebird proof database.

この slice ではまだ data copy は実行しません。既存の副作用なし SQLite -> Firebird contract shape を、user-facing かつ test 済み tutorial artifact に昇格しました。

Covered:

1. canonical snapshot and SQLite inspection are compared;
2. foreign-key load order is deterministic (`parent`, then `record`);
3. Firebird target type mapping covers integer, boolean, decimal, JSON/text, text BLOB, timestamp, and binary BLOB;
4. source retention remains required;
5. Firebird target must be new or empty;
6. backup/restore smoke and explicit local profile switch remain required gates;
7. Firebird DDL is planned from the contract;
8. SQLite source rows are exported in deterministic primary-key order;
9. JSON/text BLOB, binary BLOB, timestamp, nullable, and boolean-like values are encoded in the export artifact;
10. an import rehearsal checkpoint validates row/chunk counts and keeps local profile switch explicit;
11. live Firebird smoke creates the target tables, inserts fixture rows transactionally, and verifies row counts plus PK/FK/JSON/text BLOB/binary BLOB/timestamp evidence;
12. validator performs no mutation; live smoke explicitly reports `mutation_performed: true`.

Not covered yet:

1. profile switch execution;
2. Firebird -> MySQL/MariaDB promotion.

## Verification / 検証

Verification for this slice:

```bash
php -l mtool/scripts/validate_sample34_firebird_promotion.php
php -l mtool/app/sqlite_firebird_promotion_rehearsal.php
php -l mtool/scripts/check_sample34_firebird_promotion_smoke.php
bash -n mtool/scripts/check_sample34_firebird_backup_restore_smoke.sh
php mtool/scripts/validate_sample34_firebird_promotion.php
php mtool/scripts/validate_sample34_firebird_promotion.php --json
make -n sample34-firebird-promotion-smoke-docker
make sample34-firebird-promotion-smoke-docker
make sample34-firebird-backup-restore-smoke-docker
phpunit tests/Integration/Sample34SqliteFirebirdPromotionTest.php
phpunit tests/Integration/SamplePackCatalogTest.php
make test
```

Results:

- `php -l mtool/scripts/validate_sample34_firebird_promotion.php`: passed
- `php -l mtool/app/sqlite_firebird_promotion_rehearsal.php`: passed
- `php -l mtool/scripts/check_sample34_firebird_promotion_smoke.php`: passed
- `bash -n mtool/scripts/check_sample34_firebird_backup_restore_smoke.sh`: passed
- `php mtool/scripts/validate_sample34_firebird_promotion.php`: passed / `OK: yes`; `Rehearsal stage: firebird_import_rehearsal_ready`
- `php mtool/scripts/validate_sample34_firebird_promotion.php --json`: passed / `ok: true`; import rehearsal ready
- `make -n sample34-firebird-promotion-smoke-docker`: passed
- first live smoke found two real Firebird differences and they were corrected:
  - Firebird requires `DEFAULT` before `NOT NULL` in this DDL path;
  - the sample timestamp fixture was adjusted from 6 fractional digits to 4 fractional digits for this Firebird smoke.
- `make sample34-firebird-promotion-smoke-docker`: passed / `stage: sample34_firebird_live_import_smoke`; row counts `parent=1`, `record=2`; JSON/text BLOB, binary BLOB, timestamp, PK, FK, and next-id candidate checks passed
- `make sample34-firebird-backup-restore-smoke-docker`: passed / `stage: sample34_firebird_backup_restore_smoke`; restored DB row counts `parent=1`, `record=2`
- focused local `phpunit` was not available on the host, so PHPUnit coverage was verified through `make test`
- `make test`: passed
  - summary: `Tests: 646, Assertions: 15512, Skipped: 5.`

## Next / 次

F100 should continue to F100-6:

1. build a Firebird -> MySQL/MariaDB migration path artifact;
2. keep Firebird -> SQLite and bidirectional sync out of scope;
3. keep cutover/profile switch explicit and default-off.
