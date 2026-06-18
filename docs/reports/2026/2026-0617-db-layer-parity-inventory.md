# 2026-06-17 DB Layer Parity Inventory

## Purpose

SQLite 対応を SQLite だけの進捗として見ず、MySQL / MariaDB 側と access layer が揃っているかを棚卸しする。

Mtool は自分が生成した DBAccess / runtime output を、自分自身の proxy / runtime bundle でも使う。そのため、次の 2 つを分けて見る必要がある。

- DegoDB / Mtool 自身の design metadata store。
- ユーザー DB 向けの generated output / runtime bundle。

## Summary

現時点の結論:

- Mtool admin mainline の config store access は、MySQL / MariaDB と SQLite の両方とも PDO repository layer で揃える。
- SQLite config store 対応は、SQLite だけ generated DBAccess へ逃がす一時対応ではない。
- runtime bundle / proxy bundle は、MySQL / MariaDB と SQLite の両方とも generated DBAccess output を使う方向で揃える。
- 将来 Mtool 自身の config store access を self-generated DBAccess へ移す場合は、MySQL / MariaDB と SQLite を同じ contract / same test gate で同時に移行する。
- Mtool 側 SQLite support は current scope で 100% 完了扱い。`make mtool-lite-smoke` で lightweight lane 起動、admin / lab health、admin top page、preflight、migrate、MTOOL core seed、backup / restore、restore 後 preflight を確認する。
- ユーザー DB 側の SQLite support は SQLite 単独で完了を急がず、MySQL / MariaDB mainline と SQLite first expansion を揃える dialect 共通化の中で完成度を上げる。PostgreSQL / SQL Server は parked / post-must-features とし、当面は実装しない。
- ユーザー DB 側の後続計画は [User DB Multi-DB Dialect Roadmap](2026-0617-user-db-multidb-dialect-roadmap.md) を正本にする。
- ユーザー DB 側の複雑系は、AI-assisted review で Mtool 生成 / 継承先個別実装 / manual runtime を切り分ける。

## Layer Inventory

| Layer | MySQL / MariaDB Current | SQLite Current | Parity Judgment | Notes |
| --- | --- | --- | --- | --- |
| Mtool admin config store | PDO repository layer | PDO repository layer | aligned | 現行 mainline は両方 PDO repository。SQLite だけの一時 layer ではない。 |
| Config schema bootstrap | MariaDB initdb SQL | MariaDB initdb SQL を SQLite 用に変換して bootstrap | aligned for current scope | empty / missing SQLite store から current schema を自動作成できる。 |
| MTOOL core seed | MariaDB config-seed | SQLite seed conversion path | aligned for current scope | `make mtool-lite-smoke` で 18 seed files / 195 statements 適用済み。 |
| Config preflight | dialect helper 経由 | dialect helper 経由 | aligned | table / column existence、server version、database name lookup を dialect-aware に寄せた。 |
| Config backup / restore | dump / manifest / rotation | SQLite backup / restore / rotation | aligned | 実装方式は異なるが、ユーザー向け capability は揃える。 |
| Tutorial config store lane | default MySQL / MariaDB lane | SQLite config store lane for sample01-17 | aligned | sample01-17 は dual-profile gate と artifact parity capture 対象。今後 sample 追加時も dual-profile gate を維持する。 |
| Canonical DBAccess output | generated DBAccess + mysqli/default adapter | generated DBAccess + PDO SQLite adapter | first-slice aligned | `_support/mtool_runtime_db.php` が legacy `$mtooldb` surface を維持する。 |
| Bootstrap-generated runtime dbclasses | generated runtime dbclasses | generated runtime dbclasses with same support | first-slice aligned | sample16 proxy bundle support smoke 済み。 |
| Proxy / OpenAPI runtime execution | generated bundle path + sample13/sample16 HTTP smoke + sample13 browser Try It Out smoke | generated bundle path + sample13/sample16 HTTP smoke + sample13 browser Try It Out smoke | first-slice aligned | Authenticated proxy route、Swagger viewer、OpenAPI referenced proxy route、Swagger Try It Out は両 profile で smoke 済み。 |
| User DB schema introspection | MySQL / MariaDB mainline | SQLite source introspection first slice | partial | config store driver とは別に source DB driver として扱う。 |
| User DB output dialect expansion | mainline | SQLite first expansion | partial | PostgreSQL / SQL Server は parked / post-must-features。 |
| Complex DBAccess behavior | inherited/custom class possible | inherited/custom class possible | policy aligned | blob / file / vendor-specific SQL などは AI review で切り分ける。 |

