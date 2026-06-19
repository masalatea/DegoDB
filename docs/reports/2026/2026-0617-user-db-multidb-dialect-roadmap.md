# 2026-06-17 User DB Multi-DB Dialect Roadmap

## Purpose

ユーザー DB 側の SQLite support を、SQLite 単独の完了タスクとしてではなく、MySQL / MariaDB mainline と SQLite first expansion を揃える dialect framework として整理する。

PostgreSQL / SQL Server などの他 DB server 対応は、この計画では実装対象にしない。設計上の逃げ道を塞がない程度に意識するが、優先度は `must-have feature` 完了後の後続候補へ下げる。

ここで扱うのは DegoDB / Mtool 自身の config store ではない。対象は、ユーザーの業務 DB へ接続し、その schema から DBAccess class、runtime SQL、proxy / OpenAPI / custom runtime bundle を出力する層である。

## Current Status As Of 2026-06-19

The user DB dialect framework first stop-line is complete. The current covered contract set is:

- `sample10-dbaccess-mini-crud-flow`: CRUD behavior.
- `sample06-dbaccess-filter-sort-page`: filter / sort / pagination behavior.
- `sample08-dbaccess-join-read-model`: join read model behavior.
- `sample09-dbaccess-aggregate-report`: aggregate / group by / having behavior.

There is no remaining immediate cleanup task in this lane before moving to the next foundation work. Future dialect work should resume from this document only when a new user DB contract slice is explicitly chosen. PostgreSQL / SQL Server remain parked.

## Boundary

Mtool 側 config store:

- MySQL / MariaDB profile と folder-backed SQLite profile を current scope の supported store とする。
- `make mtool-lite-smoke` と artifact parity gate で regression を維持する。
- Mtool 側 SQLite support は current scope で 100% 完了扱い。

ユーザー DB 側:

- MySQL / MariaDB を mainline として維持する。
- SQLite は first expansion として扱う。
- PostgreSQL / SQL Server は parked / post-must-features として扱う。dialect abstraction と contract compare が安定しても、must-have feature が揃うまでは実装へ進まない。
- SQLite だけを独立して完了扱いにせず、multi-DB 共通化の中で完成度を上げる。

当面の stop line:

1. `sample10` の CRUD contract compare。done.
2. sample-specific hardcode を減らす最小 generalization。done.
3. `sample06` の filter / sort / pagination contract compare。done.
4. `sample08` の join read model contract compare。done.
5. `sample09` の aggregate report contract compare。done.

上記 5 番まで完了した。ここで他 DB server 対応へ進まず、いったん must-have feature 側へ戻る。

## Target DB Matrix

| DB | Role | Current Reading | Direction |
| --- | --- | --- | --- |
| MySQL / MariaDB | Mainline | 既存の user DB / generated output の基準 | regression gate として維持し、dialect helper へ段階的に寄せる。 |
| SQLite | First expansion | source introspection と runtime output の first slice あり | local / file-backed app、prototype、generated DBAccess smoke の基準にする。 |
| PostgreSQL | Parked candidate | 未着手 | must-have feature 完了後に再評価する。現時点では実装しない。 |
| SQL Server | Parked candidate | 未着手 | must-have feature 完了後に再評価する。現時点では実装しない。 |

## Capability Matrix

ユーザー DB 対応は `接続できる` だけでは完了としない。DB ごとに次の capability を分けて管理する。

| Capability | MySQL / MariaDB | SQLite | PostgreSQL | SQL Server |
| --- | --- | --- | --- | --- |
| Connection profile | mainline | first slice | parked | parked |
| Schema introspection | mainline | first slice | parked | parked |
| Type normalization | partial | partial | parked | parked |
| SQL dialect helper | partial | partial | parked | parked |
| DBAccess class output | mainline | first slice | parked | parked |
| Runtime adapter | mainline | first slice | parked | parked |
| CRUD / SELECT execution smoke | mainline | first slice | parked | parked |
| Join / aggregate / pagination | mainline patterns | first slice / review needed | parked | parked |
| Blob / file / JSON | review needed | review needed | parked | parked |
| Proxy / OpenAPI bundle smoke | mainline samples | first slice samples | parked | parked |
| Dialect-aware contract compare | sample10/sample06/sample08/sample09 done | sample10/sample06/sample08/sample09 done | parked | parked |

`parked` means the framework should not make future support impossible, but no implementation slice should be started before must-have product features are done.

