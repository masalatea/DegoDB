# 2026-06-17 Lightweight SQLite Persistence Plan

## Purpose

DegoDB / Mtool の設計データ永続化について、enterprise / team 利用では MySQL / MariaDB を主系として維持しつつ、lightweight / local / single-user 利用では folder-backed SQLite を選べるようにする計画を整理する。

この計画は SQLite への完全移行ではない。目的は、利用者の運用規模に応じて persistence profile を選べるようにすることである。

## Executive Summary

永続化の標準 profile は引き続き MySQL / MariaDB とする。

理由:

- team / enterprise 運用では server database、backup、monitoring、権限管理、migration 管理が自然である。
- 現在の `config_db` schema / repository / preflight は MariaDB 前提で動いている。
- external config DB lane はすでに current workflow として整備されている。

一方で、個人利用・試用・小規模運用では DB server の準備が導入障壁になる。そこで `config_db` 相当の canonical metadata store を SQLite file として持てる lightweight profile を追加する。

最終形:

- `mysql` profile
  - default / enterprise-ready / shared use
  - local MariaDB container または external MySQL / MariaDB
- `sqlite` profile
  - optional / local-first / single-user
  - user-specified folder に `config.sqlite` を保存
- backup profile
  - MySQL / MariaDB と SQLite のどちらにも明示的な backup / restore / rotation を提供
- sample / tutorial profile
  - tutorial sample は MySQL / MariaDB config store と SQLite config store の両方で検証できるようにする
  - lightweight user は folder-only SQLite で学習でき、enterprise user は server DB profile で同じ tutorial を追えるようにする

## Current Plan Inventory

2026-06-17 時点の棚卸し。

## Progress Sense

SQLite support は `Mtool 自身の保存先対応`、`sample / artifact parity`、`ユーザー DB 側の生成・接続対応` を分けて見る。

ざっくりした進捗感:

| Area | Approx. Progress | Reading |
| --- | ---: | --- |
| Mtool config store / lightweight runtime lane | 100% for current scope | folder-backed SQLite config store、bootstrap、preflight、migrate、MTOOL core seed、backup / restore、lite compose lane、admin / lab health、admin top page smoke を `make mtool-lite-smoke` で確認できる。 |
| Tutorial sample dual-profile gate / artifact parity | nearly 100% | sample01-17 は MySQL / MariaDB lane と SQLite lane の両方で実行でき、artifact parity は 597 files で `artifact parity OK`。今後は新規 sample 追加時に同時追加する運用課題。 |
| Generated runtime DBAccess SQLite adapter | 65-75% | common CRUD / SELECT first slice、prepared statement 化、sample13 / sample16 / sample17 系 smoke は通った。blob / file parameter、vendor-specific function、複雑 SQL は生成器で無理に抱えず、継承先 class の個別実装判断を残す。 |
| User DB side SQLite support | 50-60%, move to dialect framework | SQLite source introspection と runtime output expansion の first slice はあるが、SQLite 単独で完了を急がず、MySQL / MariaDB mainline と SQLite first expansion を揃える共通 dialect / DBAccess generation framework の中で完成度を上げる。 |
| Multi-DB support as a whole | parked beyond MySQL/SQLite | PostgreSQL / SQL Server などの他 DB server は must-have feature 完了後の後続候補。当面は実装しない。 |

このため、`SQLite は完了か` への答えは、DegoDB / Mtool 自身の保存先対応としては current scope で 100% 完了扱い。ユーザー DB としての SQLite は、SQLite 単独対応としてではなく multi-DB / dialect 共通化の中で完成度を上げる。

ユーザー DB 側の後続計画は [User DB Multi-DB Dialect Roadmap](2026-0617-user-db-multidb-dialect-roadmap.md) を正本にする。この roadmap では、当面は MySQL / MariaDB と SQLite に絞って、DBAccess class output、schema introspection、runtime adapter、dialect-aware contract comparison を共通 framework として育てる。PostgreSQL / SQL Server は parked / post-must-features とし、must-have feature 完了前には実装しない。

### Mtool-Side 100% Completion Criteria

Mtool 側 SQLite support は、次を満たした時点で 100% 完了扱いにする。

- folder-backed SQLite config store が documented entrypoint から起動できる。done.
- empty SQLite file / missing SQLite file から current config schema が自動 bootstrap される。done.
- config preflight / migrate / backup / restore / rotation が MySQL / MariaDB profile と同じ user-facing capability を持つ。done.
- MTOOL core seed を SQLite config store に適用できる。done.
- `up-mtool-lite` 系の lightweight lane が `db-config` service なしで起動・health check・preflight できる。done.
- tutorial sample01-17 が MySQL / MariaDB config store lane と SQLite config store lane の両方で通る。done.
- artifact parity が sample01-17 の generated output / artifact を MySQL / MariaDB lane と SQLite lane で比較し、差分なしを保証する。done.
- admin UI / script の主要操作を SQLite profile で smoke する。done via `make mtool-lite-smoke`.
- quickstart / manual に SQLite lightweight profile の導線と制約を明記する。done.
- 今後追加される tutorial sample は MySQL / MariaDB gate、SQLite gate、artifact parity capture metadata を同時に追加する運用にする。ongoing policy.