## Current Access Layer Policy

### Config Store

Current mainline:

- MySQL / MariaDB config store: PDO repository layer。
- SQLite config store: PDO repository layer。

この状態は意図的に揃える。SQLite profile だけを generated DBAccess にする、または MySQL / MariaDB profile だけを generated DBAccess にする恒久状態は作らない。

将来の self-hosted mainline:

- config store access を generated DBAccess へ移す場合は、MySQL / MariaDB と SQLite を同時に移行する。
- 同じ repository contract、同じ generated method contract、同じ test gate を使う。
- 移行中も、profile ごとに異なる恒久 layer が残らないようにする。

### Runtime / Proxy Bundle

Runtime / proxy bundle は generated output を使う層として扱う。

- MySQL / MariaDB runtime は既存の default behavior を維持する。
- SQLite runtime は `MTOOL_RUNTIME_DB_DSN=sqlite:/path/app.sqlite` または `MTOOL_RUNTIME_SQLITE_PATH=/path/app.sqlite` を使う。
- runtime support は legacy `$mtooldb` surface を維持しつつ、SQLite では PDO-backed adapter を使う。

## MySQL / MariaDB Checklist

現時点で MySQL / MariaDB 側も見ておくべき項目:

- admin config store が current PDO repository layer で green であること。
- SQLite dialect helper 導入後も MySQL / MariaDB の SQL expression が従来通りであること。
- generated DBAccess の prepared statement 化が MySQL / MariaDB behavior を壊していないこと。
- sample pack runtime test の default lane が引き続き green であること。
- backup / restore / rotation が SQLite 追加後も server DB profile で維持されていること。
- source DB import が config store driver と混線しないこと。

## SQLite Checklist

SQLite 側で見ておくべき項目:

- `APP_CONFIG_STORE_DIR` だけで SQLite config store を選べること。
- empty `config.sqlite` が bootstrap されること。
- selected repository CRUD smoke が SQLite config store で通ること。
- SQLite config store lane の sample runtime tests が通ること。
- SQLite backup / restore / rotation が使えること。
- generated DBAccess が SQLite DSN で common CRUD / SELECT を実行できること。
- proxy / OpenAPI runtime bundle で HTTP-level smoke と browser Try It Out smoke を維持すること。

## 2026-06-17 Resumption Check

計画再開前に、MySQL / MariaDB default lane と SQLite representative lane を再確認した。

Verification:

| Check | Layer | Result | Notes |
| --- | --- | --- | --- |
| `make test` | MySQL / MariaDB default config store + integration suite | OK | `174 tests, 7119 assertions` |
| `make sample13-pack-runtime-test-sqlite` | SQLite config store + OpenAPI API surface | OK | `1 test, 13 assertions`; metadata-backed OpenAPI build-plan payload remains aligned |
| `make sample16-pack-runtime-test-sqlite` | SQLite config store + authenticated proxy generated runtime bundle | OK | `1 test, 35 assertions` |
| `make sample17-pack-runtime-test-sqlite` | SQLite config store + multi-output capstone | OK | `1 test, 7 assertions` |
| `make sample13-http-runtime-smoke` | MySQL / MariaDB config store + Swagger viewer/proxy HTTP routes | OK | published OpenAPI spec, 2 operations, db source selector, referenced proxy `200 OK` |
| `make sample13-http-runtime-smoke-sqlite` | SQLite config store + Swagger viewer/proxy HTTP routes | OK | same viewer + proxy contract |
| `make sample13-browser-try-it-out-smoke` | MySQL / MariaDB config store + Swagger viewer browser Try It Out | OK | `ApiTask.GetApiTask` returns `HTTP 200 OK`, `_status=OK`, `Result.Title=Expose list endpoint` |
| `make sample13-browser-try-it-out-smoke-sqlite` | SQLite config store + Swagger viewer browser Try It Out | OK | same browser fetch / payload contract |
| `make sample16-http-runtime-smoke` | MySQL / MariaDB config store + authenticated proxy HTTP route | OK | missing/wrong token fail closed, matching token `200 OK` |
| `make sample16-http-runtime-smoke-sqlite` | SQLite config store + authenticated proxy HTTP route | OK | same generated adapter path and payload contract |

