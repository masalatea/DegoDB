# 2026-06-16 Sample11 HTML Template Output Plan

## Status

- status: `SAMPLE11 THROUGH SAMPLE17 DONE`
- target lane: `sample/tutorials/`
- target pack: `sample11-html-template-output`, `sample12-external-db-source-import`, `sample13-openapi-api-surface`, `sample14-custom-proxy-runtime`, `sample15-project-metadata-export-import`, `sample16-authenticated-proxy`, `sample17-multi-output-project`
- scope decision:
  - `LanguageResource` / i18n tutorial は tool scope から外れたため追加しない。
  - DBAccess tutorial は `sample05` から `sample10` で一度完結しているため、`sample11` は別種の Source Output tutorial として始める。
- implementation note:
  - `sample11-html-template-output` は `html-module-catalog` strategy の最小 runtime pack として追加した。
  - actual output は `work/source-outputs/SAMPLE11/HTML-PAGE/` から `sample/tutorials/sample11-html-template-output/reference/HTML-PAGE/` へコピーした。
  - focused verification: `make sample11-pack-runtime-test` -> `OK (1 test, 6 assertions)`
  - `sample12-external-db-source-import` は external named source import の最小 runtime pack として追加した。
  - actual output は `work/source-outputs/SAMPLE12/DATACLASS-PHP/` から `sample/tutorials/sample12-external-db-source-import/reference/DATACLASS-PHP/` へコピーした。
  - focused verification: `make sample12-pack-runtime-test` -> `OK (1 test, 8 assertions)`
  - `sample13-openapi-api-surface` は OpenAPI API surface の最小 runtime pack として追加した。
  - actual output は `work/source-outputs/SAMPLE13/OPENAPI-JSON/` から `sample/tutorials/sample13-openapi-api-surface/reference/OPENAPI-JSON/` へコピーした。
  - focused verification: `make sample13-pack-runtime-test` -> `OK (1 test, 13 assertions)`
  - `sample14-custom-proxy-runtime` は custom proxy metadata から PHP proxy server artifact を publish する最小 runtime pack として追加した。
  - actual output は `work/source-outputs/SAMPLE14/CUSTOM-PROXY-SERVER/` から selected reference files として `sample/tutorials/sample14-custom-proxy-runtime/reference/CUSTOM-PROXY-SERVER/` へコピーした。
  - focused verification: `make sample14-pack-runtime-test` -> `OK (1 test, 16 assertions)`
  - `sample15-project-metadata-export-import` は project-core metadata bundle の export / preview / apply を確認する最小 runtime pack として追加した。
  - actual bundle は runtime export から `sample/tutorials/sample15-project-metadata-export-import/reference/PROJECT-METADATA-BUNDLE/` へコピーした。
  - focused verification: `make sample15-pack-runtime-test` -> `OK (1 test, 8 assertions)`
  - `sample16-authenticated-proxy` は ProjectToken authenticated generated proxy と fail-closed auth behavior を確認する最小 runtime pack として追加した。
  - actual output は `work/source-outputs/SAMPLE16/AUTH-PROXY-SERVER/` から `sample/tutorials/sample16-authenticated-proxy/reference/AUTH-PROXY-SERVER/` へコピーした。
  - focused verification: `make sample16-pack-runtime-test` -> `OK (1 test, 12 assertions)`
  - `sample17-multi-output-project` は 1 project から複数 Source Output を publish する final capstone として追加した。
  - actual outputs は `work/source-outputs/SAMPLE17/` から `sample/tutorials/sample17-multi-output-project/reference/` へコピーした。
  - focused verification: `make sample17-pack-runtime-test` -> `OK (1 test, 7 assertions)`
  - suite verification after sample16: `make test` -> `OK (143 tests, 6771 assertions)`
  - suite verification after sample17: `make test` -> `OK (144 tests, 6808 assertions)`

## 結論