上記は DegoDB / Mtool 自身の保存先対応の完了条件であり、ユーザー DB 向けの dialect support 完了条件とは分ける。

Verification:

- `make mtool-lite-smoke`: passed. This starts the lightweight SQLite lane with a temporary `APP_CONFIG_STORE_DIR`, checks admin / lab health, checks the admin top page, runs config preflight, runs config migrate, applies 18 MTOOL core seed files to SQLite, rechecks preflight, creates a SQLite backup, restores it, and rechecks preflight.
- `make artifact-parity-test ARTIFACT_PARITY_RUN_ID=codex-all-samples`: passed across 597 captured files for sample01-17.

完了 / first slice done:

- MySQL / MariaDB profile は default のまま維持。
- `APP_CONFIG_STORE_DIR=work/config-store` だけで folder-backed SQLite config store を選べる。
- SQLite file は既定で `APP_CONFIG_STORE_DIR/config.sqlite`。
- `work/...` の host relative folder は Docker container 内では `/var/www/work/...` に解決する。
- SQLite file parent directory は connection 前に作成される。
- SQLite config store が未作成または空の場合、current config schema を初回 bootstrap で自動作成する。
- `config-db-preflight` は SQLite profile でも schema current 判定できる。
- `db-config-migrate-mtool` を追加し、明示的な config schema 再適用導線を持つ。
- `up-mtool-lite` / `health-mtool-lite` / `config-db-preflight-mtool-lite` / `down-mtool-lite` を追加し、`db-config` service なしで folder-backed SQLite config store を起動できる。
- `mtool-lite-smoke` を追加し、SQLite lightweight lane の admin / script / MTOOL core seed / backup / restore smoke を一括確認できる。
- `sample01-pack-runtime-test-sqlite` を追加し、最小 tutorial sample を SQLite config store profile でも検証できる。
- `sample02-pack-runtime-test-sqlite` を追加し、nullable / default / status dataclass tutorial sample を SQLite config store profile でも検証できる。
- `sample03-pack-runtime-test-sqlite` を追加し、lookup / helper dataclass tutorial sample を SQLite config store profile でも検証できる。
- `sample04-pack-runtime-test-sqlite` を追加し、parent / child dataclass tutorial sample を SQLite config store profile でも検証できる。
- `sample05-pack-runtime-test-sqlite` を追加し、DBAccess select basic tutorial sample を SQLite config store profile でも検証できる。
- `sample06-pack-runtime-test-sqlite` を追加し、DBAccess filter / sort / pagination tutorial sample を SQLite config store profile でも検証できる。
- `sample07-pack-runtime-test-sqlite` を追加し、DBAccess CRUD basic tutorial sample を SQLite config store profile でも検証できる。
- `sample08-pack-runtime-test-sqlite` を追加し、DBAccess join read model tutorial sample を SQLite config store profile でも検証できる。
- `sample09-pack-runtime-test-sqlite` を追加し、DBAccess aggregate report tutorial sample を SQLite config store profile でも検証できる。
- `sample10-pack-runtime-test-sqlite` を追加し、DB Access CRUD tutorial sample を SQLite config store profile でも検証できる。
- `sample11-pack-runtime-test-sqlite` を追加し、HTML Source Output tutorial sample を SQLite config store profile でも検証できる。
- `sample12-pack-runtime-test-sqlite` を追加し、external DB source import tutorial sample を SQLite config store profile でも検証できる。
- `sample13-pack-runtime-test-sqlite` を追加し、OpenAPI API surface tutorial sample を SQLite config store profile でも検証できる。
- `sample14-pack-runtime-test-sqlite` を追加し、custom proxy runtime tutorial sample を SQLite config store profile でも検証できる。
- `sample15-pack-runtime-test-sqlite` を追加し、project metadata bundle export / import tutorial sample を SQLite config store profile でも検証できる。
- `sample16-pack-runtime-test-sqlite` を追加し、authenticated proxy tutorial sample を SQLite config store profile でも検証できる。
- `sample17-pack-runtime-test-sqlite` を追加し、multi-output capstone tutorial sample を SQLite config store profile でも検証できる。
- MySQL / MariaDB config DB backup は rotation / manifest / restore 前 backup を持つ。
- SQLite file store backup / restore / rotation は `backup-config-db-sqlite` / `backup-config-db-sqlite-rotate` / `restore-config-db-sqlite` で実行できる。
- Docker / PHPUnit 環境に SQLite extension を追加済み。
- repository first-slice tests は SQLite config store 上で主要 metadata CRUD を広く確認済み。
- generated canonical DBAccess output は `_support/mtool_runtime_db.php` を同梱し、legacy `$mtooldb` surface のまま `MTOOL_RUNTIME_DB_DSN=sqlite:/path/app.sqlite` を扱える first slice を持つ。
- generated canonical DBAccess の `INSERT` / `UPDATE` / `DELETE` / `SELECT` は static SQL + bound params へ移行済み。
- bootstrap-generated DBAccess も `execute($sql, $params)` へ移行し、sample16 authenticated proxy bundle の `_support/runtime_dbclasses` を reference 比較対象に追加済み。
- sample16 authenticated proxy は MySQL / MariaDB と SQLite config store の両方で HTTP route smoke 済み。
- sample13 OpenAPI / Swagger viewer と referenced proxy route は MySQL / MariaDB と SQLite config store の両方で HTTP route smoke 済み。
- sample13 Swagger viewer の browser-side Try It Out は MySQL / MariaDB と SQLite config store の両方で smoke 済み。
- `make test` は `174 tests, 7119 assertions` で green。

