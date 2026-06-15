# Design Data Persistence Report

Date: 2026-06-16

## Purpose

一般ユーザーが DegoDB を使って作成・編集する「設計データ」を、どこに、どの粒度で、どのように永続化するべきかを整理する。

ここでの設計データとは、既存 DB の実データではなく、DegoDB が読み取り・編集・生成に使う project metadata、table definition、data class definition、DB access definition、source output definition などを指す。

## Executive Summary

設計データの正本は `config_db` に置くべきである。

DegoDB の一般ユーザーにとって重要なのは、生成されたファイルそのものではなく、「どの DB を読み、どの table / data class / DB access / output を設計したか」という再生成可能な設計状態である。この状態は `config_db` に canonical metadata として保存し、生成 output はその結果として扱う。

したがって、永続化の基本方針は次の通り。

- `config_db` を設計データの canonical store とする。
- `work/` や `output/` の生成物は設計データの正本にしない。
- 既存 DB / 外部 DB の実データは DegoDB の設計データとは分ける。
- project metadata bundle は設計データの移動・保全・レビュー用 snapshot として使う。
- secrets は設計データに混ぜず、別管理する。

## User Persistence Policy

一般ユーザー向けの方針は、「試用」と「継続利用」を明確に分ける。

### Default Recommendation

継続して使うユーザーには、local Docker volume だけに設計データを置かせない。

推奨 default:

1. `config_db` は durable な保存先に置く。
   - local 利用なら、少なくとも定期 dump を取る。
   - チーム利用・長期利用なら、external config DB を使う。

2. project metadata bundle を節目で export する。
   - 大きな import / apply 前。
   - release 前。
   - 別環境に渡す前。

3. secrets は bundle と Git から分離する。
   - password は `password_env` または組織の secret 管理に置く。
   - bundle には secrets template までを含める。

4. generated output は backup 対象にしない。
   - 必要な成果物だけ artifact bundle として保存する。
   - 設計データの復元は `config_db` backup または project metadata bundle から行う。

### Supported User Modes

| Mode | 対象ユーザー | 設計データ保存先 | 必須運用 |
| --- | --- | --- | --- |
| Trial | 触って試すだけ | local `db-config` Docker volume | 消えてよい前提。必要なら bundle export |
| Personal Durable | 個人で継続利用 | local `db-config` + 定期 DB dump | dump と project metadata bundle を取る |
| Team / Production-like | 複数人・長期利用 | external config DB | DB backup、migration、bundle export、secret 分離 |

### Product Default

製品としては、初回セットアップ時に次の選択肢を出すのが望ましい。

1. Trial local storage
   - Docker volume に保存する。
   - reset / volume delete で失われることを明示する。
   - backup はユーザー責任。

2. Durable local storage
   - local `db-config` を使うが、backup command / export command を案内する。
   - reset 前に dump / bundle export を促す。

3. External config DB
   - `APP_CONFIG_DB_*` を設定して外部 DB に保存する。
   - 長期利用の推奨選択肢とする。
   - 継続利用では `deploy/durable-config-db.env.example` から Git 管理外の `.env.durable` を作り、`make up-durable-config-db DURABLE_ENV_FILE=.env.durable` で起動する。

### Non-goals

次の運用は推奨しない。

- `work/` や `output/` を設計データの backup として扱う。
- generated runtime や OpenAPI JSON だけを保存して、`config_db` を backup しない。
- populated secrets file を Git に入れる。
- Docker volume だけで長期運用する。
- project metadata bundle を完全な環境 backup と説明する。

## What Counts As Design Data

| 種別 | 例 | 永続化先 | 備考 |
| --- | --- | --- | --- |
| Project metadata | project key、project name、membership | `config_db` | プロジェクト単位の基本情報 |
| Table design | dbtable、dbtablecolumns | `config_db` | import 由来でも、DegoDB 側の canonical metadata として扱う |
| Data class design | dataclass、dataclassfields | `config_db` | runtime / output 生成の入力 |
| DB access design | project_db_access、関連 function / target metadata | `config_db` | list / detail / CRUD / proxy などの設計 |
| Source output design | project_source_outputs、output type、visibility、target | `config_db` | OpenAPI / runtime 生成設定 |
| Database source metadata | source_key、driver、host、database name、capability flags | `config_db` | password は含めない |
| Import / publish intent | import preview / apply 後の canonical state | `config_db` | 操作ログではなく結果状態を永続化する |

設計データに含めないもの:

- 外部 DB の実レコード。
- DB password / token / populated secret。
- Playwright や smoke test の実行結果。
- compare 用 scratch。
- 再生成可能な raw output。
- Docker volume や local filesystem の一時状態そのもの。

## Source Of Truth Model

### Canonical Store: `config_db`

`config_db` は DegoDB の設計データを保存する正本である。

一般ユーザーが admin UI や CLI で行う設計操作は、最終的に `config_db` に反映されるべきである。生成処理、viewer、download、lab 検証は、この canonical metadata を入力として扱う。

永続化対象:

- project
- dbtable / dbtablecolumns
- dataclass / dataclassfields
- project_db_access 系 metadata
- project_source_outputs
- database_sources
- source output target metadata

### External DB: Source, Not Design Store

既存 DB や外部 DB は、schema import や runtime read の source であって、DegoDB の設計データ保存先ではない。

外部 DB の schema から設計データを import することはあるが、import 後に DegoDB が使う設計状態は `config_db` 側に保存する。外部 DB の実データ保全は、その DB の backup policy に任せる。

### Generated Output: Result, Not Source

OpenAPI JSON、runtime bundle、proxy code、viewer 用 artifact は、設計データから生成された結果である。