- 次に増やす user-facing sample は `sample11-html-template-output` とする。
- 目的は、DB schema / DataClass / DBAccess の tutorial lane の後に、HTML template 系 Source Output を最小 runtime pack として読めるようにすること。
- 既存の `sample/internal-patterns/pattern11-top-level-declaration-html-template` は migration / generator guard であり、runtime tutorial ではない。したがって `sample11` とは役割が重複しない。
- `sample11` は first slice では HTML template の metadata と generated output の理解に絞る。proxy runtime、OpenAPI、custom proxy、LanguageResource は入れない。

## 背景

- current tutorial lane は `sample01` から `sample10` までで、主に次を扱っている。
  - table import
  - Data Class sync
  - DB Access select / filter / sort / page / CRUD / join / aggregate
  - Source Output publish for `DATACLASS-PHP` and `DBACCESS-PHP`
- 一方で、HTML template / HTML source output は current tutorial lane ではまだ主題になっていない。
- internal pattern lane には `htmlTemplate` の top-level declaration guard があるが、これは legacy fixture から generated Data Class の複雑形を固定するための file-based sample である。
- user-facing に必要なのは、runtime pack として fresh config DB へ seed し、admin / generator / reference output の関係を辿れる小さい tutorial である。

## Non-Goals

- `LanguageResource` / i18n sample は作らない。
- legacy `original-codes/` を runtime input にしない。
- `sample56-runtime-misc-proxy` の縮小コピーにはしない。
- HTML authoring UI 全体の再設計はしない。
- proxy server / proxy client generation は `sample12+` の別候補に残し、`sample11` には混ぜない。
- imitation output を `reference/` に置かない。reference は actual generated output のみとする。

## Target Pack

| item | value |
| --- | --- |
| category | `sample/tutorials/` |
| pack name | `sample11-html-template-output` |
| project key | `SAMPLE11` |
| structure | runtime pack |
| main output | HTML template related Source Output |
| secondary output | 必要最小限の `DATACLASS-PHP` |
| canonical target | `make sample11-pack-runtime-test` |

## Sample Design

### 主テーマ

- HTML template metadata を seed し、Source Output publish で actual HTML-related output を生成する。
- `README.md` だけで次が分かる状態にする。
  - どの project / table / source output を seed するか
  - HTML template がどの Source Output に紐づくか
  - どこに generated output が出るか
  - reference output がどの actual artifact 由来か

### 最小構成

- project:
  - `ProjectKey = SAMPLE11`
  - tutorial 用の isolated runtime project
- table:
  - HTML rendering の説明に必要な最小 table を 1 つ置く
  - 候補: `PageContent`
    - `Id`
    - `Title`
    - `Slug`
    - `Body`
    - `UpdatedAt`
- source output:
  - HTML template tutorial 用の source output を 1 つ
  - 必要なら supporting `DATACLASS-PHP` を 1 つ
- template:
  - HTML file 1 枚または最小 template set
  - generated output の差分確認がしやすいよう、conditional / loop / proxy には踏み込まない

## 既存 sample との差別化

| existing pack | 役割 | `sample11` との差 |
| --- | --- | --- |
| `sample01-simple-table-runtime` | first end-to-end | DataClass / DBAccess が主題で、HTML template は扱わない |
| `sample05` - `sample10` | DBAccess tutorial | select / write metadata が主題で、HTML Source Output は扱わない |
| `pattern11-top-level-declaration-html-template` | file-based migration guard | runtime pack ではなく、user-facing tutorial でもない |
| `sample56-runtime-misc-proxy` | legacy representative project | misc / proxy mix の代表 pack で、HTML template tutorial ではない |

## 実装順

1. `docs/sample-tutorial-roadmap.md` に `sample11-html-template-output` を planned / current candidate として追記する。
2. `sample/tutorials/sample11-html-template-output/` を runtime pack として作る。
3. seed を追加する。
   - `900_010_sample11_project_seed.sql`
   - `900_020_sample11_html_template_seed.sql`
   - `900_030_sample11_source_output_seed.sql`
   - `900_040_sample11_html_definition_seed.sql`