Note:

- `sample16-pack-runtime-test-sqlite` は sandbox 内では Docker buildx が `~/.docker` に書けず一度失敗した。権限付き再実行では green。
- `sample17-pack-runtime-test-sqlite` も権限付き実行で green。
- これにより、現時点の再開基準としては default MySQL / MariaDB lane と SQLite representative lane の両方が通っている。
- HTTP-level authenticated proxy smoke、Swagger viewer smoke、OpenAPI referenced proxy route smoke、Swagger viewer browser-side Try It Out smoke は MySQL / MariaDB と SQLite の両方で実装済み。

## 2026-06-17 HTTP Runtime Smoke Finding

sample16 HTTP route smoke の実装中に、generated proxy bundle 側の autoload ordering 問題を検出した。

Finding:

- `autoload_proxy_runtime.php` が legacy mysqli-only `connect_mtooldb_if_not_yet()` を先に定義していた。
- `_support/mtool_runtime_db.php` の generated adapter は `function_exists` guard を持つため、HTTP route では adapter 側の `execute($sql, $params)` が有効化されなかった。
- その結果、MySQL lane の HTTP success case は `Call to undefined method mysqli::execute()` で 500 になっていた。

Resolution:

- proxy bundle autoload は `_support/mtool_runtime_db.php` を require し、接続関数は generated adapter に一本化する。
- `lab_published_single_proxy_page.php` は requested DB source から `MTOOL_RUNTIME_DB_*` / `MTOOL_RUNTIME_DB_DSN` を設定し、published generated runtime に渡す。
- MySQL / MariaDB と SQLite の両 lane で sample16 published proxy route が同じ generated adapter surface を使うことを HTTP smoke で確認した。

## Sample Gate Matrix

Makefile 上の tutorial sample gate は次の状態。

