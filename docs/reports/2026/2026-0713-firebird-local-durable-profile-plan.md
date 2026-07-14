# Firebird Local Durable DB Profile Plan / Firebird local durable DB profile 計画

Date: 2026-07-13
Status: `F1_FEASIBILITY_INVENTORY_DONE`

## Purpose / 目的

Firebird support should be evaluated as a local durable RDB profile for Mtool, not as a novelty database lane and not as broad "support every database" work.

Firebird 対応は、変わり種 DB 対応や「全 DB 対応」ではなく、Mtool の local durable RDB profile として評価する。

The product gap is between SQLite and MySQL/MariaDB:

- SQLite is the lightweight local file profile. / SQLite は軽量 local file profile。
- MySQL/MariaDB is the server-operation default. / MySQL・MariaDB は server 運用 default。
- Firebird may fit the middle: local PC, stronger RDB behavior than SQLite, lower operational burden than running a full database server. / Firebird はその中間として、local PC で使え、SQLite より本格 RDB 寄りで、full DB server 運用より軽い可能性がある。

## Current external anchors / 外部確認の根拠

As of 2026-07-13, the Firebird project publishes current Firebird 5 documentation, language reference, command-line tools, backup/restore documentation, platform downloads for Windows/Linux/macOS, Docker images, and PHP driver links.

2026-07-13 時点で、Firebird project は Firebird 5 の documentation、language reference、command-line tools、backup/restore documentation、Windows/Linux/macOS 向け download、Docker image、PHP driver 導線を公開している。

Reference URLs:

- Firebird 5.0 downloads: https://firebirdsql.org/en/firebird-5-0/
- Firebird reference manuals: https://firebirdsql.org/en/reference-manuals/

## Target position / 位置づけ

| Profile | Role / 役割 | Mtool reading / Mtool上の読み方 |
| --- | --- | --- |
| SQLite | Lightweight local profile / 軽量local profile | Easy start, file-backed, low setup; not the only local story. / 手軽な開始点。file-backed・低setup。ただしlocalの唯一解ではない。 |
| Firebird | Local durable RDB profile / local durable RDB profile | Candidate upgrade path from SQLite when the user wants stronger local data management without running MySQL/MariaDB. / MySQL・MariaDBを立てずによりしっかりしたlocal data managementが欲しい場合のSQLiteからのupgrade候補。 |
| MySQL/MariaDB | Server-operation default / server運用default | Existing default for full server operation and final promotion target. / full server運用と最終promotion targetの既存default。 |
| PostgreSQL | Opt-in user DB/generated-output lane / opt-in user DB・generated-output lane | Already complete for required scope; not a config-store target. / 必要範囲では完了済み。config-store targetではない。 |

## Promotion direction / promotion 方向

Supported direction to evaluate:

1. SQLite -> Firebird
   - Upgrade from lightweight local profile to durable local RDB profile. / 軽量local profileからdurable local RDB profileへのupgrade。
2. Firebird -> MySQL/MariaDB
   - One-way promotion from local durable profile to server operation. / local durable profileからserver運用への一方向promotion。

Non-goals for the first lane:

- MySQL/MariaDB -> Firebird reverse migration. / MySQL・MariaDBからFirebirdへの逆方向migration。
- Firebird -> SQLite downgrade. / FirebirdからSQLiteへのdowngrade。
- Firebird as every user DB/output dialect immediately. / Firebirdを全user DB・output dialectとして即時全面対応すること。
- Full production guarantee before a local proof. / local proof前のproduction保証。
- Replacing MySQL/MariaDB default server profile. / MySQL・MariaDB default server profileの置換。

## Active plan rows / active計画行

| Order | Work unit / 作業の塊 | Exit condition / 完了条件 |
| --- | --- | --- |
| #885 | Firebird local durable DB profile feasibility / Firebird local durable DB profile feasibility | Product position, embedded/local-file reality, PHP driver path, setup burden, backup/restore, and non-goals are recorded. |
| #886 | Firebird local connection proof / Firebird local connection proof | Smallest local create/connect/transaction smoke works, or an explicit blocker is recorded. |
| #887 | Firebird config-store fit and schema preflight / Firebird config-store fit・schema事前設計 | Mtool config schema fit and first config-store slice boundary are clear. |
| #888 | SQLite-to-Firebird promotion contract / SQLiteからFirebird promotion contract | Side-effect-free promotion plan shape and validation gates are defined. |
| #889 | Firebird-to-MySQL/MariaDB promotion boundary / FirebirdからMySQL・MariaDB promotion境界 | Reuse/delta against the existing offline promotion chain is clear. |
| #890 | Firebird lane checkpoint / Firebird lane checkpoint | Go / park / stop decision is recorded with evidence. |

## First feasibility questions / 最初の確認事項

- Can current Firebird still satisfy the old desired shape: program accesses a local database file with no meaningful server operation burden? / 現行Firebirdで、昔の期待通り「プログラムがlocal DB fileを指定して、実質server運用負担なしでaccessする」形が成立するか。
- Which PHP path is realistic for Mtool: PDO Firebird, ext/interbase, ODBC, or another driver? / Mtoolで現実的なPHP経路は PDO Firebird、ext/interbase、ODBC、その他のどれか。
- What is the lowest-friction developer and user setup on macOS, Windows, and Linux? / macOS・Windows・Linuxで最も摩擦の少ないdeveloper/user setupは何か。
- Does the Mtool config schema use anything that conflicts with Firebird identifiers, sequences/generators, BLOB/text, timestamp, transactions, or metadata queries? / Mtool config schemaがFirebirdのidentifier、sequence/generator、BLOB/text、timestamp、transaction、metadata queryと衝突しないか。
- Can the existing SQLite-to-MySQL promotion artifacts be generalized to SQLite-to-Firebird and Firebird-to-MySQL with small dialect adapters? / 既存SQLite-to-MySQL promotion artifactを、小さなdialect adapterでSQLite-to-FirebirdとFirebird-to-MySQLへ一般化できるか。

