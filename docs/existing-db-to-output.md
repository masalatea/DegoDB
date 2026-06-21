# Existing DB To Output

English companion:
This is the end-to-end runbook for connecting an existing database, storing its canonical metadata, generating outputs, verifying them in lab, and capturing a rerun payload. Each stage keeps the purpose, UI lane, CLI lane, persistence point, success markers, and troubleshooting notes together.

この文書は、既存 DB を current repo に自然につなぎ、canonical metadata として永続化し、`Data Class`、`DB Access`、`Source Output`、lab verification まで 1 本で辿るための恒久文書です。  
人は UI lane、AI は CLI lane を選びやすいように、step ごとに `何をするか`、`どこへ保存されるか`、`成功条件` を固定します。

何がどこに残るかを 1 枚で見たい時は [storage-and-state-model.md](storage-and-state-model.md) を参照してください。  
bundle export / import preview / secret 分離の詳細は [project-metadata-bundle.md](project-metadata-bundle.md)、topology と `config_db` の扱いは [config-db-externalization.md](config-db-externalization.md) を参照してください。

## この文書の使い方

- 初回に 1 本通す時
  - `Quick Start` で全体像を掴んでから Stage 1 から Stage 10 を順に辿る
- source 登録後の rerun
  - topology が固定済みなら Stage 1 を飛ばしてよい
  - `source_key` が残っていれば Stage 3 を飛ばしてよい
  - ただし apply 前の preview は Stage 4 で取り直す
- AI / handoff
  - `project_key`、`source_key`、chosen lane、最後に publish した `artifact_key` を残す
  - 別環境へ持ち出すか次回再開まで固めるなら Stage 10 を使う

重要:

- preview は Stage 4 の UI を使う
- `import_project_tables.php` は preview ではなく apply

## この flow の前提

- local default lane
  - 1 台の local stack で始める
  - `make up` を使う
  - canonical metadata は local `db-config` に保存する
- external `config_db` lane
  - shared / hosted MariaDB を canonical store として使う
  - `APP_CONFIG_DB_*` を付けて `make up-external-config-db` を使う
  - canonical metadata は external `config_db` に保存する

どちらの lane でも、既存 DB 自体は import source です。  
`import -> sync -> publish` で更新される正本は `config_db` と output artifact であり、既存 DB schema/data をこの flow が直接書き換える前提ではありません。

<a id="e0-quick-start"></a>
## Quick Start

### local default lane で新しい source を登録して 1 周する

1. boot / preflight

```bash
make env
make up
make config-db-preflight
```

2. source を登録して preview を確認する

```text
/settings/database-sources
/projects/<project_key>/tables/import?source=named-live-schema:<source_key>
```

3. apply / sync / publish

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_tables.php \
  --project-key=<project_key> \
  --source=named-live-schema:<source_key>

docker compose exec -T web-admin php /var/www/mtool/scripts/sync_project_data_classes.php \
  --project-key=<project_key>

docker compose exec -T web-admin php /var/www/mtool/scripts/sync_project_db_access.php \
  --project-key=<project_key>

docker compose exec -T web-admin php /var/www/mtool/scripts/create_project_output.php \
  --project-key=<project_key> \
  --source-output-key=DBTABLE-PROXY-SERVER \
  --requested-by=manual \
  --publish

docker compose exec -T web-admin php /var/www/mtool/scripts/create_project_output.php \
  --project-key=<project_key> \
  --source-output-key=OPENAPI-JSON \
  --requested-by=manual \
  --publish
```

4. verify

- viewer: `/runs/swagger/<project_key>?source_output_key=OPENAPI-JSON&db_source_key=<source_key>`
- artifact: `/projects/<project_key>/source-outputs/artifacts/{artifact_key}/download`

### source 登録済みなら CLI lane だけで rerun する

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_tables.php \
  --project-key=<project_key> \
  --source=named-live-schema:<source_key> \
  --table=<table_name>

docker compose exec -T web-admin php /var/www/mtool/scripts/sync_project_data_classes.php \
  --project-key=<project_key>

docker compose exec -T web-admin php /var/www/mtool/scripts/sync_project_db_access.php \
  --project-key=<project_key>

docker compose exec -T web-admin php /var/www/mtool/scripts/create_project_output.php \
  --project-key=<project_key> \
  --source-output-key=DBTABLE-PROXY-SERVER \
  --requested-by=manual \
  --publish

docker compose exec -T web-admin php /var/www/mtool/scripts/create_project_output.php \
  --project-key=<project_key> \
  --source-output-key=OPENAPI-JSON \
  --requested-by=manual \
  --publish
```