これらは必要に応じて保存・配布できるが、設計データの正本ではない。将来の再生成・差分比較・修正を安全に行うためには、生成物ではなく `config_db` の metadata を保存する必要がある。

## Persistence Modes

### Local Trial Mode

ローカルで試す場合は、local compose の `db-config` で設計データを保存できる。

ただし、これは一般ユーザーにとって消えやすい保存先である。Docker volume を削除すると設計データも失われる可能性がある。

必要な案内:

- `config_db` に設計データが保存されていること。
- reset / volume delete で設計データが消える可能性。
- 作業の節目では project metadata bundle を export すること。

### Durable Single-user Mode

個人でも継続利用する場合は、`config_db` の backup を明示的に取るべきである。

最低限必要なもの:

- `config_db` の DB backup。
- project metadata bundle の定期 export。
- secrets の別保管。

この mode では、local compose のままでもよいが、backup / restore 手順をユーザーが理解できる必要がある。

### Team / Shared Mode

チーム利用では、`APP_CONFIG_DB_*` で external config DB を使う構成を標準にするべきである。

理由:

- 設計データがローカル端末に閉じない。
- DB backup / restore / migration を通常の運用に乗せられる。
- 複数人・複数環境で同じ canonical metadata を扱いやすい。

## Project Metadata Bundle Positioning

project metadata bundle は、設計データの portable snapshot である。

主な用途:

- 作業区切りの snapshot。
- 別環境への移動。
- review / handoff。
- config DB 障害時の部分復旧材料。

ただし、これは `config_db` の完全代替ではない。current scope に含まれない周辺情報があるため、「完全バックアップ」ではなく「project-scoped design metadata snapshot」と説明するのが正確である。

bundle に含めるべきもの:

- project core metadata。
- table / data class / DB access / source output metadata。
- database source metadata。
- secrets template。

bundle に含めないもの:

- 実 password。
- 外部 DB の実データ。
- scratch output。
- browser smoke artifact。

## Secrets Policy

設計データと secrets は分離する。

database source には接続先や source_key などの設計情報を保存できるが、password そのものは設計データに混ぜない。設計データの export/import では、次の扱いが安全である。

- password は default export に含めない。
- `password_env` を優先する。
- secrets template は生成してよい。
- populated secrets file は Git に入れない。
- import 時に必要な secret がない場合は fail closed にする。

## Backup And Restore Policy

設計データのバックアップ対象は `config_db` である。

推奨:

| 対象 | 頻度 | 目的 |
| --- | --- | --- |
| `config_db` DB backup | 日次または変更頻度に応じて | 設計データ全体の復旧 |
| project metadata bundle export | 大きな変更前後、release 前 | project 単位の snapshot / handoff |
| secrets template | source 追加・変更時 | 必要 secret の棚卸し |
| populated secrets | 組織の secret 管理方針に従う | 接続情報の復旧 |

restore は次の 2 系統に分ける。

1. 環境全体の復旧
   - `config_db` backup を restore する。
   - schema migration / preflight を実行する。

2. project 単位の復旧・移動
   - project metadata bundle を import preview する。
   - 差分を確認して apply する。
   - secrets は別途設定する。

## Product Requirements

設計データ永続化を一般ユーザーに安全に提供するには、次の導線が必要である。

1. 保存先の可視化
   - 現在の `config_db` が local か external かを admin UI で表示する。

2. 破壊的操作の警告
   - reset、volume delete、project delete、import apply replace の前に、設計データへの影響を明示する。

3. export / import の標準導線
   - project metadata bundle export。
   - import preview。
   - apply。
   - secrets template export。

4. backup / restore docs
   - local compose の backup。
   - external config DB の backup。
   - project metadata bundle による移行。

Current implementation note:

- local default stack 用に `make backup-config-db` / `make restore-config-db` を用意する。
- MTOOL core seed stack 用に `make backup-config-db-mtool` / `make restore-config-db-mtool` を用意する。
- durable env file lane 用に `deploy/durable-config-db.env.example` と `make up-durable-config-db` / `make config-db-preflight-durable-config-db` / `make db-config-migrate-durable-config-db` を用意する。
- restore は `BACKUP_FILE=...` と `CONFIRM_RESTORE=yes` を必須にする。
- dump は `work/backups/config-db/` 配下に置き、Git 管理しない。

5. artifact と設計データの UI 分離
   - generated output は成果物として表示する。
   - 設計データの保存状態とは別に扱う。

## Current Gaps

1. local compose 利用時、設計データが Docker volume に依存することが一般ユーザーに伝わりにくい。

2. project metadata bundle は有用だが、完全バックアップではないため、scope 表示が必要である。

3. generated output が見えるため、ユーザーがそれを保存済み設計データだと誤解する可能性がある。

4. reset / delete / import apply などの破壊的操作では、設計データへの影響を明示する guard が必要である。

5. secrets と database source metadata の境界を UI / docs でさらに明確にする必要がある。

## Recommended User-facing Statement

> DegoDB の設計データは `config_db` に保存されます。設計データには、プロジェクト、テーブル定義、データクラス定義、DB access 設計、source output 設定、DB source metadata が含まれます。生成された OpenAPI や runtime ファイルは、この設計データから作られる成果物であり、正本ではありません。DB password などの秘密情報と、外部 DB の実データは、設計データとは別に管理します。

## Conclusion

一般ユーザー向けには、DegoDB の保存対象を「設計データ」として明確に定義し、その正本を `config_db` に置く方針を徹底するべきである。

生成物、scratch、browser artifact、外部 DB の実データ、secrets を設計データから分離することで、backup / restore / import / export の説明が単純になる。長期利用では external config DB と定期 backup、プロジェクト単位の移動には project metadata bundle、秘密情報には env / secrets sidecar を使う構成が現実的である。