残作業:

- tutorial sample の dual-profile gate は current lane の capstone `sample17` まで完了。今後 sample を追加する場合は MySQL / MariaDB と SQLite config store の両方を追加する。
- Mtool がユーザー DB 向けに出力する runtime / DB access class 自体の SQLite 対応。
  - canonical DBAccess PHP の runtime adapter first slice は完了。
  - generated canonical DBAccess の `INSERT` / `UPDATE` / `DELETE` / `SELECT` prepared statement 化 first slice は完了。
  - bootstrap-generated DBAccess の prepared statement 化と sample16 proxy bundle support smoke は完了。
  - sample16 authenticated proxy の HTTP-level runtime execution smoke は MySQL / MariaDB と SQLite config store の両方で完了。
  - sample13 OpenAPI / Swagger viewer と referenced proxy route の HTTP-level smoke は MySQL / MariaDB と SQLite config store の両方で完了。
  - sample13 Swagger viewer の browser-side Try It Out smoke は MySQL / MariaDB と SQLite config store の両方で完了。
  - blob / file parameter や vendor-specific function などの複雑系は、生成器で共通化できる範囲を超える場合、継承先 class の個別実装へ委ねる。
- ユーザー DB 側の output dialect expansion。
  - MySQL / MariaDB を mainline として維持する。
  - SQLite は local / file-backed runtime output の first supported expansion とする。
  - PostgreSQL / SQL Server は config store ではなく、ユーザー DB 接続・introspection・DBAccess 出力側の parked / post-must-features 候補として扱う。
  - SQLite だけを先に完了扱いにせず、dialect helper、schema introspection、DBAccess class output、dialect-aware contract comparison を共通化しながら完成度を上げる。
  - Detailed roadmap: [User DB Multi-DB Dialect Roadmap](2026-0617-user-db-multidb-dialect-roadmap.md).
- MySQL / MariaDB config store と SQLite config store の相互 portability。
- quickstart / manual 全体の日英併記 final pass は必要に応じて継続するが、SQLite lightweight profile の主要導線は記載済み。

## Current Understanding

### Current Store

現在の設計データの正本は `config_db` である。

保存対象:

- `projects`
- `project_memberships`
- `dbtable` / `dbtablecolumns`
- `dataclass` / `dataclassfields`
- `project_db_access_*`
- `project_source_outputs`
- `database_sources`
- HTML / custom proxy / compare output / host assignment related metadata

### Initial Implementation Bias And Remaining Bias

計画開始時点では実質 MySQL / MariaDB 固定だった。現在は config store first slice と主要 repository smoke に SQLite support が入ったが、sample runner、MTOOL seed、generated output、lightweight compose lane には MySQL / MariaDB 前提がまだ残る。

当初の主な理由:

- `app_load_config()` が `mysql:host=...` DSN を生成していた。
- `docker/mariadb/config-initdb/*.sql` が MariaDB DDL である。
- repository に MySQL dialect が散っていた。
  - `DATE_FORMAT(...)`
  - `ON DUPLICATE KEY UPDATE`
  - `GROUP_CONCAT ... SEPARATOR`
  - `information_schema`
  - `DATABASE()`
  - `NOW()`
- config DB preflight / migrate は MariaDB schema introspection を前提としていた。

現在の残存 bias:

- `make up-mtool` は `db-config` service を起動する。
- MTOOL core seed / sample seed は MariaDB SQL として流れる導線が中心である。
- generated runtime / DB access output は SQLite dialect first slice を持ち、sample16 authenticated proxy、sample13 Swagger viewer、sample13 referenced proxy route の HTTP-level smoke は MySQL / MariaDB と SQLite config store の両方で通っている。
- blob / file parameter や vendor-specific SQL は、生成器で共通化できる範囲を AI-assisted review で判定し、複雑系は継承先 class / custom runtime に委ねる。
- sample pack gate は MySQL / MariaDB config store profile を主対象にしている。

### Generated Runtime Context

Mtool は runtime / DB access class の self-generation を進めているが、current mainline の admin repository はまだ PDO repository が主役である。

したがって、SQLite support は次の 2 層で考える。

1. Mtool が SQLite 対応 runtime / SQL / DB access output を生成できるようにする。
2. Mtool 自身の config store implementation が `mysql` / `sqlite` を選べるようにする。

### Self-Hosting Layer Policy

Mtool は自分が生成した DB access / runtime output を、自分自身の runtime bundle や proxy bundle でも使い始めている。そのため、SQLite 対応の順序は分かりにくいが、方針は次のように整理する。

1. まず output 側の SQLite 対応を進める。
   - generated DBAccess が SQLite DSN を扱える。
   - generated SQL が SQLite でも実行しやすい static SQL + bound params に寄る。
   - proxy / OpenAPI / custom runtime bundle に同じ runtime support を同梱できる。