## Contract Compare Framework

Config store parity と user DB dialect parity は比較目的が違う。

Config store parity:

- MySQL / MariaDB config store と SQLite config store が、同じ generated artifact / output を出すことを確認する。
- 原則 byte-for-byte equality を使う。
- 現行の `make artifact-parity-test` がこの役割を持つ。

User DB dialect parity:

- MySQL-target output と SQLite-target output は、SQL 文、type name、adapter detail が意図的に異なってよい。
- byte-for-byte equality ではなく、normalized contract manifest を比較する。
- 目的は `同じ業務 schema / API intent から、同じ外部 contract と runtime behavior が得られること` を確認すること。

比較対象:

- generated class / method surface
- method parameter names、types、nullability、default handling
- result shape、field names、nullable handling
- CRUD behavior
- SELECT / join / aggregate / pagination behavior
- placeholder style と bind ordering
- generated OpenAPI schema / examples where relevant
- proxy / runtime route status code and payload contract
- fixture DB に対する runtime execution result

保管形:

- dialect ごとに capture folder を分ける。
- 例: `work/user-db-contract/<run-id>/mysql/<sample>/`
- 例: `work/user-db-contract/<run-id>/sqlite/<sample>/`
- 各 lane の raw output は残す。
- 比較は raw file ではなく normalized contract manifest を主対象にする。

Failure report:

- sample key
- DB dialect
- generated artifact key
- contract section
- expected value
- actual value
- dialect-specific allowlist reason, if any

First slice implemented:

- `mtool/scripts/user_db_contract.php`
  - `manifest`: DBACCESS-PHP output から normalized contract manifest を作る。
  - `compare`: 2 lane の manifest を比較する。
- `mtool/scripts/user_db_contract_runtime_smoke.php`
  - selected sample の generated DBAccess を fixture DB に対して実行し、runtime result を manifest 比較へ入れる。
- `mtool/scripts/run_user_db_contract_capture.sh`
  - selected sample を MySQL / MariaDB lane または SQLite config store lane で実行し、DBACCESS-PHP / DATACLASS-PHP raw output、runtime result、manifest を `work/user-db-contract/<run-id>/<lane>/` に保存する。
- Make targets:
  - `make user-db-contract-capture-mysql`
  - `make user-db-contract-capture-sqlite`
  - `make user-db-contract-compare`
  - `make user-db-contract-test`

Current manifest fields:

- class / base class
- method name
- action type
- parameters and defaults
- normalized SQL
- bind expressions and bind ordering
- result fields and field order
- cardinality: `list`, `single`, `write-result`, or `unknown`
- runtime operations selected by sample definition:
  - list before write
  - detail before write
  - insert
  - update
  - detail after update
  - delete
  - detail after delete
  - list after write
  - filtered / sorted / limited list
  - join read model list
  - aggregate report list

Before expanding beyond sample10, do a small generalization pass:

- sample key / project key / source output keys を capture definition として持つ。first slice done for sample10/sample06/sample08/sample09.
- fixture schema / fixture seed を sample ごとに定義できるようにする。first slice done for sample10/sample06/sample08/sample09.
- runtime method calls と expected result shape を sample ごとに定義できるようにする。first slice done for sample10/sample06/sample08/sample09.
- MySQL / MariaDB fixture と SQLite fixture の差分は runner 内で吸収する。first slice done for sample10/sample06/sample08/sample09.
- ただし、この段階で汎用 testing DSL や全 DB 抽象 engine は作らない。sample06 / sample08 / sample09 を重複少なく追加できる最小限に留める。

Generalization first slice:

- `mtool/scripts/lib/user_db_contract_runtime.php` に runtime contract definition / fixture / runner を分離した。
- `user_db_contract_runtime_smoke.php` は thin CLI とし、`--sample=...` で sample definition を選ぶ。
- sample10 definition は dataclass files、dbaccess files、MySQL fixture SQL、SQLite fixture SQL、runtime runner を持つ。
- sample06 definition は dataclass files、dbaccess files、MySQL fixture SQL、SQLite fixture SQL、runtime runner を持つ。
- sample08 definition は dataclass files、dbaccess files、MySQL fixture SQL、SQLite fixture SQL、runtime runner を持つ。
- sample09 definition は dataclass files、dbaccess files、MySQL fixture SQL、SQLite fixture SQL、runtime runner を持つ。
- runtime result shape は generic object summary に寄せ、sample-specific summary 関数を減らした。
- Runtime aggregate result は MySQL DECIMAL string と SQLite numeric value のような dialect 表現差を normalized numeric value に寄せる。

