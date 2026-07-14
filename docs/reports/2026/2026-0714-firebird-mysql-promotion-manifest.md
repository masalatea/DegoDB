# Firebird-to-MySQL Promotion Manifest / FirebirdからMySQL promotion manifest

Status: `F100_6_CUTOVER_OPERATOR_PACKAGE_DONE`

This report records the first F100-6 implementation slices after closing the F100-5 SQLite -> Firebird migration path proof.

## What changed / 変更点

- Added `mtool/app/firebird_mysql_promotion_manifest.php`.
- Added `tests/Integration/FirebirdMysqlPromotionManifestTest.php`.
- Added `mtool/app/firebird_mysql_promotion_rehearsal.php`.
- Added `tests/Integration/FirebirdMysqlPromotionRehearsalTest.php`.
- Added `mtool/scripts/check_sample34_firebird_mysql_export_smoke.php`.
- Added `mtool/scripts/check_sample34_firebird_mysql_import_smoke.php`.
- Added `sample34-firebird-mysql-export-smoke-docker`.
- Added `sample34-firebird-mysql-import-smoke-docker`.
- The new manifest is pure and side-effect-free:
  - no Firebird mutation;
  - no MySQL/MariaDB connection;
  - no filesystem writes;
  - no secret propagation.
- It converts normalized Firebird source inspection evidence into a MySQL/MariaDB promotion manifest.
- It now also derives pure target/rehearsal artifacts from that manifest:
  - MySQL/MariaDB target schema plan;
  - deterministic fixture-based Firebird export chunks;
  - rehearsal readiness package and chunk summary.
- It now includes an opt-in live Firebird export smoke that reads the disposable sample34 Firebird tables and emits the same export chunk contract without opening MySQL/MariaDB or mutating Firebird.
- It now includes a Firebird -> MySQL import wrapper for the Firebird-specific manifest/export versions:
  - explicit approval is required before driver checks or mutation;
  - MySQL driver is required;
  - checkpoint retry is supported;
  - chunk integrity and row shape are checked before/inside the transaction.
- It now includes a dedicated live Firebird -> MySQL/MariaDB import smoke:
  - starts disposable Firebird plus MariaDB services;
  - imports sample34 SQLite -> Firebird data first;
  - exports Firebird rows into the Firebird -> MySQL chunk contract;
  - creates the MySQL/MariaDB target schema;
  - imports all chunks with explicit approval;
  - verifies row counts, JSON, BLOB, and checkpoint retry.
- It now includes the first Firebird -> MySQL/MariaDB post-import verification adapter:
  - builds source evidence from Firebird export chunks without mutating Firebird;
  - reuses the SQLite -> MySQL verification checks where the digest/schema semantics are shared;
  - adapts BLOB evidence from Firebird export `base64` envelopes to target-side `sha256` envelopes;
  - collects MySQL/MariaDB target evidence with the Firebird promotion manifest shape;
  - emits a Firebird-specific verification artifact version;
  - detects target row drift as a blocker.
- It now includes a Firebird -> MySQL/MariaDB cutover/operator package layer:
  - the cutover plan requires ready Firebird verification, frozen writes, final source/verification digests, post-cutover smoke evidence, rollback retention, and explicit approvals;
  - the operator package is side-effect-free and only carries safe references to switch, smoke, backup, rollback, and rehearsal artifacts;
  - inline commands, secrets, automatic apply, and source deletion fail closed.

## Boundary / 境界

Covered:

1. Firebird source inspection must already be ready;
2. canonical metadata remains the design authority;
3. deterministic foreign-key load order is reused;
4. stable primary keys are required;
5. Firebird constraints are compared against canonical metadata;
6. source backup and Firebird backup/restore smoke remain required gates;
7. target MySQL/MariaDB must be empty;
8. MySQL/MariaDB target schema plan is derived without touching the target;
9. fixture-based export chunks validate row count, stable ordering, JSON conversion, BLOB encoding, and resume markers;
10. rehearsal package proves the artifact chain is ready before any live import/cutover;
11. live Firebird export adapter reads sample34 tables through PDO_FIREBIRD and proves the same export summary without mutation;
12. Firebird -> MySQL import wrapper has fast approval/driver fail-closed tests and an opt-in live MySQL commit/checkpoint test;
13. dedicated CLI/docker live MySQL/MariaDB import smoke verifies a disposable target;
14. post-import verification can compare Firebird export evidence with MySQL/MariaDB target evidence and produce a cutover-ready/blocker artifact;
15. cutover/operator packaging can produce side-effect-free review artifacts that require explicit approvals;
16. Firebird -> SQLite, bidirectional sync, zero-downtime CDC, and automatic cutover remain non-goals.

