# Firebird 100% checkpoint / Firebird 100%チェックポイント

Status: `FIREBIRD_100_QUALIFIED_FOR_AGREED_SCOPE`

This checkpoint closes the Firebird completion lane under the definition agreed on 2026-07-14.

ここでは、2026-07-14 に合意した定義に基づき、Firebird completion lane を閉じる。

## Completion definition / 完了定義

Firebird 100% does not mean every possible Firebird feature, every sample, or every migration direction.

Firebird 100% は、Firebird の全機能、全 sample、全 migration 方向を意味しない。

The agreed scope has two parts:

1. MySQL-equivalent Mtool/profile support for the supported local durable DB profile boundary.
2. Migration-path support for SQLite -> Firebird and Firebird -> MySQL/MariaDB.

合意済み scope は次の2つ。

1. supported local durable DB profile boundary における MySQL 同等の Mtool/profile support。
2. SQLite -> Firebird と Firebird -> MySQL/MariaDB の migration path support。

## Evidence / 根拠

Representative sample-first coverage is complete:

- sample05 read/select;
- sample08 join/read-model;
- sample09 aggregate/report;
- sample18 CRUD/list, no-code runtime, Transaction Full, guarded route;
- sample21 catalog relationship/read-model;
- sample22 workflow state transition;
- sample27 Firebird server DTO -> App-local SQLite persistence boundary.

Mtool/profile support is complete for the agreed opt-in boundary:

- config/dialect recognition;
- Firebird bootstrap DDL helpers;
- full config-initdb apply smoke;
- representative config-store repository evidence for Project, SourceOutput, DBAccess metadata, and AuditLog/BLOB text.

SQLite -> Firebird migration path is complete for the agreed one-way path:

- deterministic contract;
- Firebird target schema plan;
- SQLite export chunks;
- Firebird import rehearsal package;
- opt-in live Firebird import smoke;
- next-id candidates;
- backup/restore smoke.

Firebird -> MySQL/MariaDB migration path is complete for the agreed one-way path:

- pure Firebird source inspection -> MySQL/MariaDB promotion manifest;
- MySQL/MariaDB target schema plan;
- fixture-based Firebird export chunks;
- rehearsal package;
- opt-in live Firebird export smoke;
- Firebird-specific MySQL import wrapper;
- dedicated live MySQL/MariaDB import smoke;
- post-import verification adapter;
- cutover/operator package artifacts.

## Explicit non-goals / 明示的 non-goals

The following are intentionally not required for the Firebird 100% claim:

- Firebird -> SQLite downgrade path;
- bidirectional sync;
- zero-downtime CDC;
- automatic cutover;
- production native installer/distribution packaging;
- support for every Firebird-specific SQL feature;
- converting every historical sample into Firebird mode;
- making Mtool itself depend on PostgreSQL or Firebird by default.

## Product claim / product claim

Firebird is now qualified as 100% complete for the agreed Mtool/profile + migration-path scope.

Firebird は、合意済みの Mtool/profile + migration-path scope において 100% 完了とみなす。

Future Firebird work should be treated as new product scope, not as unfinished F100 work.

今後の Firebird 作業は、F100 の未完了ではなく、新しい product scope として扱う。

## Verification / 検証

Latest code verification from the closing slices:

- Firebird -> MySQL verification adapter:
  - focused PHPUnit `FirebirdMysqlPromotionRehearsalTest.php`: `Tests: 8, Assertions: 53, Skipped: 1.`
  - `make test`: `Tests: 658, Assertions: 15590, Skipped: 6.`
- Firebird -> MySQL cutover/operator package:
  - focused PHPUnit `FirebirdMysqlCutoverTest.php`: `OK (4 tests, 38 assertions)`
  - `make test`: `Tests: 662, Assertions: 15628, Skipped: 6.`

## Next / 次

There is no automatic next implementation lane from Firebird itself.

Firebird 自体から自動で続く実装 lane はない。

Possible next work should be selected explicitly from parked roadmap items or new user direction.

次の作業は、parked roadmap または新しいユーザー方針から明示的に選ぶ。