| Sample | Default MySQL / MariaDB Gate | SQLite Config Store Gate | Status |
| --- | --- | --- | --- |
| sample01 simple table runtime | `sample01-pack-runtime-test` / `sample1-output-test` | `sample01-pack-runtime-test-sqlite` | dual gate |
| sample02 dataclass nullable default status | `sample02-pack-runtime-test` / `sample2-output-test` | `sample02-pack-runtime-test-sqlite` | dual gate |
| sample03 dataclass lookup and helper | `sample03-pack-runtime-test` / `sample3-output-test` | `sample03-pack-runtime-test-sqlite` | dual gate |
| sample04 dataclass parent child basic | `sample04-pack-runtime-test` / `sample4-output-test` | `sample04-pack-runtime-test-sqlite` | dual gate |
| sample05 DBAccess select basic | `sample05-pack-runtime-test` / `sample5-output-test` | `sample05-pack-runtime-test-sqlite` | dual gate |
| sample06 DBAccess filter sort page | `sample06-pack-runtime-test` / `sample6-output-test` | `sample06-pack-runtime-test-sqlite` | dual gate |
| sample07 DBAccess CRUD basic | `sample07-pack-runtime-test` / `sample7-output-test` | `sample07-pack-runtime-test-sqlite` | dual gate |
| sample08 DBAccess join read model | `sample08-pack-runtime-test` / `sample8-output-test` | `sample08-pack-runtime-test-sqlite` | dual gate |
| sample09 DBAccess aggregate report | `sample09-pack-runtime-test` / `sample09-runtime-output-test` | `sample09-pack-runtime-test-sqlite` | dual gate |
| sample10 DBAccess mini CRUD flow | `sample10-pack-runtime-test` / `sample10-runtime-output-test` | `sample10-pack-runtime-test-sqlite` | dual gate |
| sample11 HTML template output | `sample11-pack-runtime-test` / `sample11-runtime-output-test` | `sample11-pack-runtime-test-sqlite` | dual gate |
| sample12 external DB source import | `sample12-pack-runtime-test` / `sample12-runtime-output-test` | `sample12-pack-runtime-test-sqlite` | dual gate |
| sample13 OpenAPI API surface | `sample13-pack-runtime-test` / `sample13-runtime-output-test` / `sample13-http-runtime-smoke` / `sample13-browser-try-it-out-smoke` | `sample13-pack-runtime-test-sqlite` / `sample13-http-runtime-smoke-sqlite` / `sample13-browser-try-it-out-smoke-sqlite` | dual gate + Swagger viewer/proxy HTTP smoke + browser Try It Out smoke |
| sample14 custom proxy runtime | `sample14-pack-runtime-test` / `sample14-runtime-output-test` | `sample14-pack-runtime-test-sqlite` | dual gate |
| sample15 project metadata export/import | `sample15-pack-runtime-test` / `sample15-runtime-output-test` | `sample15-pack-runtime-test-sqlite` | dual gate |
| sample16 authenticated proxy | `sample16-pack-runtime-test` / `sample16-runtime-output-test` / `sample16-http-runtime-smoke` | `sample16-pack-runtime-test-sqlite` / `sample16-http-runtime-smoke-sqlite` | dual gate + HTTP route smoke |
| sample17 multi-output project | `sample17-pack-runtime-test` / `sample17-runtime-output-test` | `sample17-pack-runtime-test-sqlite` | dual gate |

Policy:

- current capstone lane is dual-gated through sample17.
- new tutorial samples should add default MySQL / MariaDB and SQLite config store gates together.
- sample01-sample17 are dual-gated; add future tutorial samples to both lanes and artifact parity together.

## Artifact Parity Framework Plan

Current sample gates compare each lane's generated output against the same reference tree. This gives useful indirect parity: if the default MySQL / MariaDB config store lane and the SQLite config store lane both match the same reference, the artifacts should be equivalent.

The first framework slice now makes this explicit by capturing both lanes first, then comparing their artifacts directly.

Implemented:

- `make artifact-parity-capture-mysql`
- `make artifact-parity-capture-sqlite`
- `make artifact-parity-compare`
- `make artifact-parity-test`
- Targets: `sample01-simple-table-runtime` through `sample17-multi-output-project`.
- Verification: `make artifact-parity-test ARTIFACT_PARITY_RUN_ID=codex-smoke` passed with `artifact parity OK` across 26 captured files.
- Expanded verification: `make artifact-parity-test ARTIFACT_PARITY_RUN_ID=codex-expanded2` ran both lane captures for sample10-17 and passed with `artifact parity OK` across 549 captured files. During implementation, the first strict compare exposed 3 normalized JSON differences: sample14 build-plan volatile step IDs/timestamps and sample15 metadata bundle dialect type names. The parity normalizer now follows the existing sample14/sample15 semantic comparison policy.
- Full tutorial verification: `make artifact-parity-test ARTIFACT_PARITY_RUN_ID=codex-all-samples` ran both lane captures for sample01-17 and passed with `artifact parity OK` across 597 captured files.

Scope:

- This framework is for config-store parity first: MySQL / MariaDB config store vs SQLite config store should produce the same generated output / artifact.
- It is not the same as user DB dialect parity. MySQL-target output and SQLite-target output may intentionally differ in SQL or runtime adapter details and should use dialect-aware contract comparison later.
- User DB dialect parity is tracked separately in [User DB Multi-DB Dialect Roadmap](2026-0617-user-db-multidb-dialect-roadmap.md).

Proposed flow:

1. Run the default MySQL / MariaDB config store lane for selected samples in one batch.
2. Copy each generated / published output into `work/artifact-parity/<run-id>/mysql/<sample>/<source-output>/`.
3. Run the SQLite config store lane for the same selected samples in one batch.
4. Copy each generated / published output into `work/artifact-parity/<run-id>/sqlite/<sample>/<source-output>/`.
5. Generate lane manifests with sample key, source output key, file path, size, sha256, and optional normalized digest.
6. Compare MySQL / MariaDB and SQLite manifests after both lane batches complete.

Comparison policy:

- Default rule: byte-for-byte equality for generated output files.
- JSON build-plan / manifest-like files may use normalized comparison when they contain intentionally volatile fields such as timestamps, artifact keys, or generated catalog counts.
- Any intentional difference must be listed in an explicit allowlist with a reason and owner.
- A parity failure should report sample key, source output key, relative file path, comparison mode, expected digest, actual digest, and short diff context where possible.

Make targets:

- `make artifact-parity-capture-mysql`
- `make artifact-parity-capture-sqlite`
- `make artifact-parity-compare`
- `make artifact-parity-test`

Current target samples:

- `sample01-simple-table-runtime` through `sample17-multi-output-project`

Migration policy:

- Keep existing per-sample reference checks; they remain useful local regression tests.
- Add the parity framework beside them, not as an immediate replacement.
- Keep all tutorial samples in artifact parity.
- New tutorial samples should define both lane gates and parity capture metadata from the start.
- Before expanding more feature work, expand this framework so later dual-profile work has a common verification shape.

## User DB Dialect Contract Compare Plan

User DB dialect comparison should not require raw output identity. MySQL / MariaDB output and SQLite output may differ in SQL syntax, type names, placeholder style, adapter implementation, and DDL details. The comparison target is the normalized contract.

Compare dimensions:

- generated class / method surface
- parameter names, types, nullability, and default handling
- result shape, field names, and nullable handling
- CRUD behavior
- SELECT / join / aggregate / pagination behavior
- placeholder style and bind ordering
- generated OpenAPI schema / examples where relevant
- runtime execution result against fixture DB
- proxy / runtime route status code and payload contract

Capture shape:

- Store raw outputs per dialect lane, for example `work/user-db-contract/<run-id>/mysql/<sample>/` and `work/user-db-contract/<run-id>/sqlite/<sample>/`.
- Generate a normalized contract manifest per lane.
- Compare normalized manifests first.
- Keep dialect-specific raw diffs as diagnostic material, not as the primary pass/fail signal.

Policy:

- Existing artifact parity remains the exact-output gate for config store parity.
- User DB dialect compare is semantic / contract-based.
- Intentional dialect differences require an allowlist entry with a reason.
- AI-assisted generation review should classify each generated unit as `generated`, `generated_with_options`, `inherited_custom`, `manual_runtime`, or `needs_design_review`.

First slice:

- `make user-db-contract-test USER_DB_CONTRACT_RUN_ID=codex-sample10` captures sample10 DBACCESS-PHP output from MySQL / MariaDB lane and SQLite config store lane, then compares normalized user DB contract manifests.
- The first manifest covers class / method surface, action type, parameters, normalized SQL, bind ordering, result field shape, cardinality, and runtime CRUD behavior.
- Runtime execution runs generated DBAccess against a MySQL / MariaDB `db-lab` fixture and a file-backed SQLite fixture.
- Verification passed with `user DB contract OK`.

Expanded samples:

- `sample06-dbaccess-filter-sort-page`: filter / sort / pagination contract compare passed for MySQL / MariaDB and SQLite.
- `sample08-dbaccess-join-read-model`: join read model contract compare passed for MySQL / MariaDB and SQLite.
- `sample09-dbaccess-aggregate-report`: aggregate report contract compare passed for MySQL / MariaDB and SQLite.
- Current user DB contract stop-line is complete. Return to must-have feature work next.

Close status on 2026-06-18:

- `sample09-dbaccess-aggregate-report` is implemented in the user DB contract framework.
- Verification passed with `user DB contract OK`.
- Resume from [User DB Multi-DB Dialect Roadmap](2026-0617-user-db-multidb-dialect-roadmap.md), section `Completion Handoff`.
- Stop the DB-server expansion line and return to must-have feature work. PostgreSQL / SQL Server remain parked.

## AI-Assisted Generation Review

ユーザー DB 側では、Mtool が生成すべきか、継承先 class で個別実装すべきか、manual runtime に委ねるべきかを AI-assisted review で判定する。

分類:

- `generated`
- `generated_with_options`
- `inherited_custom`
- `manual_runtime`
- `needs_design_review`

この review は MySQL / MariaDB と SQLite の両方を見る。SQLite だけで生成可能、または MySQL / MariaDB だけで生成可能な場合は、dialect-specific note と fallback implementation を明示する。

First applied review:

- [AI Generation Review: sample13 / sample16](2026-0617-ai-generation-review-sample13-sample16.md)
  - sample13 `ApiTask.GetApiTask`: `generated`
  - sample13 `ApiTask.GetApiTaskList`: `generated_with_options`
  - sample13 `OPENAPI-JSON`: `generated_with_options`
  - sample16 `AuthTask.GetAuthTask`: `generated`
  - sample16 ProjectToken auth: `generated`
  - sample16 custom authorization hook: `inherited_custom`

## Risk

最大のリスクは、SQLite 対応のために access layer が profile ごとに分岐し、MySQL / MariaDB と SQLite の保守形がずれることである。

避けるべき状態:

- SQLite config store だけ generated DBAccess、MySQL / MariaDB config store だけ PDO repository。
- MySQL / MariaDB config store だけ generated DBAccess、SQLite config store だけ PDO repository。
- runtime bundle では generated output を使うが、test gate が片方の dialect だけに偏る。
- AI-assisted review が SQLite 可否だけを見て、MySQL / MariaDB 側の影響を見ない。

## Next Actions

1. Mtool 側 SQLite support は current scope で 100% 完了扱い。`make mtool-lite-smoke` を regression gate として維持する。
2. Existing reference compare は維持し、sample01-17 の artifact parity compare を共通 regression gate として使う。
3. 今後追加される sample は、MySQL / MariaDB gate、SQLite config store gate、artifact parity capture metadata を同時に追加する。
4. generated DBAccess prepared statement 化の MySQL / MariaDB regression を継続して sample gates に残す。
5. ユーザー DB 側の SQLite support は、SQLite 単独の完了タスクではなく [User DB Multi-DB Dialect Roadmap](2026-0617-user-db-multidb-dialect-roadmap.md) として進める。dialect helper、schema introspection、DBAccess class output、dialect-aware contract comparison を共通化する。
6. AI-assisted generation review artifact を今後の user DB samples に適用する。First pass: [AI Generation Review: sample13 / sample16](2026-0617-ai-generation-review-sample13-sample16.md). Contract: [AI Generation Review](../../internal/ai-generation-review.md).
7. User DB contract manifest に runtime execution result を足す。first slice done for sample10.
8. sample10 専用 runtime smoke を、sample definition / fixture definition / expected result definition に分ける最小 generalization を行う。done.
9. User DB contract compare を `sample06` filter / sort / pagination、`sample08` join read model、`sample09` aggregate report へ広げる。sample06/sample08/sample09 done.
10. 上記代表 sample まで完了したので、PostgreSQL / SQL Server へ進まず、must-have feature work へ戻る。
11. OpenAPI examples の typed scalar 改善と DBAccess metadata-backed scalar typing first slice は完了。次は richer parameter metadata が必要な operation を棚卸しする。
12. MySQL / MariaDB config store と SQLite config store の portability 設計を進める。特に sample15 metadata bundle は semantic parity では揃っているが、raw table/dataclass type names は dialect 由来の差分として残る。
13. auth-required OpenAPI operation など、browser Try It Out smoke の横展開候補を整理する。Design: [HTTP Runtime Smoke Plan](2026-0617-http-runtime-smoke-plan.md).