## Initial verification gates / 初期検証 gate

- Docs-only plan changes: `git diff --check`. / docs-only計画変更は `git diff --check`。
- First code proof should be opt-in and local. It must not make normal `make test` require Firebird. / first code proofはopt-in localにし、通常の`make test`がFirebird必須にならないようにする。
- Prefer a focused local smoke command before adding broad sample/runtime claims. / 広範なsample・runtime claimの前にfocused local smoke commandを優先する。

## Current decision / 現在の判断

Promote Firebird to the next active planning lane. Start with feasibility and a local connection proof before touching config-store runtime or promotion code.

Firebird を次の active planning lane に昇格する。config-store runtime や promotion code を触る前に、feasibility と local connection proof から開始する。

## F1 feasibility inventory result / F1 feasibility棚卸し結果

Date: 2026-07-13

Status: `DONE_FOR_F2`

### External reality check / 外部実態確認

Firebird still matches the product hypothesis enough to continue to a local proof:

- Firebird 5.0.4 is current on the official download page as of 2026-07-13, with packages listed for Windows, Linux, macOS, Android, and source distribution.
- The official Firebird documentation index includes Firebird 5 quick start, Firebird 5 language reference, configuration reference, command-line tools, `gbak`, `nbackup`, and security/file metadata manuals.
- The official Firebird download/navigation surface includes Docker images and PHP driver links.
- PHP has a `PDO_FIREBIRD` driver documented by php.net. This should be the first Mtool proof path because Mtool already prefers PDO-style access.
- The older PHP Firebird/InterBase extension should not be the primary path: php.net marks it as unmaintained/dead and moved to PECL.

Firebird は local durable profile の仮説を検証する価値がある状態です。

- 2026-07-13 時点で、公式 download page には Firebird 5.0.4 が current として掲載され、Windows、Linux、macOS、Android、source 配布がある。
- 公式 documentation index には Firebird 5 quick start、Firebird 5 language reference、configuration reference、command-line tools、`gbak`、`nbackup`、security/file metadata manuals がある。
- 公式 download/navigation surface には Docker images と PHP driver 導線がある。
- PHP には php.net documented な `PDO_FIREBIRD` driver がある。Mtool は PDO 系 access を既に主経路としているため、first proof はここから始める。
- 古い PHP Firebird/InterBase extension は primary path にしない。php.net が unmaintained/dead と明記し、PECL 移動扱いにしている。

External anchors / 外部根拠:

- https://firebirdsql.org/en/firebird-5-0/
- https://firebirdsql.org/en/reference-manuals/
- https://firebirdsql.org/en/php-driver/
- https://www.php.net/manual/en/ref.pdo-firebird.php
- https://www.php.net/manual/en/book.ibase.php

### Current local/runtime check / 現行local runtime確認

Current local PHP modules show `PDO`, `odbc`, and `PDO_ODBC`, but not `pdo_firebird`, `firebird`, `interbase`, or `ibase`.

現在の local PHP module には `PDO`、`odbc`、`PDO_ODBC` はあるが、`pdo_firebird`、`firebird`、`interbase`、`ibase` はない。

Implication:

- Do not wire Firebird into normal `make test`.
- Do not assume the developer's host PHP can run the first proof.
- Use an opt-in Docker/runtime lane, or a clearly documented local PHP-with-PDO_FIREBIRD setup, for F2.

含意:

- 通常の `make test` に Firebird を混ぜない。
- developer の host PHP で first proof が動く前提にしない。
- F2 は opt-in Docker/runtime lane、または `PDO_FIREBIRD` 入り local PHP setup を明示する形で始める。

### Mtool fit inventory / Mtool適合棚卸し

Current Mtool config-store support is intentionally narrow:

- `APP_CONFIG_STORE_DRIVER` normalizes to `mysql` or `sqlite`.
- `app_config_store_config()` returns either `mysql:` DSN or `sqlite:` DSN.
- SQL dialect helpers currently have explicit branches for `sqlite` and `pgsql`; other DSNs generally fall back to `mysql`.
- Existing SQLite-to-MySQL promotion code is narrowly named and typed around source `sqlite` and target `mysql`.

現行 Mtool config-store support は意図的に狭い。

- `APP_CONFIG_STORE_DRIVER` は `mysql` または `sqlite` に正規化される。
- `app_config_store_config()` は `mysql:` DSN または `sqlite:` DSN を返す。
- SQL dialect helper は `sqlite` と `pgsql` を明示分岐し、それ以外は概ね `mysql` に倒れる。
- 既存 SQLite-to-MySQL promotion code は source `sqlite`、target `mysql` 前提の命名・型になっている。

Therefore, Firebird should not be added directly to config-store runtime as the first implementation. The first proof should be a separate, opt-in local connection smoke that proves the smallest valuable unit:

1. create or open a Firebird database;
2. create one table with a stable identifier policy;
3. insert / select using prepared statements;
4. begin / rollback / commit behavior;
5. report driver/runtime/version information;
6. leave no claim that full Mtool config-store support exists.

したがって、最初の実装で Firebird を config-store runtime へ直接混ぜない。first proof は独立した opt-in local connection smoke とし、最小価値単位を確認する。

1. Firebird database の create/open;
2. 安定した identifier policy で 1 table 作成;
3. prepared statement による insert / select;
4. begin / rollback / commit behavior;
5. driver/runtime/version の報告;
6. full Mtool config-store support が存在するとは主張しない。