2. その output を、Mtool が生成して使う runtime dbclasses / proxy bundle へ戻す。
   - sample16 authenticated proxy では、generated proxy bundle の autoload ordering を修正し、MySQL / MariaDB と SQLite の両方で同じ generated runtime adapter を HTTP route から使う状態を確認済み。
3. Mtool admin mainline の config store は、現時点では MySQL / MariaDB profile も SQLite profile も PDO repository を使う。

3 は SQLite だけの一時的な逃げ道ではない。現行の admin mainline が MySQL / MariaDB でも PDO repository を使っているため、SQLite config store 対応も同じ repository layer に dialect support を足す形にする。

将来、Mtool 自身の config store access を self-generated DBAccess へ移す場合は、MySQL / MariaDB と SQLite のどちらか一方だけを generated output に切り替えない。両 profile が同じ access layer を使う状態を保つ。

移行原則:

- current mainline: MySQL / MariaDB config store と SQLite config store はどちらも PDO repository layer を使う。
- runtime bundle / proxy bundle: generated DBAccess output を使う。
- future self-hosted mainline: config store access を generated DBAccess へ寄せるなら、MySQL / MariaDB と SQLite を同じタイミング、同じ contract、同じ test gate で移行する。
- SQLite だけ generated output、MySQL / MariaDB だけ PDO repository、またはその逆の恒久状態を作らない。

### Persistence Boundary Decision

SQLite support は、DegoDB / Mtool 自身の保存先対応と、ユーザー DB 側の接続・生成対応を分けて扱う。

MySQL / MariaDB と SQLite の layer parity は [DB Layer Parity Inventory](2026-0617-db-layer-parity-inventory.md) で棚卸しする。

DegoDB / Mtool 自身の design metadata store は、当面は次の 2 種類に絞る。

1. DB 保存
   - default / team / enterprise 用。
   - local MariaDB container または external MySQL / MariaDB を使う。
2. SQLite file 保存
   - lightweight / personal / local-first 用。
   - user-facing には `APP_CONFIG_STORE_DIR` で保存フォルダだけを指定し、内部的に `config.sqlite` を使う。

この層では、対応 store をむやみに増やさない。ツール自身の metadata store は、導入が軽いこと、backup / restore が明示できること、schema preflight が安定することを優先する。

一方で、ユーザー DB 側は DegoDB の価値に直結するため、対応 DB を段階的に増やす対象とする。ここでいうユーザー DB 側とは、DB 接続 profile、schema introspection、runtime SQL generation、DB access class generation、proxy / OpenAPI / custom runtime bundle から参照される業務データベースである。

優先順位:

1. MySQL / MariaDB
   - 既存 mainline。current behavior と enterprise lane の基準にする。
2. SQLite
   - local app、single-file app、prototype、generated DBAccess smoke の基準にする。
3. PostgreSQL
   - SaaS / modern backend で重要な候補だが、must-have feature 完了後まで parked にする。
4. SQL Server
   - enterprise / Windows 業務システム向け候補だが、must-have feature 完了後まで parked にする。

ユーザー DB 側の対応は、`接続できる` だけでは完了としない。最低でも次を別々の capability として扱う。

- connection profile
- schema introspection
- SQL dialect helper
- DDL / runtime SQL generation
- DB access class generation
- CRUD / SELECT / join / aggregate / pagination
- blob / file / JSON column support where a portable common pattern exists
- generated runtime bundle smoke

この判断により、config store の SQLite 対応は `保存先 profile の完成` として閉じ、ユーザー DB 側の SQLite / PostgreSQL / SQL Server 対応は `output dialect expansion` として別ロードマップで育てる。

### Generator Responsibility Boundary

Mtool の出力で対応できない形式は、元々の設計思想として継承先 class で個別実装できる。したがって、multi-dialect output expansion では、生成器がすべての SQL / DB-specific behavior を完全自動生成することを目標にしない。

生成器が責任を持つ範囲:

- table / column metadata から自然に導ける CRUD。
- simple SELECT、where、order、pagination、join、aggregate の共通パターン。
- MySQL / MariaDB、SQLite、将来の PostgreSQL / SQL Server で対応関係を説明できる型・構文。
- prepared statement 化できる scalar parameters。
- runtime adapter / connection profile の標準 surface。

継承先 class または custom implementation に委ねる範囲:

- blob / file upload など、driver ごとに binding strategy が大きく変わるもの。
- vendor-specific SQL function、stored procedure、trigger-dependent behavior。
- 複雑な transaction、lock、upsert conflict policy。
- 高度な full-text search、JSON path、geospatial、window function の個別最適化。
- generated code の読みやすさや安全性を壊してまで汎用化する必要がある処理。

この境界により、SQLite 対応の完了条件は `全特殊ケースを生成器が内包すること` ではなく、`共通 CRUD / SELECT 系が MySQL / MariaDB と SQLite で揃い、複雑系を継承先で安全に差し替えられること` とする。

### AI-Assisted Generation Decision

Mtool で生成可能かどうかの初期判断は、AI-assisted review の対象にする。

AI は、ユーザー DB schema、既存 SQL、期待する API / screen behavior、対象 dialect を見て、次を分類する。