4. `compose.yaml` と `run.sh` を既存 tutorial pack の runner pattern に合わせる。
5. actual output を publish し、`reference/` へコピーする。
6. checker / PHPUnit / Make target を追加する。
   - `mtool/scripts/check_sample11_html_template_output_outputs.php`
   - `mtool/scripts/lib/sample11_html_template_output_check.php`
   - `tests/Integration/Sample11HtmlTemplateOutputTest.php`
   - `make sample11-pack-runtime-test`
7. catalog / README / test docs を更新する。
   - `mtool/app/sample_pack_catalog.php`
   - `sample/README.md`
   - `sample/tutorials/README.md`
   - `tests/README.md`
   - `tests/Integration/README.md`
8. focused runtime test を通す。
9. suite time を見て `make test` へ入れるか判断する。

## Acceptance Criteria

- `sample/tutorials/sample11-html-template-output/README.md` が単体で tutorial として読める。
- `run.sh up`、`run.sh apply-seed`、`run.sh down` が既存 tutorial pack と同じ操作感で動く。
- `reference/` は actual generated output のみを含む。
- `make sample11-pack-runtime-test` が fresh runtime で通る。
- `SamplePackCatalogTest` が `sample11` を current tutorial pack として固定する。
- `docs/sample-tutorial-roadmap.md` で `sample11` の役割と、`LanguageResource` を追加しない判断が読める。

## 次の候補

`sample11` 以降は、DBAccess の細分化を増やすより、一般ユーザーが実運用で迷いやすい周辺 flow を tutorial 化する。

| order | pack | 主テーマ | 追加理由 |
| --- | --- | --- | --- |
| 1 | `sample11-html-template-output` | HTML template / HTML Source Output | DBAccess lane 後の最初の non-DBAccess Source Output tutorial |
| 2 | `sample12-external-db-source-import` | external DB source import | ユーザーが自分の DB をつなぎ、table import -> sync -> output publish へ進む最初の実用導線。追加済み |
| 3 | `sample13-openapi-api-surface` | OpenAPI / Swagger / API surface | `sample10` の CRUD flow を外部から確認する導線。追加済み |
| 4 | `sample14-custom-proxy-runtime` | custom proxy runtime | proxy metadata / custom proxy を小さく学ぶ tutorial。`sample56` の legacy representative pack とは分ける。追加済み |
| 5 | `sample15-project-metadata-export-import` | project metadata bundle export / import | 他の contributor や AI が設計 metadata を移して再現する流れを固定する。追加済み |
| 6 | `sample16-authenticated-proxy` | generated proxy auth | token / auth / fail-closed 境界を tutorial として確認する。追加済み |
| 7 | `sample17-multi-output-project` | multi-output capstone | 1 project で `DATACLASS-PHP`、`DBACCESS-PHP`、HTML、OpenAPI など複数 Source Output を扱う総合 sample。追加済み |

### sample12: external DB source import

- 目的:
  - external named DB source を登録し、既存 DB から table import -> DataClass sync -> Source Output publish まで進む導線を固定する。
- 既存との差別化:
  - `sample01` から `sample10` は pack 内 seed で config DB / sample schema を作る。
  - `sample12` は「外部 DB を使う利用開始 flow」を主題にする。
- first slice:
  - 小さい external DB schema を sample pack 側で用意する。
  - import 対象 table は 1-2 個に抑える。
  - generated output は actual reference のみ保存する。
- implementation note:
  - `database_sources.source_key=sample12_lab` と `ExternalArticle` を使い、`named-live-schema:sample12_lab` から import する flow にした。
  - focused runtime と full suite の両方で再現できるよう、checker は実行中スタックの `lab_db` 接続情報へ sample source を揃え、fixture table を idempotent に準備する。

### sample13: OpenAPI / API surface

- 目的:
  - generated output を OpenAPI / Swagger surface で確認する。
  - `sample10` の CRUD flow を「生成して終わり」ではなく、外から叩くところまでつなげる。
- 既存との差別化:
  - `sample10` は DBAccess metadata と generated PHP output が主題。
  - `sample13` は viewer / OpenAPI / API surface の確認が主題。
- first slice:
  - list / detail / create / update / delete のうち、最小 CRUD cycle を 1 つに絞る。
  - browser smoke が必要なら、manual step ではなく testable command へ寄せる。