### Risk matrix / risk matrix

| Risk / リスク | Reading / 読み方 | First mitigation / 初期対策 |
| --- | --- | --- |
| PHP driver availability | `PDO_FIREBIRD` may not be installed on host PHP by default. / host PHP に `PDO_FIREBIRD` が標準で入っていない可能性が高い。 | Make F2 opt-in and runtime-specific; do not require it in `make test`. |
| Embedded/local-file expectation | Firebird has current packages and docs, but the exact lowest-friction embedded/local-file path must be proven per platform. / package・docs はあるが、最小摩擦の embedded/local-file 経路は platform ごとに実証が必要。 | Use Docker/server smoke first for CI-style proof, then decide whether embedded/local-file is worth productizing. |
| Dialect fallback to MySQL | Existing helpers fall back to MySQL for unknown DSNs. / 既存 helper は unknown DSN を MySQL 扱いしやすい。 | Keep Firebird out of shared dialect helpers until a focused adapter is ready. |
| Config schema dialect differences | Identifier case, sequences/generators, BLOB/text, timestamp, boolean, and metadata queries may differ. / identifier case、sequence/generator、BLOB/text、timestamp、boolean、metadata query が違う可能性。 | F3 should run schema preflight after F2 connection proof. |
| Promotion code naming | Current promotion lane is SQLite-to-MySQL-specific. / 現行 promotion lane は SQLite-to-MySQL 固有。 | F4/F5 should introduce a target-profile/dialect adapter only after Firebird source/target behavior is known. |
| Backup/restore expectation | Firebird has `gbak` and `nbackup`, but Mtool has no Firebird backup lane. / Firebird には `gbak`・`nbackup` があるが Mtool 側 lane は未実装。 | Treat backup/restore as F2/F3 evidence, not current support. |

### F2 target / F2 target

Proceed to F2 with this target:

> Add a focused opt-in Firebird connection smoke that can run only when a Firebird-capable runtime is available. Prefer `PDO_FIREBIRD` as the PHP API. It should prove create/connect/prepared insert/select/transaction/version behavior and record a blocker if the runtime cannot be made available without unacceptable setup burden.

F2 は次を target にする。

> Firebird 対応 runtime がある時だけ動く focused opt-in connection smoke を追加する。PHP API は `PDO_FIREBIRD` を優先する。create/connect/prepared insert/select/transaction/version behavior を証明し、runtime 準備の負担が大きすぎる場合は blocker として記録する。

F2 must not:

- change `APP_CONFIG_STORE_DRIVER` accepted values;
- change normal `make test`;
- claim Firebird config-store support;
- generalize promotion code before a connection proof exists.

F2 では次をしない。

- `APP_CONFIG_STORE_DRIVER` の accepted values を変更しない;
- 通常の `make test` を変更しない;
- Firebird config-store support を主張しない;
- connection proof 前に promotion code を一般化しない。

## F2 first smoke entry / F2 first smoke入口

Date: 2026-07-13

Status: `RUNTIME_BLOCKER_RECORDED`

Added a focused opt-in smoke entry:

- Script: `mtool/scripts/check_firebird_connection_smoke.php`
- Make target: `make firebird-connection-smoke`
- Required runtime: PHP `PDO_FIREBIRD`
- Required environment: `MTOOL_FIREBIRD_DSN`, `MTOOL_FIREBIRD_USER`, `MTOOL_FIREBIRD_PASSWORD`
- Normal test impact: none; the target is not part of `make test`

focused opt-in smoke 入口を追加した。

- Script: `mtool/scripts/check_firebird_connection_smoke.php`
- Make target: `make firebird-connection-smoke`
- Required runtime: PHP `PDO_FIREBIRD`
- Required environment: `MTOOL_FIREBIRD_DSN`, `MTOOL_FIREBIRD_USER`, `MTOOL_FIREBIRD_PASSWORD`
- Normal test impact: なし。`make test` には含めない

Verification on the current host:

```bash
php -l mtool/scripts/check_firebird_connection_smoke.php
make -n firebird-connection-smoke
make firebird-connection-smoke
```

Result:

```json
{
  "ok": false,
  "stage": "runtime_preflight",
  "error": "pdo_firebird_extension_missing",
  "mutation_performed": false,
  "details": {
    "loaded_pdo_drivers": ["dblib", "mysql", "odbc", "pgsql", "sqlite"],
    "required_driver": "firebird"
  }
}
```

Interpretation:

- The smoke entry works as a diagnostic gate.
- The current host PHP cannot prove Firebird because `PDO_FIREBIRD` is not installed.
- This is not a Mtool schema blocker yet; it is a runtime availability blocker.
- The next F2 continuation is to choose the least-burden Firebird-capable runtime: Docker-based PHP with `PDO_FIREBIRD`, or a documented local PHP setup.

読み方:

- smoke 入口は diagnostic gate として機能している。
- 現在の host PHP では `PDO_FIREBIRD` がないため Firebird proof はできない。
- これはまだ Mtool schema blocker ではなく、runtime availability blocker。
- 次の F2 continuation は、最小負担の Firebird-capable runtime を選ぶこと。候補は Docker-based PHP with `PDO_FIREBIRD`、または documented local PHP setup。

## F2 Firebird server lane / F2 Firebird server lane

Date: 2026-07-13

Status: `DOCKER_CONNECTION_PROOF_DONE`

Added an opt-in Firebird server compose lane based on the official `firebirdsql/firebird:5.0.4` image:

- Compose file: `compose.user-db-firebird.yaml`
- Make targets:
  - `make up-user-db-firebird`
  - `make down-user-db-firebird`
  - `make reset-user-db-firebird`
  - `make ps-user-db-firebird`
  - `make logs-user-db-firebird`
  - `make health-user-db-firebird`