- `generated`: Mtool の標準 generator で生成すべき。
- `generated_with_options`: generator で生成できるが、dialect / pagination / null handling / naming などの option 指定が必要。
- `inherited_custom`: generated base class を出した上で、継承先 class に個別実装すべき。
- `manual_runtime`: generator の責務外として、custom proxy / custom runtime / handwritten repository に委ねるべき。
- `needs_design_review`: schema、relation、key、transaction boundary が曖昧で、生成判断の前に設計確認が必要。

AI が見る観点:

- CRUD / SELECT / join / aggregate として共通化できるか。
- MySQL / MariaDB と SQLite、将来の PostgreSQL / SQL Server で dialect mapping が説明できるか。
- prepared statement の scalar parameter で安全に表現できるか。
- blob / file / JSON / full-text / geospatial / vendor-specific function など、個別実装へ逃がすべき要素があるか。
- generated base class と inherited class の境界を保てるか。
- テスト可能な runtime contract に落とせるか。

AI の出力は、automatic apply ではなく review artifact として扱う。最低限、判定、理由、推奨実装先、必要な generator option、継承先で実装すべき method、必要な test / smoke を返す。

## Product Positioning

SQLite は MySQL / MariaDB の置き換えではなく、別の supported profile として扱う。

| Profile | Target | Store | Expected Operation |
| --- | --- | --- | --- |
| Trial local | 試用 | local MariaDB volume or disposable SQLite | 消えてもよい前提。必要なら bundle export |
| Personal durable | 個人の継続利用 | folder-backed SQLite or local MariaDB + dump | backup rotation と project metadata bundle |
| Team / Enterprise | 複数人・長期運用 | external MySQL / MariaDB | DB backup、migration、monitoring、secret management |

UI / docs 上は、SQLite を廉価版ではなく `Local file store` として見せるのがよい。

候補:

- `Local file store (SQLite)`
- `Local database container (MariaDB)`
- `External server database (MySQL / MariaDB)`

## Target Configuration Shape

最終的には、通常ユーザーは保存フォルダだけを指定する。

```env
APP_CONFIG_STORE_DIR=./work/config-store
```

この場合、`APP_CONFIG_STORE_DIR/config.sqlite` を lightweight SQLite file store として使う。

server DB profile を使う場合は `APP_CONFIG_STORE_DIR` を空にし、従来どおり `APP_CONFIG_DB_*` または local MariaDB compose を使う。

advanced / internal override として config store driver も明示できる。

```env
APP_CONFIG_STORE_DRIVER=mysql
```

SQLite profile:

```env
APP_CONFIG_STORE_DRIVER=sqlite
APP_CONFIG_SQLITE_DIR=./work/config-store
APP_CONFIG_SQLITE_FILE=config.sqlite
```

MySQL / MariaDB profile:

```env
APP_CONFIG_STORE_DRIVER=mysql
APP_CONFIG_DB_HOST=db-config
APP_CONFIG_DB_PORT=3306
APP_CONFIG_DB_NAME=config_app
APP_CONFIG_DB_USER=config_app
APP_CONFIG_DB_PASSWORD=...
```

Resolved UX direction:

- 既存の `APP_CONFIG_DB_*` を維持したまま folder-only `APP_CONFIG_STORE_DIR` を足す。
- `APP_CONFIG_STORE_DRIVER` は advanced override とし、通常 docs では folder-only を入口にする。

現時点では、user-facing には `APP_CONFIG_STORE_DIR` が一番分かりやすい。DB server と file store の詳細な区別は内部設定で扱う。

## Roadmap

### Phase 0. Inventory And Boundary

目的:

- SQLite support の対象範囲を確定する。
- MySQL / MariaDB current behavior を壊さないための境界を作る。

作業:

- admin / lab / script の repository 使用箇所を分類する。
- generated runtime に置換済みの箇所と PDO repository の箇所を分ける。
- MySQL dialect 使用箇所を棚卸しする。
- `config_db` と `lab_db` を分けて扱う。

判断:

- first target は `config_db` 相当の design metadata store。
- `lab_db` の SQLite 化は後段に回す。

成果物:

- dialect inventory report。
- first-slice target table list。

### Phase 1. Backup Hardening

目的:

- SQLite 対応に入る前に、現行 MySQL / MariaDB profile の安全性を上げる。
- migration / experiment 中に戻れる状態を作る。

作業:

- `backup-config-db` / `backup-config-db-mtool` に保存先指定を追加する。
- backup rotation target を追加する。
  - keep days
  - keep count
- restore 前 auto backup を追加する。
- backup manifest を保存する。
  - file path
  - created at
  - source profile
  - schema preflight summary
  - git commit if available

候補 env:

```env
CONFIG_DB_BACKUP_DIR=work/backups/config-db
CONFIG_DB_BACKUP_KEEP_DAYS=7
CONFIG_DB_BACKUP_KEEP_COUNT=7
```

成果物:

- `make backup-config-db-rotate`
- `make backup-config-db-mtool-rotate`
- restore safety rule

### Phase 2. Dialect Abstraction

目的:

- repository / bootstrap / generator が MySQL 固有表現を直接持ちすぎないようにする。

作業:

- config store connection config に driver を持たせる。
- dialect helper を追加する。
  - current timestamp expression
  - datetime formatting
  - upsert
  - table exists
  - column exists
  - last insert id
  - identifier quote