## Generator Responsibility

Mtool が生成可能かどうかの初期判断は AI-assisted review でよい。ただし、AI の判定は automatic apply ではなく review artifact として扱う。

分類:

- `generated`: 標準 generator で生成する。
- `generated_with_options`: generator で生成できるが、dialect / pagination / null handling / naming などの option が必要。
- `inherited_custom`: generated base class を出した上で、継承先 class に個別実装する。
- `manual_runtime`: custom proxy / custom runtime / handwritten repository に委ねる。
- `needs_design_review`: schema、relation、key、transaction boundary の確認が必要。

生成器が責任を持つ範囲:

- table / column metadata から自然に導ける CRUD。
- simple SELECT、where、order、pagination、join、aggregate の共通パターン。
- prepared statement 化できる scalar parameters。
- standard runtime adapter / connection profile。
- DB ごとの型差分を canonical metadata に正規化できる範囲。

継承先 class または custom runtime に委ねる範囲:

- blob / file upload など driver ごとの binding strategy が大きく違う処理。
- vendor-specific SQL function、stored procedure、trigger-dependent behavior。
- 複雑な transaction、lock、upsert conflict policy。
- 高度な full-text search、JSON path、geospatial、window function の個別最適化。
- 生成 code の読みやすさや安全性を壊してまで汎用化する必要がある処理。

## Minimal First Sample

multi-DB framework の最初の実装 sample は、小さくても比較目的を満たすものにする。

候補:

- simple table CRUD
- nullable / default / status field
- DBAccess SELECT with filter / sort / pagination
- join read model

first acceptance:

- MySQL / MariaDB と SQLite の両方で fixture DB を作れる。
- 両 dialect で DBAccess class output を生成できる。
- 両 dialect で runtime execution smoke が通る。
- raw output は dialect ごとに保存される。
- normalized contract manifest が一致する。
- 差分が必要な場合は allowlist に理由を残す。

Selected first sample:

- `sample10-dbaccess-mini-crud-flow`
  - list / detail / insert / update / delete を含む最小 CRUD flow。
  - MySQL / MariaDB config store lane と SQLite config store lane の両方で既存 sample test が通る。
  - first slice では DBACCESS-PHP output の normalized static contract と、MySQL / MariaDB `db-lab` および SQLite file fixture に対する runtime execution result を比較する。

Verification:

- `make user-db-contract-test USER_DB_CONTRACT_RUN_ID=codex-sample10-runtime2`: passed.
  - MySQL / MariaDB lane: `Sample10DbAccessMiniCrudFlowOutputTest.php` passed, `1 test, 28 assertions`.
  - MySQL / MariaDB runtime: generated DBAccess executed against `db-lab` fixture.
  - SQLite lane: `Sample10DbAccessMiniCrudFlowOutputTest.php` passed, `1 test, 28 assertions`.
  - SQLite runtime: generated DBAccess executed against file-backed SQLite fixture.
  - contract compare: `user DB contract OK`.
- Targeted PHPUnit for manifest parser:
  - `UserDbContractManifestTest.php` passed, `2 tests, 28 assertions`.
- After generalization:
  - `make user-db-contract-test USER_DB_CONTRACT_RUN_ID=codex-generalized-sample10`: passed.
  - `UserDbContractManifestTest.php` passed, `3 tests, 34 assertions`.
- sample06:
  - `make user-db-contract-test USER_DB_CONTRACT_RUN_ID=codex-sample06 USER_DB_CONTRACT_SAMPLE=sample06-dbaccess-filter-sort-page`: passed.
  - MySQL / MariaDB lane: `Sample6DbAccessFilterSortPageOutputTest.php` passed, `1 test, 16 assertions`.
  - SQLite lane: `Sample6DbAccessFilterSortPageOutputTest.php` passed, `1 test, 16 assertions`.
  - contract compare: `user DB contract OK`.
  - `UserDbContractManifestTest.php` passed, `4 tests, 40 assertions`.