- Default host port: `13050`
- Default database: `lab_app.fdb`
- Default non-production user: `lab_app`

公式 `firebirdsql/firebird:5.0.4` image を使う opt-in Firebird server compose lane を追加した。

- Compose file: `compose.user-db-firebird.yaml`
- Make targets:
  - `make up-user-db-firebird`
  - `make down-user-db-firebird`
  - `make reset-user-db-firebird`
  - `make ps-user-db-firebird`
  - `make logs-user-db-firebird`
  - `make health-user-db-firebird`
- Default host port: `13050`
- Default database: `lab_app.fdb`
- Default non-production user: `lab_app`

This initially did not complete PHP-level connection proof because the current host PHP does not have `PDO_FIREBIRD`. A follow-up dedicated PHP smoke client image completed the Docker-based proof.

当初は現在の host PHP に `PDO_FIREBIRD` がないため PHP-level connection proof は未完だった。その後、専用 PHP smoke client image により Docker-based proof は完了した。

## F2 Docker PHP smoke proof / F2 Docker PHP smoke proof

Date: 2026-07-13

Status: `DONE_DOCKER_PROOF`

Added a dedicated opt-in PHP client image for Firebird proof:

- Dockerfile: `docker/firebird-php-smoke/Dockerfile`
- Base image: `php:8.4-cli`
- Firebird client dependency: Debian `firebird-dev`
- PHP extension: `docker-php-ext-install pdo_firebird`
- Compose service: `firebird-php-smoke` with `smoke` profile
- Make target: `make firebird-connection-smoke-docker`

Firebird proof 専用の opt-in PHP client image を追加した。

- Dockerfile: `docker/firebird-php-smoke/Dockerfile`
- Base image: `php:8.4-cli`
- Firebird client dependency: Debian `firebird-dev`
- PHP extension: `docker-php-ext-install pdo_firebird`
- Compose service: `firebird-php-smoke` with `smoke` profile
- Make target: `make firebird-connection-smoke-docker`

Verification:

```bash
make firebird-connection-smoke-docker
```

Result:

```json
{
  "ok": true,
  "stage": "ok",
  "error": "",
  "mutation_performed": true,
  "details": {
    "pdo_driver": "firebird",
    "engine_version": "5.0.4",
    "table": "MTOOL_FIREBIRD_SMOKE",
    "selected_row": {
      "ID": 2,
      "NOTE": "commit"
    }
  }
}
```

What this proves:

- Firebird 5.0.4 server can run as an opt-in local Docker lane.
- A PHP 8.4 `PDO_FIREBIRD` client can be built in a dedicated proof image.
- Mtool-side PHP can perform create table, prepared insert/select, rollback, commit, and version read against Firebird.
- The smoke can remain outside normal `make test`.

これで証明できたこと:

- Firebird 5.0.4 server は opt-in local Docker lane として起動できる。
- PHP 8.4 `PDO_FIREBIRD` client は専用 proof image で build できる。
- Mtool 側 PHP は Firebird に対して create table、prepared insert/select、rollback、commit、version read を実行できる。
- smoke は通常の `make test` の外に置ける。

What this does not prove yet:

- Host PHP has `PDO_FIREBIRD`.
- Embedded/serverless local-file operation is low-friction on every platform.
- Mtool config-store schema is Firebird-compatible.
- Generated DBAccess Firebird dialect is supported.
- SQLite-to-Firebird or Firebird-to-MySQL promotion is implemented.

まだ証明していないこと:

- host PHP に `PDO_FIREBIRD` があること。
- embedded/serverless local-file operation が全 platform で低摩擦であること。
- Mtool config-store schema が Firebird-compatible であること。
- generated DBAccess Firebird dialect が support されていること。
- SQLite-to-Firebird / Firebird-to-MySQL promotion が実装済みであること。

F2 is complete as a Docker-based local connection proof. The next step is F3: config-store schema fit and schema preflight against Firebird's dialect and metadata behavior.

F2 は Docker-based local connection proof として完了。次は F3: Firebird dialect と metadata behavior に対する config-store schema fit / schema preflight。

## F3 config schema preflight first pass / F3 config schema preflight first pass

Date: 2026-07-13

Status: `PREFLIGHT_BLOCKERS_RECORDED`

Added a read-only Firebird config-store schema fit preflight:

- Script: `mtool/scripts/firebird_config_schema_preflight.php`
- Make target: `make firebird-config-schema-preflight`
- Input: current MariaDB config-initdb SQL under `docker/mariadb/config-initdb`
- Mutation: none
- Default output: summary plus issue sample; full list is available with `--include-issues`

read-only Firebird config-store schema fit preflight を追加した。

- Script: `mtool/scripts/firebird_config_schema_preflight.php`
- Make target: `make firebird-config-schema-preflight`
- Input: `docker/mariadb/config-initdb` 配下の current MariaDB config-initdb SQL
- Mutation: なし
- Default output: summary と issue sample。全件は `--include-issues` で出せる。

Verification:

```bash
php -l mtool/scripts/firebird_config_schema_preflight.php
php mtool/scripts/firebird_config_schema_preflight.php --pretty --max-issues=3
```

First-pass result:

```json
{
  "ok": false,
  "stage": "firebird_config_schema_preflight",
  "mutation_performed": false,
  "summary": {
    "file_count": 42,
    "statement_count": 86,
    "create_table_count": 49,
    "alter_table_count": 26,
    "drop_statement_count": 6,
    "issue_count": 330,
    "severity_counts": {
      "blocker": 214,
      "warning": 116,
      "info": 0
    },
    "issue_counts_by_code": {
      "mysql_alter_add_if_not_exists": 20,
      "mysql_alter_after_position": 20,
      "mysql_auto_increment": 49,
      "mysql_datetime": 43,
      "mysql_drop_if_exists": 6,
      "mysql_mediumtext": 6,
      "mysql_on_update_timestamp": 38,
      "mysql_table_options": 49,
      "mysql_text": 33,
      "mysql_tinyint_boolean": 14,
      "mysql_unsigned_integer": 52
    }
  }
}
```

Interpretation:

- The config schema is not directly Firebird-compatible.
- The blockers are expected dialect blockers, not product blockers.
- The first implementation slice should be a Firebird DDL converter/preflight, not direct config-store runtime wiring.
- The converter must first handle identity/generator policy, unsigned integer normalization, MySQL table option removal, `ON UPDATE CURRENT_TIMESTAMP`, `ALTER ADD COLUMN IF NOT EXISTS`, `DROP COLUMN IF EXISTS`, text/blob policy, and identifier case.

読み方:

- config schema は Firebird に直接互換ではない。
- blocker は想定どおりの dialect blocker であり、product blocker ではない。
- first implementation slice は config-store runtime 直結ではなく、Firebird DDL converter / preflight にする。
- converter はまず identity/generator policy、unsigned integer normalization、MySQL table option removal、`ON UPDATE CURRENT_TIMESTAMP`、`ALTER ADD COLUMN IF NOT EXISTS`、`DROP COLUMN IF EXISTS`、text/blob policy、identifier case を扱う必要がある。

F3 is not closed yet. The next F3 continuation is to generate a disposable Firebird DDL plan from a narrow subset, apply it to the Firebird proof database, and inspect Firebird metadata behavior.

F3 はまだ完了ではない。次の F3 continuation は、狭い subset から disposable Firebird DDL plan を生成し、Firebird proof DB に適用し、Firebird metadata behavior を確認すること。

## F3 first-slice DDL apply proof / F3 first-slice DDL apply proof

Date: 2026-07-13

Status: `DONE_FIRST_SLICE_DDL_PROOF`

Added a narrow Firebird CREATE TABLE first-slice converter and Docker apply proof:

- Script: `mtool/scripts/firebird_config_schema_first_slice.php`
- Make target: `make firebird-config-schema-first-slice-docker`
- Scope: first 5 `CREATE TABLE` statements from `001_schema.sql`
- Applied target: disposable Firebird proof database
- Explicitly out of scope: indexes, unique keys, foreign keys, `ALTER`, seed DML, and config-store runtime wiring

狭い Firebird CREATE TABLE first-slice converter と Docker apply proof を追加した。

- Script: `mtool/scripts/firebird_config_schema_first_slice.php`
- Make target: `make firebird-config-schema-first-slice-docker`
- Scope: `001_schema.sql` の先頭 5 `CREATE TABLE`
- Applied target: disposable Firebird proof database
- Explicitly out of scope: indexes、unique keys、foreign keys、`ALTER`、seed DML、config-store runtime wiring

Converter rules proven in this slice:

- `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` -> `BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL`
- `BIGINT UNSIGNED` -> `BIGINT`
- `INT UNSIGNED` -> `INTEGER`
- `TINYINT(1)` -> `SMALLINT`
- `TEXT` / `MEDIUMTEXT` -> `BLOB SUB_TYPE TEXT`
- `DATETIME` -> `TIMESTAMP`
- MySQL `ON UPDATE CURRENT_TIMESTAMP` is stripped
- Firebird column order uses `DEFAULT ... NOT NULL`, not MySQL-style `NOT NULL DEFAULT ...`
- MySQL table options / secondary indexes / unique keys / foreign keys are dropped for this first slice

この slice で実証した converter rule:

- `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` -> `BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL`
- `BIGINT UNSIGNED` -> `BIGINT`
- `INT UNSIGNED` -> `INTEGER`
- `TINYINT(1)` -> `SMALLINT`
- `TEXT` / `MEDIUMTEXT` -> `BLOB SUB_TYPE TEXT`
- `DATETIME` -> `TIMESTAMP`
- MySQL `ON UPDATE CURRENT_TIMESTAMP` は除去
- Firebird column order は MySQL式 `NOT NULL DEFAULT ...` ではなく `DEFAULT ... NOT NULL`
- MySQL table options / secondary indexes / unique keys / foreign keys は first slice では drop

Verification:

```bash
php -l mtool/scripts/firebird_config_schema_first_slice.php
php mtool/scripts/firebird_config_schema_first_slice.php --pretty --limit=2
make firebird-config-schema-first-slice-docker
```

Docker apply result:

```json
{
  "ok": true,
  "stage": "firebird_config_schema_first_slice",
  "mutation_performed": true,
  "summary": {
    "planned_table_count": 5,
    "apply": {
      "requested": true,
      "applied": 5,
      "skipped": 0,
      "errors": []
    }
  }
}
```

Metadata inspection result:

```text
PROJECTS                                                10 columns
PROJECT_HOST_ASSIGNMENTS                                10 columns
PROJECT_MEMBERSHIPS                                      6 columns
PROJECT_PAGE_SECURITY_POLICIES                           8 columns
PROJECT_PAGE_SECURITY_POLICY_CAPABILITIES                4 columns
```

F3 conclusion:

- Firebird can accept a converted config-schema DDL subset.
- Firebird metadata inspection through `RDB$RELATIONS` and `RDB$RELATION_FIELDS` is sufficient for a preflight/apply proof.
- Full config-store support is still not claimed.
- Next Firebird lane can move to SQLite-to-Firebird promotion contract planning, while any full Firebird config-store implementation remains a later, explicit expansion.