Not covered yet:

1. final F100-6 closure decision and Firebird 100% checkpoint wording.

## Verification / 検証

```bash
php -l mtool/app/firebird_mysql_promotion_manifest.php
php -l tests/Integration/FirebirdMysqlPromotionManifestTest.php
php -l mtool/app/firebird_mysql_promotion_rehearsal.php
php -l tests/Integration/FirebirdMysqlPromotionRehearsalTest.php
php -l mtool/scripts/check_sample34_firebird_mysql_export_smoke.php
php -l mtool/scripts/check_sample34_firebird_mysql_import_smoke.php
php -l mtool/app/firebird_mysql_cutover.php
php -l tests/Integration/FirebirdMysqlCutoverTest.php
Focused PHPUnit FirebirdMysqlPromotionRehearsalTest.php
Focused PHPUnit FirebirdMysqlCutoverTest.php
make sample34-firebird-mysql-export-smoke-docker
make sample34-firebird-mysql-import-smoke-docker
make test
```

Results:

- `php -l mtool/app/firebird_mysql_promotion_manifest.php`: passed
- `php -l tests/Integration/FirebirdMysqlPromotionManifestTest.php`: passed
- `php -l mtool/app/firebird_mysql_promotion_rehearsal.php`: passed
- `php -l tests/Integration/FirebirdMysqlPromotionRehearsalTest.php`: passed
- `php -l mtool/scripts/check_sample34_firebird_mysql_export_smoke.php`: passed
- `php -l mtool/scripts/check_sample34_firebird_mysql_import_smoke.php`: passed
- `php -l mtool/app/firebird_mysql_cutover.php`: passed
- `php -l tests/Integration/FirebirdMysqlCutoverTest.php`: passed
- focused PHPUnit `FirebirdMysqlPromotionRehearsalTest.php`: passed
  - summary: `OK (4 tests, 31 assertions)`
- focused PHPUnit `FirebirdMysqlPromotionRehearsalTest.php` after import wrapper: passed
  - summary: `Tests: 7, Assertions: 41, Skipped: 1.`
- focused PHPUnit `FirebirdMysqlPromotionRehearsalTest.php` after Firebird -> MySQL verification adapter: passed
  - summary: `Tests: 8, Assertions: 53, Skipped: 1.`
- focused PHPUnit `FirebirdMysqlCutoverTest.php`: passed
  - summary: `OK (4 tests, 38 assertions)`
- first `make sample34-firebird-mysql-export-smoke-docker` passed the prerequisite sample34 Firebird import smoke, then failed at Docker buildx sandbox activity-file write
- escalated `make sample34-firebird-mysql-export-smoke-docker`: passed
  - stage: `sample34_firebird_mysql_export_smoke`
  - summary: `table_count=2`, `chunk_count=3`, `row_count=3`, `mutation_performed=false`
- first `make sample34-firebird-mysql-import-smoke-docker` passed the prerequisite sample34 Firebird import smoke, then failed at Docker buildx sandbox activity-file write
- escalated `make sample34-firebird-mysql-import-smoke-docker`: passed
  - stage: `sample34_firebird_mysql_import_smoke`
  - checkpoint completed count: `3`
  - verification: `parent=1`, `record=2`, JSON/BLOB/checkpoint retry passed
- first `make test` found one assertion-order issue in the new test; the expected order was corrected
- `make test`: passed
  - summary: `Tests: 650, Assertions: 15537, Skipped: 5.`
- second `make test` after target schema/export/rehearsal artifacts: passed
  - summary: `Tests: 652, Assertions: 15563, Skipped: 5.`
- third `make test` after live Firebird export adapter/smoke wiring: passed
  - summary: `Tests: 654, Assertions: 15568, Skipped: 5.`
- fourth `make test` after Firebird -> MySQL import wrapper: passed
  - summary: `Tests: 657, Assertions: 15578, Skipped: 6.`
- fifth `make test` after dedicated live MySQL/MariaDB import smoke wiring: passed
  - summary: `Tests: 657, Assertions: 15578, Skipped: 6.`
- sixth `make test` after Firebird -> MySQL verification adapter: passed
  - summary: `Tests: 658, Assertions: 15590, Skipped: 6.`
- seventh `make test` after Firebird -> MySQL cutover/operator package: passed
  - summary: `Tests: 662, Assertions: 15628, Skipped: 6.`

## Next / 次

F100-6 should now move to closure review:

1. decide whether the agreed Firebird -> MySQL/MariaDB migration-path scope is complete;
2. update the Firebird 100% checkpoint wording with explicit non-goals.