## Journey Map

| stage | 目的 | 主な保存先 | primary lane | success markers | troubleshooting |
| --- | --- | --- | --- | --- | --- |
| [Stage 1](#e1-choose-topology) | topology を選ぶ | なし | CLI | local か external かが固定される | [T1](troubleshooting.md#t1-lane-mixups) |
| [Stage 2](#e2-boot-and-preflight) | boot / preflight を通す | chosen `config_db` schema | CLI | `ok=true` / `schema_current=true` | [T1](troubleshooting.md#t1-lane-mixups), [T4](troubleshooting.md#t4-config-db-preflight) |
| [Stage 3](#e3-register-source) | existing DB を source 登録する | `config_db.database_sources` | UI | `named-live-schema:{source_key}` が候補に出る | [T2](troubleshooting.md#t2-source-missing-from-import-options) |
| [Stage 4](#e4-review-import-preview) | import preview を読む | なし | UI | insert / change / stale count を説明できる | [T3](troubleshooting.md#t3-import-preview-apply-confusion) |
| [Stage 5](#e5-apply-import) | canonical table metadata を apply する | `config_db.dbtable*` | UI or CLI | apply summary が期待どおり | [T3](troubleshooting.md#t3-import-preview-apply-confusion), [T4](troubleshooting.md#t4-config-db-preflight) |
| [Stage 6](#e6-sync-data-classes) | Data Class metadata を sync する | `config_db.dataclass*` | CLI | imported table に対応する data class metadata が揃う | [T4](troubleshooting.md#t4-config-db-preflight) |
| [Stage 7](#e7-sync-db-access) | DB Access metadata を sync する | `config_db.project_db_access_*` | CLI | imported function と target assignment が揃う | [T4](troubleshooting.md#t4-config-db-preflight) |
| [Stage 8](#e8-publish-output) | output を create / publish する | `work/artifacts/...`, `work/source-outputs/...` | CLI | `artifact_key` と `published` が返る | [T7](troubleshooting.md#t7-openapi-visibility-and-raw-route-assumptions) |
| [Stage 9](#e9-verify-output) | lab / artifact lane で verify する | なし | UI or CLI smoke | viewer / artifact download が確認できる | [T6](troubleshooting.md#t6-runtime-source-selection-in-swagger-and-proxy), [T7](troubleshooting.md#t7-openapi-visibility-and-raw-route-assumptions) |
| [Stage 10](#e10-capture-rerun-path) | rerun path を固める | bundle dir, secrets sidecar | CLI | preview/apply と rerun scope を説明できる | [T5](troubleshooting.md#t5-missing-secret-env-in-bundle-preview) |

<a id="e1-choose-topology"></a>
## Stage 1. Choose Topology

### Purpose

- local default lane を使うか
- external `config_db` lane を使うか

ここを最初に固定します。

### UI

- なし

### CLI

local default:

```bash
make env
make up
```

external `config_db` lane:

```bash
APP_CONFIG_DB_HOST=external-db.example \
APP_CONFIG_DB_PORT=3306 \
APP_CONFIG_DB_NAME=config_app \
APP_CONFIG_DB_USER=config_app \
APP_CONFIG_DB_PASSWORD=secret \
make up-external-config-db
```

### Persistence

- まだ canonical metadata は増えません
- ここで固定されるのは compose topology と `config_db` の接続先です

### Success Markers

- local default なら `compose.yaml + compose.local-db-config.yaml` が前提になっている
- external lane なら local `db-config` を起動しない前提になっている
- team / AI の会話で「どちらの lane か」を明示できる

### Troubleshooting

- [T1. Lane Mixups](troubleshooting.md#t1-lane-mixups)

<a id="e2-boot-and-preflight"></a>
## Stage 2. Boot And Preflight

### Purpose

- chosen `config_db` が current schema で使えるかを確認する

### UI

- なし

### CLI

local default:

```bash
make config-db-preflight
```

external lane:

```bash
make config-db-preflight-external-config-db
```

`schema_current=false` の時は migration を先に通します。

local default:

```bash
make db-config-migrate
make config-db-preflight
```

external lane:

```bash
make db-config-migrate-external-config-db
make config-db-preflight-external-config-db
```

### Persistence

- migration を打った時だけ chosen `config_db` schema が更新されます

### Success Markers

- `ok=true`
- `schema_current=true`

### Troubleshooting

- [T1. Lane Mixups](troubleshooting.md#t1-lane-mixups)
- [T4. Config DB Preflight](troubleshooting.md#t4-config-db-preflight)

<a id="e3-register-source"></a>
## Stage 3. Register The Existing DB As A Named Source

### Purpose

- existing DB を import source catalog に登録する
- 必要なら Swagger / published proxy の runtime read source にもする

### UI

- page: `/settings/database-sources`
- current supported lane では、new source の一般用途登録はこの UI を正本にします

登録時の current rule:

- `source_key` は built-in key (`db` / `config_db` / `lab_db`) と衝突させない
- schema import に使うなら `supports_live_schema_import=1`
- Swagger / published proxy の runtime read にも使うなら `supports_proxy_runtime_read=1`

### CLI

- current supported lane には汎用の create CLI を入れていません
- 既存 source を別環境へ持ち込みたい時は [project-metadata-bundle.md](project-metadata-bundle.md) の `database_sources` sidecar import を使います

### Persistence

- `config_db.database_sources`

### Success Markers

- source が `/settings/database-sources` の一覧に見える
- project import page の source 候補に `named-live-schema:{source_key}` が出る

### Troubleshooting

- [T2. Source Missing From Import Options](troubleshooting.md#t2-source-missing-from-import-options)

<a id="e4-review-import-preview"></a>
## Stage 4. Review Import Preview

### Purpose

- apply 前に import scope と diff を読む

### UI

```text
/projects/{project_key}/tables/import?source=named-live-schema:{source_key}
```

1 table に絞りたい時:

```text
/projects/{project_key}/tables/import?source=named-live-schema:{source_key}&table={table_name}
```

preview で読むもの:

- `source tables`
- `canonical tables`
- `table new`
- `table changed`
- `table stale`
- `column new`
- `column changed`
- `column stale`

### CLI

- current supported preview は UI page を正本にします
- current CLI の `import_project_tables.php` は apply です

### Persistence

- preview 自体は何も保存しません

### Success Markers

- source schema と target project が期待どおり
- insert / change / stale count の意味を説明できる
- 取り込み scope が大きすぎる時は `table=` で絞れる

### Troubleshooting

- [T3. Import Preview / Apply Confusion](troubleshooting.md#t3-import-preview-apply-confusion)

<a id="e5-apply-import"></a>
## Stage 5. Apply Canonical Table Metadata

### Purpose

- preview で確認した schema diff を canonical table metadata に反映する

### UI

- preview page の apply button を使う

### CLI

all tables:

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_tables.php \
  --project-key=<project_key> \
  --source=named-live-schema:{source_key}
```

1 table:

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_tables.php \
  --project-key=<project_key> \
  --source=named-live-schema:{source_key} \
  --table=<table_name>
```

### Persistence

- `config_db.dbtable`
- `config_db.dbtablecolumns`

### Success Markers

- apply summary の insert / change / delete count が期待どおり
- project 側の canonical table metadata が import source の schema に追随する

### Troubleshooting

- [T3. Import Preview / Apply Confusion](troubleshooting.md#t3-import-preview-apply-confusion)
- [T4. Config DB Preflight](troubleshooting.md#t4-config-db-preflight)

<a id="e6-sync-data-classes"></a>
## Stage 6. Sync Data Class Metadata

### Purpose

- imported table metadata から data class metadata を current canonical shape に揃える

### UI

- current first-pass guide では UI を primary lane にしません
- rerun の再現性を優先して CLI を正本にします

### CLI

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/sync_project_data_classes.php \
  --project-key=<project_key>
```

### Persistence

- `config_db.dataclass`
- `config_db.dataclassfields`

### Success Markers

- imported table に対応する data class metadata が生成 / 更新される
- canonical metadata の source of truth は引き続き `config_db`

### Troubleshooting

- [T4. Config DB Preflight](troubleshooting.md#t4-config-db-preflight)

<a id="e7-sync-db-access"></a>
## Stage 7. Sync DB Access Metadata

### Purpose

- imported table metadata から DB access metadata と function target assignment を current canonical shape に揃える

### UI

- current first-pass guide では UI を primary lane にしません
- rerun の再現性を優先して CLI を正本にします

### CLI

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/sync_project_db_access.php \
  --project-key=<project_key>
```

### Persistence

- `config_db.project_db_access_classes`
- `config_db.project_db_access_functions`
- `config_db.project_db_access_function_source_output_targets`

### Success Markers

- imported canonical-bootstrap function が project に入る
- generic single-function outputs があれば、初回 sync 時に `DBTABLE-PROXY-SERVER` / `OPENAPI-JSON` へ default target assignment される

### Troubleshooting

- [T4. Config DB Preflight](troubleshooting.md#t4-config-db-preflight)

<a id="e8-publish-output"></a>
## Stage 8. Create And Publish Outputs

### Purpose

- runtime verification に使う output artifact を生成し、current raw output に publish する

### UI

- current first-pass guide では UI を primary lane にしません
- rerun の再現性を優先して CLI を正本にします

### CLI

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/create_project_output.php \
  --project-key=<project_key> \
  --source-output-key=DBTABLE-PROXY-SERVER \
  --requested-by=manual \
  --publish

docker compose exec -T web-admin php /var/www/mtool/scripts/create_project_output.php \
  --project-key=<project_key> \
  --source-output-key=OPENAPI-JSON \
  --requested-by=manual \
  --publish
```

### Persistence

- artifact history: `work/artifacts/source-outputs/{project_key}/{artifact_key}/bundle/...`
- current raw output: `work/source-outputs/{project_key}/{source_output_key}`

### Success Markers

- `artifact_key` が返る
- `archive_path` と manifest path が返る
- `published` が non-null になり `work/source-outputs/...` が更新される

### Troubleshooting

- [T7. OpenAPI Visibility And Raw Route Assumptions](troubleshooting.md#t7-openapi-visibility-and-raw-route-assumptions)

<a id="e9-verify-output"></a>
## Stage 9. Verify In Lab And Artifact Lanes

### Purpose

- published output が current supported share lane で実際に使えるかを見る

### UI

Swagger viewer:

```text
/runs/swagger/{project_key}?source_output_key=OPENAPI-JSON&db_source_key={source_key}
```

artifact download:

```text
/projects/{project_key}/source-outputs/artifacts/{artifact_key}/download
```

### CLI

default local stack で smoke をまとめて通したい時:

```bash
make mtool-external-source-lab-smoke
make mtool-external-source-lab-browser-smoke
```

### Persistence

- verify 自体は新しい canonical metadata を増やしません
- browser smoke artifact は `output/playwright/...` に残ることがあります

### Success Markers

- viewer が `db_source_key={source_key}` を保持して開く
- `Try It Out` で target source を読ませられる
- artifact bundle を tar.gz として取れる

current rule:

- explicit `db_source_key` は `supports_proxy_runtime_read=1` の source のみ使える
- `project_source_outputs.spec_visibility` の default は `internal-only`
- `spec_visibility=disabled` だと authenticated viewer からも隠れる
- Delivery 時に `work/source-outputs/.../openapi.json` をそのまま public hosting へ置かない。fixed filename 自体ではなく、未認証の raw delivery がリスクになる

### Troubleshooting

- [T6. Runtime Source Selection In Swagger And Proxy](troubleshooting.md#t6-runtime-source-selection-in-swagger-and-proxy)
- [T7. OpenAPI Visibility And Raw Route Assumptions](troubleshooting.md#t7-openapi-visibility-and-raw-route-assumptions)

<a id="e10-capture-rerun-path"></a>
## Stage 10. Capture The Rerun Path

### Purpose

- 次回の更新、別環境への移送、AI への handoff で必要な材料を確定する

### UI

- なし

### CLI

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/export_project_metadata.php \
  --project-key=<project_key> \
  --database-sources=<source_key> \
  --output-dir=/tmp/mtool-project-metadata-bundle-<project_key> \
  --requested-by=manual

docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_metadata.php \
  --bundle=/tmp/mtool-project-metadata-bundle-<project_key> \
  --mode=preview \
  --database-source-secrets=/tmp/mtool-project-metadata-secrets.json \
  --requested-by=manual
```

### Persistence

- bundle dir
- `database-source-secrets` sidecar

### Success Markers

- import source はどの DB か説明できる
- canonical metadata はどの `config_db` に保存されたか説明できる
- runtime read はどの `db_source_key` を使うか説明できる
- publish した artifact はどれか説明できる
- import preview が warning / apply plan を返す

### Troubleshooting

- [T5. Missing Secret Env In Bundle Preview](troubleshooting.md#t5-missing-secret-env-in-bundle-preview)

<a id="e10-handoff-payload"></a>
### Handoff Payload Example

```text
project_key=<project_key>
chosen_lane=local-default | external-config-db
source_key=<source_key>
current_stage=stage4-previewed | stage5-applied | stage8-published | stage9-verified | stage10-handoff-ready
runtime_db_source_key=<source_key>
last_artifact_key=<artifact_key>
config_db_target=local-db-config | <external host>/<database name>
bundle_dir=/tmp/mtool-project-metadata-bundle-<project_key>
database_source_secrets=/tmp/mtool-project-metadata-secrets.json
latest_checks:
- make config-db-preflight => ok=true schema_current=true
- docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/DocsEntranceContractTest.php => OK
```

bundle や secret sidecar がまだ無い段階なら、その行は省略して構いません。  
重要なのは `project_key`、chosen lane、`source_key`、`current_stage`、`last_artifact_key` を 1 つの block で残すことです。

## 何が起きて、何が起きないか

この flow で起きること:

- existing DB schema を canonical metadata として `config_db` へ取り込む
- canonical metadata から `Data Class`、`DB Access`、`Source Output` を更新する
- lab viewer / artifact download まで current supported lane を通す

この flowで起きないこと:

- 既存 DB を canonical store に置き換えること
- import / sync / publish が既存 DB schema/data を直接書き換えること
- raw `openapi.json` を public static file として配ること
- `mtool/reference/legacy-*` を runtime input に戻すこと

## 完了時に手元に残るもの

- source 登録を行ったなら `config_db.database_sources` に `source_key` が残る
- canonical table metadata は `config_db.dbtable*` に残る
- canonical Data Class / DB Access metadata は `config_db.dataclass*` と `config_db.project_db_access_*` に残る
- publish した output は `work/artifacts/source-outputs/...` と `work/source-outputs/...` に残る
- Stage 10 まで行ったなら bundle dir と `database-source-secrets` sidecar が rerun / handoff 材料として残る
- existing DB 自体は import source または runtime source のままで、canonical store にはならない

## 関連文書

- [storage-and-state-model.md](storage-and-state-model.md)
  - 何がどこに保存されるか
- [config-db-externalization.md](config-db-externalization.md)
  - topology と preflight / migrate
- [project-metadata-bundle.md](project-metadata-bundle.md)
  - bundle export / import preview / secret sidecar
- [current-supported-workflow.md](current-supported-workflow.md)
  - current lane の supported boundary
- [troubleshooting.md](troubleshooting.md)
  - stage ごとの warning / error 切り分け