F3 結論:

- Firebird は変換済み config-schema DDL subset を受け入れられる。
- `RDB$RELATIONS` と `RDB$RELATION_FIELDS` による Firebird metadata inspection は preflight/apply proof に十分。
- full config-store support はまだ主張しない。
- 次の Firebird lane は SQLite-to-Firebird promotion contract planning へ進める。一方、full Firebird config-store implementation は後続の明示 expansion として扱う。

## F4 SQLite-to-Firebird promotion contract first slice / F4 SQLite-to-Firebird promotion contract first slice

Date: 2026-07-13

Status: `DONE_CONTRACT_SHAPE`

Added a side-effect-free Firebird local durable promotion contract:

- Module: `mtool/app/sqlite_firebird_promotion_contract.php`
- Test: `tests/Integration/SqliteFirebirdPromotionContractTest.php`
- Direction: SQLite local/lightweight source -> Firebird local durable file profile
- Scope: contract shape, deterministic table planning, type mapping, source retention, target file profile, required approvals, and verification gates
- Explicitly out of scope: row export/import, Firebird DDL creation, live Firebird mutation, cutover, automatic source deletion, reverse Firebird-to-SQLite migration, bidirectional sync, and zero-downtime CDC

副作用なしの Firebird local durable promotion contract を追加した。

- Module: `mtool/app/sqlite_firebird_promotion_contract.php`
- Test: `tests/Integration/SqliteFirebirdPromotionContractTest.php`
- Direction: SQLite local/lightweight source -> Firebird local durable file profile
- Scope: contract shape、deterministic table planning、type mapping、source retention、target file profile、required approvals、verification gates
- Explicitly out of scope: row export/import、Firebird DDL creation、live Firebird mutation、cutover、automatic source deletion、Firebird-to-SQLite reverse migration、bidirectional sync、zero-downtime CDC

The Firebird contract intentionally reuses the established SQLite-to-MySQL promotion discipline where it is still valid:

- no secrets in artifacts;
- no mutation in planning artifacts;
- deterministic table order;
- canonical metadata remains the design authority;
- SQLite source inspection must match canonical metadata;
- stable primary keys are required;
- FK cycles fail closed for v1;
- source SQLite is retained after promotion as rollback/evidence.

Firebird-specific decisions in this first slice:

- target driver is `firebird`;
- target profile is `local_durable_file`;
- target must be new or empty;
- backup/restore smoke is a required verification gate;
- `boolean` maps to `SMALLINT`;
- `json` maps to `BLOB SUB_TYPE TEXT` with `json_stored_as_text` warning;
- `text/string` maps to `BLOB SUB_TYPE TEXT`;
- `blob/binary` maps to `BLOB SUB_TYPE BINARY`;
- `datetime/timestamp` maps to `TIMESTAMP`;
- integer primary keys use `preserve_source_values_then_advance_firebird_identity_or_generator`.

この first slice での Firebird 固有判断:

- target driver は `firebird`;
- target profile は `local_durable_file`;
- target は new または empty 必須;
- backup/restore smoke を required verification gate に入れる;
- `boolean` は `SMALLINT`;
- `json` は `BLOB SUB_TYPE TEXT` に写し、`json_stored_as_text` warning を出す;
- `text/string` は `BLOB SUB_TYPE TEXT`;
- `blob/binary` は `BLOB SUB_TYPE BINARY`;
- `datetime/timestamp` は `TIMESTAMP`;
- integer primary key は `preserve_source_values_then_advance_firebird_identity_or_generator`。

Verification:

```bash
php -l mtool/app/sqlite_firebird_promotion_contract.php
php -l tests/Integration/SqliteFirebirdPromotionContractTest.php
bash mtool/scripts/run_sample_pack_phpunit_test.sh \
  --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml \
  --run-script=sample/tutorials/sample01-simple-table-runtime/run.sh \
  --phpunit-target=/var/www/tests/Integration/SqliteFirebirdPromotionContractTest.php
```

Focused PHPUnit result:

```text
OK (7 tests, 34 assertions)
```

F4 conclusion:

- The side-effect-free SQLite-to-Firebird promotion artifact shape is now explicit.
- The next lane can move to Firebird-to-MySQL/MariaDB promotion boundary planning.
- Full SQLite-to-Firebird copy/import tooling remains a later implementation expansion, not required to close this planning step.

F4 結論:

- 副作用なし SQLite-to-Firebird promotion artifact shape は明確になった。
- 次の lane は Firebird-to-MySQL/MariaDB promotion boundary planning へ進める。
- full SQLite-to-Firebird copy/import tooling は後続の implementation expansion であり、この planning step の完了条件ではない。

## F5 Firebird-to-MySQL/MariaDB promotion boundary / F5 Firebird-to-MySQL/MariaDB promotion boundary

Date: 2026-07-13

Status: `DONE_BOUNDARY`

Decision:

Firebird-to-MySQL/MariaDB should not become a second, independent promotion framework. It should reuse the established offline SQLite-to-MySQL promotion chain wherever the target-side rule is the same, and add only a Firebird source adapter plus Firebird-specific evidence collectors.

Firebird-to-MySQL/MariaDB は、2つ目の独立した promotion framework にしない。target側 rule が同じところは既存 SQLite-to-MySQL promotion chain を再利用し、Firebird source adapter と Firebird固有 evidence collector だけを追加する。

Supported v1 direction:

- Firebird local durable profile -> fresh MySQL/MariaDB server profile
- Offline one-way promotion only
- Explicit source backup before export
- Explicit write freeze before final export
- Fresh/empty target only
- No automatic source delete
- No MySQL/MariaDB-to-Firebird reverse path
- No bidirectional sync or CDC