- implementation note:
  - `ApiTask.GetApiTaskList` / `GetApiTask` を `OPENAPI-JSON` の single-function proxy target にし、`openapi-json` strategy で `openapi.json` / `build-plan.json` を publish する flow にした。
  - actual proxy runtime execution は sample14+ へ分け、sample13 は OpenAPI artifact と authenticated Swagger viewer の入口に絞った。

### sample14: custom proxy runtime

- 目的:
  - proxy metadata / custom proxy を学習用に小さく切り出す。
- 既存との差別化:
  - `sample56-runtime-misc-proxy` は legacy `Project.PID = 16` 由来の representative pack。
  - `sample14` は tutorial として最小 metadata だけを扱う。
- first slice:
  - proxy server / proxy client の両方を詰め込まず、custom proxy runtime の最小成功 path に絞る。
- implementation note:
  - `CATALOG-SUMMARY` custom proxy を seed し、steps は `dbtable.GetdbtableList` と `ProjectSourceOutput.GetProjectSourceOutputList` に絞った。
  - output は `CUSTOM-PROXY-SERVER` とし、custom proxy build plan と generated handler / wrapper の selected actual files を reference として固定した。
  - full generated bundle は大きいため、checker は key files を比較し、`build-plan.json` は volatile fields を正規化して比較する。

### sample15: project metadata export / import

- 目的:
  - project metadata bundle の export / import を、他の contributor や AI が再現できる tutorial にする。
- 既存との差別化:
  - runtime pack の generated output ではなく、設計 metadata の移動と再現性を主題にする。
- first slice:
  - `SAMPLE15` を export し、fresh runtime へ import して同じ output を publish できることを確認する。
- implementation note:
  - `BundleNote` table を import / sync し、`project-core` scope の bundle として export する flow にした。
  - import は同じ project への `preview -> apply` に絞り、reference bundle と runtime export の一致、復元後 metadata 件数を検証する。
  - 別 project key への import は bundle 内 slug uniqueness / rename policy を含むため、first slice では扱わない。

### sample16: authenticated proxy

- 目的:
  - generated proxy の token / auth / fail-closed 挙動を tutorial として固定する。
- 既存との差別化:
  - security hardening の report はあるが、user-facing sample としてはまだまとまっていない。
- first slice:
  - success case と missing / wrong token case を最小限で検証する。
- implementation note:
  - `AuthTask.GetAuthTask` を `AUTH-PROXY-SERVER` の `single-function-proxy` target にし、`single-proxy-server` strategy で generated proxy server artifact を publish する flow にした。
  - auth type は `ProjectToken` に固定し、missing `TOKEN`、empty `TOKEN`、missing `MTOOL_PROXY_PROJECT_TOKEN`、wrong token が fail-closed になり、matching token のみ通ることを checker で検証する。
  - HTTP server 起動には踏み込まず、generated handler の `authorizeRequest` 境界を actual artifact から直接ロードして検証する。

### sample17: multi-output project capstone

- 目的:
  - 1 project で複数 Source Output を扱う実運用に近い capstone を作る。
- 既存との差別化:
  - `sample10` は DBAccess CRUD capstone。
  - `sample17` は project-level multi-output capstone。
- first slice:
  - `DATACLASS-PHP`
  - `DBACCESS-PHP`
  - HTML template output
  - OpenAPI / API surface
  - 上記を同じ project key で生成し、reference provenance を明確にする。
- implementation note:
  - `CapstoneTask` table と `GetCapstoneTaskList` / `GetCapstoneTask` の 2 function を使う `SAMPLE17` project にした。
  - `DATACLASS-PHP`、`DBACCESS-PHP`、`HTML-PAGE`、`OPENAPI-JSON` を同じ project から publish し、4 つの actual reference tree を比較する checker を追加した。
  - Project metadata bundle と ProjectToken auth はそれぞれ `sample15` / `sample16` へ分け、sample17 は multi-output publish に絞った。

`LanguageResource` / i18n はこの候補列へ戻さない。
