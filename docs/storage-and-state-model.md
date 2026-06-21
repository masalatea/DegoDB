# Storage And State Model

English companion:
This document explains where state is stored across the existing-DB-to-output journey. It separates durable metadata, disposable runtime output, shared artifacts, and resume checkpoints so operators know what persists and what can be thrown away.

この文書は、`existing DB -> canonical metadata -> output` の journey で、何がどこに保存されるかを 1 枚で説明するための恒久文書です。  
「既存 DB をつないだら何が永続化されるのか」「どこが disposable なのか」「どこが share lane なのか」を current rule で固定します。

導線の正本は [existing-db-to-output.md](existing-db-to-output.md) を参照してください。

## 1 枚で見る flow

```text
existing DB / lab_db
  -> import preview / apply
  -> config_db canonical metadata
  -> sync_project_data_classes.php
  -> sync_project_db_access.php
  -> create_project_output.php --publish
  -> work/artifacts/... + work/source-outputs/...
  -> lab viewer / admin artifact download
```

## state map

| place | 主な役割 | 何が入るか | 誰が書くか | durable reading |
| --- | --- | --- | --- | --- |
| `config_db` | canonical store | `project`, `dbtable*`, `dataclass*`, `project_db_access_*`, `project_source_outputs`, `database_sources` | admin UI、import apply、sync CLI、bundle import | current source of truth |
| existing DB | import source / runtime source | 既存 schema と data | DB owner、自分の migration、運用 job | canonical store ではない |
| `db-lab` | local rehearsal source | editable schema / sample data | `lab-db-ui`、lab 側の手操作 | canonical store ではない |
| `work/artifacts/source-outputs/{project_key}/{artifact_key}/bundle/...` | artifact history | 生成済み source output bundle | `create_project_output.php` | regenerate できる artifact |
| `work/source-outputs/{project_key}/{source_output_key}` | current raw output | 最新 publish の materialized output | `create_project_output.php --publish` | 次回 publish で上書きされる |
| bundle dir (`/tmp/...`) | transport package | export 済み metadata bundle | `export_project_metadata.php` | 配布用。live store ではない |
| `database-source-secrets*.json` | secret sidecar | database source password または `password_env` | operator | commit しない |
| `mtool/reference/legacy-*` | curated legacy reference | 旧生成 DB class、旧 build ロジック、旧テンプレートの参照 | 人の調査作業だけ | runtime input ではない |

## よくある質問

### 既存 DB に接続したら、その DB 自体に書き込みますか

current mainline では、`import_project_tables.php`、`sync_project_data_classes.php`、`sync_project_db_access.php`、`create_project_output.php --publish` は canonical metadata と output artifact を更新します。  
既存 DB を直接 canonical store に置き換えたり、schema/data をそのまま書き換えたりする前提ではありません。

例外は、自分で `lab-db-ui` や DB client から既存 DB / `db-lab` を編集した時だけです。  
その変更を canonical metadata へ反映するには、改めて import preview / apply を流します。

### 設計はどこに永続化されますか

`config_db` です。

- table 設計: `dbtable` / `dbtablecolumns`
- data class 設計: `dataclass` / `dataclassfields`
- DB access 設計: `project_db_access_*`
- output 設計: `project_source_outputs`
- source catalog: `database_sources`

つまり、既存 DB から取り込んだあとに設計として残る正本は `config_db` 側です。

### output はどこに出ますか

`create_project_output.php` はまず artifact bundle を `work/artifacts/source-outputs/{project_key}/{artifact_key}/bundle/...` に作ります。  
`--publish` を付けた時だけ、current raw output として `work/source-outputs/{project_key}/{source_output_key}` も更新します。

この current raw output は便利な materialization ですが、永続正本ではありません。  
次の publish で置き換わるので、再現可能な正本は `config_db` と artifact history だと考えます。

### Swagger viewer / published proxy はどの DB を読みますか

runtime DB source は `db_source_key` で選びます。

- viewer path: `/runs/swagger/{project_key}?source_output_key=OPENAPI-JSON&db_source_key={source_key}`
- current rule: explicit `db_source_key` は `supports_proxy_runtime_read=1` の source だけ

つまり、existing DB を runtime read にも使いたいなら、named source 登録時に `supports_proxy_runtime_read=1` が必要です。

### OpenAPI は public file として配られますか

いいえ。current supported share lane は次だけです。

- authenticated viewer
- admin artifact download

`spec_visibility=internal-only` が default で、`disabled` なら viewer からも隠れます。  
raw `openapi.json` を public static file として配る前提にはしません。

## durable / disposable の読み方

durable と読むもの:

- `config_db` の canonical metadata
- 必要に応じて保持した artifact bundle
- 明示的に配布した metadata bundle

disposable と読むもの:

- `work/` 配下の current raw output
- compare 用の scratch
- local compose stack の一時 state

補足:

- `work/` は repo rule 上 disposable です
- ただし artifact history は再現確認や配布に使えるので、必要なものは明示的に残します

<a id="s1-resume-checkpoints"></a>
## resume / handoff でどこを見るか

- source 登録が残っているか
  - `config_db.database_sources`
- import apply が終わっているか
  - `config_db.dbtable` / `config_db.dbtablecolumns`
- Data Class sync が終わっているか
  - `config_db.dataclass` / `config_db.dataclassfields`
- DB Access sync が終わっているか
  - `config_db.project_db_access_*`
- 最後に publish した artifact はどれか
  - `work/artifacts/source-outputs/{project_key}/{artifact_key}/bundle/...`
- 今 viewer / proxy が見ている current raw output は何か
  - `work/source-outputs/{project_key}/{source_output_key}`
- 別環境へ持ち出せる rerun 材料は何か
  - bundle dir と `database-source-secrets*.json`

resume 時は、existing DB や `db-lab` の中身そのものより、まず `config_db` と artifact / bundle 側に残った state を確認します。  
existing DB、`db-lab`、`work/source-outputs/...` は便利でも canonical store ではありません。

## secrets の current rule

- bundle 本体には actual password を入れない
- `database-source-secrets.template.json` を placeholder として配布してよい
- populated secret file は commit しない
- 共有する時は literal password より `{ "password_env": "ENV_NAME" }` を優先する

## current boundary

- `config_db` は canonical store
- existing DB / `db-lab` は import source または runtime source
- `work/` は output materialization と artifact workspace
- `mtool/reference/legacy-*` は curated legacy reference only

## 関連文書

- [existing-db-to-output.md](existing-db-to-output.md)
  - journey 全体
- [config-db-externalization.md](config-db-externalization.md)
  - `config_db` topology の詳細
- [project-metadata-bundle.md](project-metadata-bundle.md)
  - bundle / secret sidecar の current rule