Reuse from the SQLite-to-MySQL lane:

- side-effect-free manifest/contract discipline;
- no secrets in artifacts;
- canonical metadata as design authority;
- stable primary-key requirement;
- deterministic load order;
- target MySQL/MariaDB DDL review boundary;
- chunked import/checkpoint model;
- verification concepts: row counts, primary-key set, row value digests, nullability, unique keys, foreign keys, JSON/BLOB/timestamp value classes, next-id safety, DBAccess smoke;
- cutover/operator/rehearsal approval model.

Firebird-specific deltas:

| Area | Firebird delta | V1 handling |
| --- | --- | --- |
| Metadata inspection | Use `RDB$RELATIONS`, `RDB$RELATION_FIELDS`, `RDB$FIELDS`, `RDB$RELATION_CONSTRAINTS`, `RDB$INDEX_SEGMENTS`, and `RDB$REF_CONSTRAINTS` rather than SQLite PRAGMA. | Build a Firebird source inspection adapter first; do not reuse SQLite PRAGMA code. |
| Identifier case | Unquoted Firebird identifiers are stored uppercase. | Normalize to canonical names and fail closed on ambiguous quoted/case-sensitive names. |
| Identity / generator | Identity/generator state must be inspected separately from max PK. | Verify target MySQL `AUTO_INCREMENT` exceeds imported max, and record Firebird source identity evidence separately. |
| BLOB text/binary | Firebird text and binary BLOBs require subtype-aware handling. | Hash BLOBs by byte stream; text BLOBs must use declared charset/UTF-8 policy. |
| JSON | Firebird does not provide native MySQL JSON equivalence in this lane. | Treat JSON as validated logical text, then map to MySQL JSON only when canonical metadata requires and values pass validation. |
| Timestamp | Firebird timestamp is timezone-less. | Require declared convention, usually UTC, before mapping to MySQL `DATETIME(6)`/`TIMESTAMP` policy. |
| Boolean | Firebird local profile likely stores booleans as `SMALLINT` unless a later native boolean rule is qualified. | Require value profile to contain only `0`, `1`, and `NULL`. |
| Consistent read | Firebird should use a read-only snapshot transaction for export evidence. | First adapter must prove stable count/hash under a read-only transaction. |
| Backup | Local file durability depends on a restorable backup story. | Source backup reference and backup/restore smoke are required before cutover. |

First implementation candidate:

Add a read-only Firebird source inspection adapter, not target import:

```php
app_firebird_mysql_promotion_source_inspection(PDO $firebird, array $canonicalSnapshot, array $options = []): array;
```

Candidate boundaries:

- opens no MySQL/MariaDB connection;
- writes no file;
- performs no Firebird mutation;
- runs inside a read-only Firebird transaction where supported;
- returns normalized table/column/key/FK/value-profile evidence compatible with the existing SQLite-to-MySQL manifest/verification concepts;
- records Firebird metadata ambiguity as blockers rather than guessing;
- has a small fixture-backed test that can run without a live Firebird server for normalization, plus an opt-in Docker/live smoke later for real metadata.

F5 conclusion:

- The next implementation should start at source inspection, not import/cutover.
- The existing SQLite-to-MySQL target/import/verification/cutover model remains the target-side reference.
- Firebird work should stay local-profile-focused until a real Firebird-to-server migration demand appears.

F5 結論:

- 次の実装は import/cutover ではなく source inspection から始める。
- 既存 SQLite-to-MySQL target/import/verification/cutover model は target-side reference として維持する。
- Firebird 作業は、実際の Firebird-to-server migration 需要が出るまでは local-profile-focused に保つ。

## F6 Firebird lane checkpoint / F6 Firebird lane checkpoint

Date: 2026-07-13

Status: `DONE_NARROW_GO`

Checkpoint result:

The Firebird local durable profile feasibility lane has no known feasibility blocker inside the agreed narrow boundary. It is not complete broad Firebird product support. It is enough evidence to continue with one small implementation-first slice: a read-only Firebird source inspection adapter.

Firebird local durable profile feasibility lane は、合意済みの狭い境界内では known feasibility blocker なし。これは Firebird 全面 product support 完了ではない。一方で、read-only Firebird source inspection adapter という小さな implementation first slice に進むだけの根拠は揃った。

Completed evidence:

- F1: product position, current Firebird reality, platform/runtime risk, and non-goals recorded.
- F2: Firebird 5.0.4 server + PHP 8.4 `PDO_FIREBIRD` Docker connection smoke passed.
- F3: config-schema read-only preflight plus 5-table Firebird DDL apply/metadata proof completed.
- F4: side-effect-free SQLite-to-Firebird local durable promotion contract added and tested.
- F5: Firebird-to-MySQL/MariaDB reuse/delta boundary recorded.

Supported boundary after checkpoint:

- Firebird is considered a candidate local durable profile between SQLite and MySQL/MariaDB.
- SQLite-to-Firebird is one-way for local durability upgrade.
- Firebird-to-MySQL/MariaDB is one-way for later server operation.
- Planning artifacts must remain secret-free and side-effect-free.
- Source deletion is forbidden automatically.
- Source backup and backup/restore smoke remain required before any cutover claim.
- Firebird work stays narrow until a concrete app/user demand exists.

Not claimed:

- full Mtool config-store runtime on Firebird;
- generated DBAccess full Firebird runtime support across all samples;
- Firebird as a replacement for MySQL/MariaDB server operation;
- automatic cutover;
- reverse migration;
- bidirectional sync;
- zero-downtime CDC.

Decision:

Proceed with one implementation-first slice:

```text
I1: Firebird source inspection adapter first slice
```