- MySQL dialect を既存 behavior に合わせて実装する。
- SQLite dialect は first slice の必要最小限から始める。

注意:

- 既存 SQL を一気に全置換しない。
- first slice は selected repository だけを dialect helper 経由にする。

成果物:

- `mysql` dialect helper
- `sqlite` dialect helper skeleton
- focused repository conversion

### Phase 3. User DB Output Dialect Expansion

目的:

- Mtool の output として、ユーザー DB 向け runtime / SQL / DB access class の dialect 対応を広げる。
- SQLite は first expansion として扱い、後続で PostgreSQL / SQL Server などを追加できる形は残す。ただし他 DB server 実装は must-have feature 完了後まで parked にする。
- 詳細計画は [User DB Multi-DB Dialect Roadmap](2026-0617-user-db-multidb-dialect-roadmap.md) に集約する。

作業:

- source output metadata に target database driver を表現できるようにする。
- runtime SQL generator に SQLite DDL を追加する。
- generated DB access runtime で SQLite PDO DSN を扱えるようにする。
- sample pack で SQLite output を検証する。
- PostgreSQL / SQL Server は、この phase では実装しない。must-have feature 完了後に別 slice として再評価する。

SQLite DDL の方針:

- `INTEGER PRIMARY KEY AUTOINCREMENT`
- `TEXT` / `INTEGER` / `REAL` / `BLOB` を基本型にする。
- `CURRENT_TIMESTAMP` を使う。
- `ON UPDATE CURRENT_TIMESTAMP` は trigger または app-side update に逃がす。
- foreign key は `PRAGMA foreign_keys = ON` 前提にする。

成果物:

- SQLite runtime output sample
- SQLite DDL generation
- output verification test
- ユーザー DB dialect expansion の capability checklist
- dialect-aware contract comparison first slice

### Phase 4. Config Store SQLite Profile

目的:

- DegoDB / Mtool 自身の design metadata store を SQLite file にできる optional profile を追加する。

作業:

- `APP_CONFIG_STORE_DRIVER=sqlite` を追加する。done.
- user-facing には `APP_CONFIG_STORE_DIR=...` だけで SQLite profile を選べるようにする。done.
- `APP_CONFIG_SQLITE_DIR` / `APP_CONFIG_SQLITE_FILE` を解決する。advanced/internal として done.
- SQLite file parent directory を作成する。done.
- SQLite schema migration path を追加する。first slice done.
- SQLite preflight を追加する。first slice done.
- selected repository を SQLite 対応する。

初期対象候補:

- `projects`
- `project_memberships`
- `project_source_outputs`
- `database_sources`

Current status:

- `projects` repository first CRUD smoke is done on SQLite config store.
- `project_memberships` is covered only through owner membership insert / count in the `projects` smoke.
- `project_source_outputs` repository create / fetch / update / catalog / delete smoke is done on SQLite config store.
- `database_sources` repository create / fetch / update / catalog / delete smoke is done on SQLite config store.
- `project_memberships` replace / summary smoke is done on SQLite config store.
- page security policy / capability smoke is done on SQLite config store.
- host assignment smoke is done on SQLite config store.
- `dbtable` / `dbtablecolumns` table metadata smoke is done on SQLite config store.
- `dataclass` / `dataclassfields` data class metadata smoke is done on SQLite config store.
- DB access class / function metadata first-slice smoke is done on SQLite config store.
- DB access function source output target replace / fetch / target catalog smoke is done on SQLite config store.
- DB access SELECT where / target field / having detail smoke is done on SQLite config store.
- DB access UPDATE / DELETE where and INSERT / UPDATE target field detail smoke is done on SQLite config store.
- Compare output / additional path smoke is done on SQLite config store.
- Custom proxy / target keys / steps smoke is done on SQLite config store.
- HTML source binding smoke is done on SQLite config store.
- Project HTML definition / parameter smoke is done on SQLite config store.
- HTML template / parameter smoke is done on SQLite config store.

後続対象:

- Remaining `project_db_access_*` detail tables and UI paths not yet covered by the first CRUD smoke.
- Remaining custom proxy UI paths not yet covered by the first CRUD smoke.

成果物:

- local SQLite config store で admin が起動する。
- first target の CRUD が通る。
- MySQL profile は default のまま維持される。

### Phase 5. Lightweight Runtime Lane

目的:

- ユーザーが config DB server を意識せず、保存フォルダ指定だけで Mtool を起動できる local-first lane を作る。
- `make up-mtool` の既存 server DB trial lane は維持し、lightweight lane を別名で追加する。

作業:

- `db-config` service を起動しない compose overlay / make target を追加する。
- `APP_CONFIG_STORE_DIR` が指定された時の admin / lab 起動を smoke する。
- 空の SQLite file が初回 bootstrap されることを lane test に含める。
- `lab_db` は first slice では MariaDB container のまま残し、config store と user / lab DB を混同しない。
- access URL 表示と quickstart 文言を lightweight lane に合わせる。

候補 target:

```bash
make up-mtool-lite APP_CONFIG_STORE_DIR=work/config-store
make health-mtool-lite
make config-db-preflight-mtool-lite
make down-mtool-lite
```

成果物:

- folder-only SQLite config store で admin / lab が起動する。done.
- config DB server を要求しない軽量 quickstart が成立する。first slice done.
- server DB trial lane / durable lane は従来どおり残る。

Current status:

- `mtool/docker/compose/01_mtool-lite.compose.yaml` を追加した。
- `make up-mtool-lite` は `compose.yaml + 01_mtool-lite.compose.yaml` を使い、`db-config` service を起動しない。
- admin health は SQLite config store を probe し、lab health は従来どおり `db-lab` を probe する。
- `APP_CONFIG_STORE_DIR=work/config-store-lite-smoke` の fresh folder smoke で `config.sqlite` 自動 bootstrap と preflight green を確認した。

### Phase 6. Dual-Profile Sample Support

目的:

- tutorial sample を MySQL / MariaDB config store と SQLite config store の両方で検証できるようにする。
- lightweight user と enterprise user が同じ sample story を別 persistence profile で追えるようにする。

方針:

- sample の domain / user DB は原則そのまま維持し、まずは DegoDB 自身の config store profile を切り替える。
- SQLite config store 版 sample は `APP_CONFIG_STORE_DIR` を一時 folder に向ける。
- sample 実行後は generated output / reference compare が MySQL / MariaDB profile と同じ結果になることを確認する。
- sample seed が config DB に直接 SQL を流す場合は、SQLite profile でも流せる形式へ変換するか、application-level import / bundle import に寄せる。

作業:

- sample runner に config store profile option を追加する。
  - `mysql` / `mariadb` default
  - `sqlite` first slice done for `sample01` / `sample10` / `sample11`
- first target sample を選ぶ。
  - `sample01-simple-table-runtime`
  - `sample10-dbaccess-mini-crud-flow`
  - `sample11-html-template-output`
- SQLite profile 用の sample seed application path を作る。
- `make sample01-pack-runtime-test` は既存 MySQL / MariaDB gate として維持する。done.
- 追加 gate として SQLite profile の sample test を足す。`sample01` / `sample10` / `sample11` done.

候補 target:

```bash
make sample01-pack-runtime-test-sqlite
make sample10-pack-runtime-test-sqlite
make sample11-pack-runtime-test-sqlite
make sample-pack-runtime-test-sqlite
```

受け入れ条件:

- MySQL / MariaDB sample gate が green のまま。
- SQLite config store sample gate でも import / sync / output / reference compare が green。
- sample docs が profile の違いを説明し、ユーザーの業務 DB と DegoDB config store を混同しない。

成果物:

- dual-profile sample runner first slice
- SQLite config store sample gate for `sample01`
- tutorial docs の profile selection notes

Current blocker:

- current broader sample runner still assumes MariaDB-oriented seed SQL for many packs.
- `sample01` has a SQLite seed path; later samples need the same path hardened or replaced with bundle / app-level import.
- Candidate approaches:
  - convert existing sample seed SQL into SQLite statements before applying
  - prefer application-level import / project metadata bundle import so seed path is not dialect-specific
  - keep MariaDB sample seed as default and add SQLite seed path only for dual-profile gates

Current status:

- `mtool/scripts/apply_config_sample_seed_sqlite.php` applies sample SQL to SQLite config store for the first slice.
- `sample01-simple-table-runtime/compose.sqlite-config.yaml` and `run-sqlite-config.sh` run the pack without `db-config`.
- `project_table_import_source.php` can introspect SQLite live schema via `sqlite_schema` / `PRAGMA table_info`.
- `make sample01-pack-runtime-test-sqlite` is green.

### Phase 7. Migration And Portability

目的:

- lightweight profile と enterprise profile の間を移動できるようにする。

作業:

- MySQL / MariaDB -> SQLite export/import。
- SQLite -> MySQL / MariaDB export/import。
- project metadata bundle との重複と役割を整理する。
- secrets は引き続き DB file / bundle に混ぜない。

成果物:

- `export config store`
- `import config store`
- profile migration runbook

### Phase 8. UX And Documentation

目的:

- 利用者が自分の運用規模に合う profile を選べるようにする。

作業:

- setup docs に persistence profile を追加する。
- `start-here` / troubleshooting を更新する。
- backup / restore / rotation の user-facing command を整理する。
- SQLite profile の制約を明記する。
- quickstart / manual 類は、SQLite profile 完成前でも `APP_CONFIG_STORE_DIR` を folder-only entry として先に示す。
- 恒久 docs は最終 pass で日英併記に揃える。`docs/reports/` 配下の dated progress / slice report は履歴用途なので日本語中心でよい。

SQLite profile constraints:

- first target は local / single-user。
- concurrent writers は想定しすぎない。
- shared network folder は推奨しない。
- enterprise / team では MySQL / MariaDB profile を推奨する。

## Backup Strategy

Backup hardening は SQLite support と並行して進める。

### MySQL / MariaDB

backup:

- `mariadb-dump --single-transaction`
- configured backup dir
- temp file -> atomic rename
- manifest JSON

rotation:

- keep N latest
- keep days
- failed backup does not delete old backups

restore:

- require explicit confirmation
- restore 前に automatic backup を取る
- restore 後に preflight を実行する

### SQLite

backup:

- running app がある場合、単純 copy ではなく `VACUUM INTO` または SQLite backup API 相当を使う。
- WAL mode の場合は `.sqlite` / `-wal` / `-shm` の扱いを明確にする。

rotation:

- MySQL / MariaDB と同じ keep policy を使う。

restore:

- app stop または maintenance mode を前提にする。
- restore 前 backup を取る。

## Non-goals

- MySQL / MariaDB support を廃止しない。
- enterprise profile を SQLite に寄せない。
- `lab_db` を first slice で SQLite 化しない。
- generated output directory を design metadata の正本にしない。
- secrets を SQLite file や project metadata bundle に混ぜない。

## Open Questions

- config store driver 名は `APP_CONFIG_STORE_DRIVER` でよいか。
  - 現状は yes。user-facing では `APP_CONFIG_STORE_DIR` を優先し、driver は advanced override とする。
- SQLite profile でも `database_sources` に external MySQL source を登録できるようにするか。
  - 方針としては yes。config store と import/runtime source は別物として扱う。
- `lab_db` の lightweight profile は必要か。
  - first slice では no。後段で disposable SQLite lab として検討する。
- generated repository driver をどの table から導入するか。
  - `projects` から始めるのが最小でよい。
- SQLite migration を MariaDB SQL から変換生成するか、手書き canonical schema とするか。
  - first slice は runtime conversion で成立。後続で curated SQLite schema file へ昇格するかを判断する。
- sample seed を SQLite config store にどう流すか。
  - 直接 SQL seed を SQLite に変換するより、可能なら application-level import / project metadata bundle import に寄せる方が長期的に安全。
- lightweight lane では `db-config` service を完全に外すか、起動しても未使用として扱うか。
  - first slice は完全に外した。既存 `make up-mtool` は従来 trial lane として維持する。

## Recommended Next Slice

Priority update:

Artifact parity framework is implemented as described in [DB Layer Parity Inventory](2026-0617-db-layer-parity-inventory.md). The framework batch-runs the MySQL / MariaDB config store lane, captures generated outputs under `work/artifact-parity/<run-id>/mysql/`, batch-runs the SQLite config store lane, captures generated outputs under `work/artifact-parity/<run-id>/sqlite/`, then compares manifests / digests / normalized JSON after both lanes complete. Current targets are `sample01` through `sample17`; full tutorial verification passed with `make artifact-parity-test ARTIFACT_PARITY_RUN_ID=codex-all-samples` across 597 captured files.

backup hardening、dialect inventory、dialect helper first slice、config store folder profile first slice、SQLite config bootstrap first slice、主要 repository first-slice smoke、lightweight runtime lane first slice は着手済み。

`sample01` through `sample17` の SQLite config store gate、SQLite file store backup / restore / rotation、generated canonical DBAccess PHP の SQLite runtime adapter first slice、generated write / read SQL の prepared statement 化 first slice、bootstrap-generated DBAccess prepared statement 化、sample16 proxy bundle support smoke、sample16 authenticated proxy HTTP route smoke、sample13 Swagger viewer / referenced proxy HTTP route smoke、sample13 Swagger viewer browser-side Try It Out smoke は完了した。AI-assisted generation review の実運用 first pass、OpenAPI examples の typed scalar 改善 first slice、DBAccess metadata-backed scalar typing first slice も sample13 / sample17 に適用済み。次の実装 slice は MySQL / MariaDB config store と SQLite config store の portability、または richer DBAccess parameter metadata の棚卸しに進める。

理由:

- `APP_CONFIG_STORE_DIR` の user-facing entry と空 SQLite file の自動 bootstrap は入った。
- `up-mtool-lite` で config DB server を起動しない lane は入った。
- sample01-17 が両 profile で通り、manual / quickstart を SQLite 寄りにしても学習導線の基本信頼性を維持できる。
- `sample01` で runner / seed / SQLite live schema import の first slice が通った。
- `sample02`-`sample09` で nullable/default dataclass、lookup/helper dataclass、parent-child dataclass、DBAccess select/filter/sort/pagination/CRUD/join/aggregate/having の feature coverage を parity 対象に入れた。
- `sample10` は DB Access detail metadata が多く、SQLite seed path の妥当な負荷テストになった。
- `sample11` は HTML template / project HTML metadata を含むため、UI/output metadata の幅を広げる gate になった。

次の順で進める。

1. SQLite sample seed applier の対応 SQL を増やすか、bundle / app-level import へ移すか判断する。
2. 今後追加される tutorial sample は MySQL / MariaDB と SQLite config store の dual-profile gate と artifact parity capture metadata を同時に追加する。
3. MySQL / MariaDB config store と SQLite config store の portability を設計する。
4. auth-required OpenAPI operation など、browser Try It Out smoke の横展開候補を整理する。Design: [HTTP Runtime Smoke Plan](2026-0617-http-runtime-smoke-plan.md).
5. AI-assisted generation review artifact を今後の user DB samples に適用し、Mtool 生成 / 継承先個別実装 / manual runtime の切り分けを記録する。First pass: [AI Generation Review: sample13 / sample16](2026-0617-ai-generation-review-sample13-sample16.md). Contract: [AI Generation Review](../../internal/ai-generation-review.md).
6. quickstart / manual を実装状況に合わせて日英併記で更新する。