- sample08:
  - `make user-db-contract-test USER_DB_CONTRACT_RUN_ID=codex-sample08 USER_DB_CONTRACT_SAMPLE=sample08-dbaccess-join-read-model`: passed.
  - MySQL / MariaDB lane: `Sample8DbAccessJoinReadModelOutputTest.php` passed, `1 test, 25 assertions`.
  - SQLite lane: `Sample8DbAccessJoinReadModelOutputTest.php` passed, `1 test, 25 assertions`.
  - contract compare: `user DB contract OK`.
  - `UserDbContractManifestTest.php` passed, `5 tests, 46 assertions`.
- sample09:
  - `make user-db-contract-test USER_DB_CONTRACT_RUN_ID=codex-sample09 USER_DB_CONTRACT_SAMPLE=sample09-dbaccess-aggregate-report`: passed.
  - MySQL / MariaDB lane: `Sample09DbAccessAggregateReportOutputTest.php` passed, `1 test, 26 assertions`.
  - SQLite lane: `Sample09DbAccessAggregateReportOutputTest.php` passed, `1 test, 26 assertions`.
  - contract compare: `user DB contract OK`.
  - `UserDbContractManifestTest.php` passed, `7 tests, 53 assertions`.

## Completion Handoff

Status at close on 2026-06-18:

- Mtool-side SQLite support remains current-scope 100%.
- User DB contract framework currently covers:
  - `sample10-dbaccess-mini-crud-flow`: CRUD behavior.
  - `sample06-dbaccess-filter-sort-page`: filter / sort / pagination behavior.
  - `sample08-dbaccess-join-read-model`: join read model behavior.
  - `sample09-dbaccess-aggregate-report`: aggregate / group by / having behavior.
- The current stop-line is complete.
- PostgreSQL / SQL Server remain parked. Do not start those before must-have feature work.
- Next planning lane should return to must-have feature work.

Current changed files for this slice:

- `mtool/scripts/lib/user_db_contract_runtime.php`
- `mtool/scripts/run_user_db_contract_capture.sh`
- `tests/Integration/UserDbContractManifestTest.php`
- `docs/reports/2026/2026-0617-user-db-multidb-dialect-roadmap.md`
- `docs/reports/2026/2026-0617-db-layer-parity-inventory.md`

Last verification before close:

- `php -l mtool/scripts/lib/user_db_contract_runtime.php`: passed.
- `php -l tests/Integration/UserDbContractManifestTest.php`: passed.
- `bash -n mtool/scripts/run_user_db_contract_capture.sh`: passed.
- `php mtool/scripts/user_db_contract_runtime_smoke.php --sample=sample09-dbaccess-aggregate-report ... --dialect=sqlite`: passed.
- `make user-db-contract-test USER_DB_CONTRACT_RUN_ID=codex-sample09 USER_DB_CONTRACT_SAMPLE=sample09-dbaccess-aggregate-report`: passed.
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample10-dbaccess-mini-crud-flow/compose.yaml --run-script=./sample/tutorials/sample10-dbaccess-mini-crud-flow/run.sh --phpunit-target=/var/www/tests/Integration/UserDbContractManifestTest.php`: passed with `7 tests, 53 assertions`.

## Next Actions

1. Existing artifact parity framework は config store parity gate として維持する。
2. User DB dialect 用に normalized contract manifest の schema を定義する。first slice done for DBACCESS-PHP.
3. Minimal first sample を 1 本選び、MySQL / MariaDB lane と SQLite lane の raw output capture を作る。first slice done for sample10.
4. raw output の完全一致ではなく、class / method / parameter / result / runtime behavior の contract compare を実装する。first slice done for class / method / parameter / SQL / bind / result shape / runtime CRUD behavior.
5. Runtime execution result を contract manifest に足す。first slice done for sample10.
6. sample10 専用 runtime smoke を、sample definition / fixture definition / expected result definition に分ける最小 generalization を行う。done.
7. `sample06-dbaccess-filter-sort-page` を user DB contract compare に追加する。done.
8. `sample08-dbaccess-join-read-model` を user DB contract compare に追加する。done.
9. `sample09-dbaccess-aggregate-report` を user DB contract compare に追加する。done.
10. AI-assisted generation review を sample06 / sample08 / sample09 に残し、`generated` / `generated_with_options` / `inherited_custom` / `manual_runtime` を明示する。next must-have planning input.
11. ここまで完了したので、PostgreSQL / SQL Server へ進まず、must-have feature work へ戻る。
12. PostgreSQL / SQL Server は must-have feature 完了後に、必要性と費用対効果を再評価する。