The first slice should normalize Firebird metadata/value evidence without mutation. It should not import data, generate MySQL DDL, switch configuration, or claim cutover readiness.

After I1, the plan should checkpoint again. If no concrete Firebird implementation demand remains, the main product lane can move to the already planned no-code app/mobile handoff work.

F6 判断:

- narrow-go: source inspection adapter first slice だけ進める。
- broad-go ではない: Firebird全面対応、import/cutover、runtime全面対応はまだ進めない。
- I1後に再checkpointし、追加需要がなければ No Code app / mobile handoff lane へ進む。

## I1 Firebird source inspection array normalizer / I1 Firebird source inspection array normalizer

Date: 2026-07-13

Status: `DONE_ARRAY_NORMALIZER`

Added the first implementation slice for Firebird source inspection:

- Module: `mtool/app/firebird_source_inspection.php`
- Test: `tests/Integration/FirebirdSourceInspectionTest.php`
- Boundary: pure array-level normalization only
- No PDO connection
- No filesystem write
- No Firebird mutation
- No MySQL/MariaDB target connection
- No import/cutover claim

Firebird source inspection の first implementation slice を追加した。

- Module: `mtool/app/firebird_source_inspection.php`
- Test: `tests/Integration/FirebirdSourceInspectionTest.php`
- Boundary: pure array-level normalization only
- PDO connection なし
- filesystem write なし
- Firebird mutation なし
- MySQL/MariaDB target connection なし
- import/cutover claim なし

The normalizer accepts Firebird metadata/value-profile row arrays and returns a promotion-source inspection shape:

- `inspection_version=firebird-source-inspection-v1`
- `driver=firebird`
- `stage=source_inspection`
- `mutation_performed=false`
- normalized table list
- normalized columns with Firebird type text
- primary/unique keys from constraints and index segments
- foreign keys from ref constraints
- row counts
- value profiles
- blockers for unsupported quoted/case-sensitive identifiers, missing FK references, missing columns, unsupported constraints, and secret-bearing input

This keeps the source adapter separate from the existing SQLite PRAGMA path, while producing a shape that can feed later promotion contracts.

Verification:

```bash
php -l mtool/app/firebird_source_inspection.php
php -l tests/Integration/FirebirdSourceInspectionTest.php
bash mtool/scripts/run_sample_pack_phpunit_test.sh \
  --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml \
  --run-script=sample/tutorials/sample01-simple-table-runtime/run.sh \
  --phpunit-target=/var/www/tests/Integration/FirebirdSourceInspectionTest.php
```

Focused PHPUnit result:

```text
OK (6 tests, 30 assertions)
```

Next:

I2 is optional Docker/live metadata smoke: collect real metadata rows from the Firebird proof database and feed them through this pure normalizer. If that proves too large for the current Firebird lane, park I2 and move to No Code app/mobile handoff.

## I2 Firebird source inspection Docker/live metadata smoke / I2 Firebird source inspection Docker/live metadata smoke

Date: 2026-07-13

Status: `DONE_LIVE_METADATA_SMOKE`

Added Docker/live metadata smoke for the Firebird source inspection normalizer:

- Script: `mtool/scripts/check_firebird_source_inspection_smoke.php`
- Compose service: `firebird-source-inspection-smoke`
- Make target: `make firebird-source-inspection-smoke-docker`
- Setup dependency: `firebird-config-schema-first-slice-docker`

The smoke reads real Firebird proof-database metadata through `PDO_FIREBIRD`, lowercases PDO-returned metadata keys, maps Firebird field type codes to normalized type names, prefers `RDB$CHARACTER_LENGTH` over byte length for character columns, and feeds the result through `app_firebird_source_inspection_normalize()`.

Firebird source inspection normalizer の Docker/live metadata smoke を追加した。smoke は `PDO_FIREBIRD` 経由で実 Firebird proof DB metadata を読み、PDO が返す metadata key を lowercase 化し、Firebird field type code を normalized type name に変換し、文字列 column では byte length ではなく `RDB$CHARACTER_LENGTH` を優先し、`app_firebird_source_inspection_normalize()` に渡す。

Verification:

```bash
php -l mtool/app/firebird_source_inspection.php
php -l mtool/scripts/check_firebird_source_inspection_smoke.php
bash mtool/scripts/run_sample_pack_phpunit_test.sh \
  --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml \
  --run-script=sample/tutorials/sample01-simple-table-runtime/run.sh \
  --phpunit-target=/var/www/tests/Integration/FirebirdSourceInspectionTest.php
make firebird-source-inspection-smoke-docker
make down-user-db-firebird
```

Focused PHPUnit:

```text
OK (6 tests, 30 assertions)
```

Docker/live smoke result:

```json
{
  "ok": true,
  "stage": "firebird_source_inspection_smoke",
  "mutation_performed": false,
  "metadata_counts": {
    "relations": 6,
    "fields": 41,
    "constraints": 40,
    "index_segments": 6,
    "ref_constraints": 0
  },
  "inspection": {
    "ok": true,
    "driver": "firebird",
    "mutation_performed": false,
    "blockers": []
  }
}
```

Firebird narrow-lane checkpoint after I2:

- The current narrow Firebird profile work is complete enough to park unless a concrete app/user migration demand appears.
- It is still not broad Firebird support.
- Next active product direction moves to No Code app/mobile handoff, starting with app-creator-facing spec output.

I2 後の Firebird narrow-lane checkpoint:

- 現在の狭い Firebird profile 作業は、具体的な app/user migration 需要が出るまで park できる水準になった。
- これは Firebird 全面対応ではない。
- 次の active product direction は No Code app / mobile handoff へ移り、app作成者向け spec output から始める。
