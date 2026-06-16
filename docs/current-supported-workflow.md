# Current Supported Workflow

English companion:
This is the current green path for the repo. Read it when you need the supported mainline for import, sync, output generation, tutorial checks, runtime reference inspection, and the boundary between current lanes and archived helpers.

この文書は、今の repo で「green path として信頼してよい mainline」をまとめたものです。  
過去の helper や archive 済み導線まで全部列挙するのではなく、AI がまず使うべき current workflow だけを残します。

既存 DB を named source として登録し、canonical metadata 永続化から output publish / verify まで 1 本で辿る時は [existing-db-to-output.md](existing-db-to-output.md) を正本にします。  
何がどこに保存されるかは [storage-and-state-model.md](storage-and-state-model.md) を参照してください。

warning / error の意味を切り分けたい時は [troubleshooting.md](troubleshooting.md) を参照してください。

途中再開なら、先に [existing-db-to-output.md#e10-handoff-payload](existing-db-to-output.md#e10-handoff-payload) で handoff payload を確認し、[storage-and-state-model.md#s1-resume-checkpoints](storage-and-state-model.md#s1-resume-checkpoints) で `config_db` / artifact 側の残存 state を見ます。  
共同作業や AI handoff の内部 contract が必要な時だけ [internal/README.md](internal/README.md) を参照してください。

## mainline の考え方

- 起点は `DB 構造 -> import -> Data Class -> DB Access -> Source Output`
- `Project 1 = MTOOL` を current core として扱う
- tutorial sample は `sample/tutorials/` を user-facing lane とする
- historical helper や archived alias は、必要になった時だけ調べる

## current lane

### core lane

- `MTOOL` 自体の import / sync / output / self-loop 検証

### tutorial lane

- `sample/tutorials/sample01-simple-table-runtime`
- `sample/tutorials/sample02-dataclass-nullable-default-status`
- `sample/tutorials/sample03-dataclass-lookup-and-helper`
- `sample/tutorials/sample04-dataclass-parent-child-basic`
- `sample/tutorials/sample05-dbaccess-select-basic`
- `sample/tutorials/sample06-dbaccess-filter-sort-page`
- `sample/tutorials/sample07-dbaccess-crud-basic`
- `sample/tutorials/sample08-dbaccess-join-read-model`
- `sample/tutorials/sample09-dbaccess-aggregate-report`
- `sample/tutorials/sample10-dbaccess-mini-crud-flow`
- `sample/tutorials/sample11-html-template-output`
- `sample/tutorials/sample12-external-db-source-import`
- `sample/tutorials/sample13-openapi-api-surface`
- `sample/tutorials/sample14-custom-proxy-runtime`
- `sample/tutorials/sample15-project-metadata-export-import`
- `sample/tutorials/sample16-authenticated-proxy`
- `sample/tutorials/sample17-multi-output-project`

### internal guard lane

- `sample/internal-patterns/pattern01-default-property-split` から `pattern14-method-and-enum-heavy-multimethod`

### representative project lane

- sanitized legacy project packs under `sample/legacy-projects/`

## まず使うコマンド

### 1. base environment

```bash
make env
make up-mtool
```

`make up-mtool` は MTOOL core seed 付きで `compose.yaml` に `compose.local-db-config.yaml` と `mtool/docker/compose/01_mtool.compose.yaml` を重ねて起動する。空の local config DB だけを起動したい時は `make up` を使う。

external/shared env で `APP_CONFIG_DB_*` を external MariaDB に向け、local `db-config` を起動しない時は次を使う。

```bash
APP_CONFIG_DB_HOST=external-db.example \
APP_CONFIG_DB_PORT=3306 \
APP_CONFIG_DB_NAME=config_app \
APP_CONFIG_DB_USER=config_app \
APP_CONFIG_DB_PASSWORD=secret \
make up-external-config-db
```

継続利用・チーム利用では、Git 管理外の durable env file を使う。

```bash
cp deploy/durable-config-db.env.example .env.durable
make up-durable-config-db DURABLE_ENV_FILE=.env.durable
make config-db-preflight-durable-config-db DURABLE_ENV_FILE=.env.durable
```

local `make up-mtool` / `make up` の設計データは Docker volume に残る。reset 前や継続利用では `make backup-config-db-mtool` または `make backup-config-db` で dump を取る。

起動後の確認や teardown は次を使う。

```bash
make ps-external-config-db
make health-external-config-db
make config-db-preflight-external-config-db
make db-config-migrate-external-config-db
make down-external-config-db
```

`make start` / `make stop` / `make ps` / `make logs` / `make db-config-shell` / `make config-db-preflight` / `make db-config-migrate` は local default overlay lane 用で、external lane には混ぜない。

external lane で shell や一時 stop が必要な時だけ raw base compose を使う。

```bash
docker compose -f compose.yaml exec web-admin bash
COMPOSE_PROFILES=lab-db-ui docker compose -f compose.yaml stop
```

### 2. MTOOL canonical import / sync

```bash
make mtool-canonical-sync
```

### 2.5. Lab DB から canonical metadata へ取り込む

operator 向けの step-by-step は [existing-db-to-output.md#e3-register-source](existing-db-to-output.md#e3-register-source) から [existing-db-to-output.md#e5-apply-import](existing-db-to-output.md#e5-apply-import) を使う。ここでは current boundary だけを残す。

- `make up-mtool` 後に `lab-db-ui` で `db-lab` の schema を編集できる
- external source を足す時は admin 側の `/settings/database-sources` で named database source を登録する
- 取り込みは `lab-live-schema` または `named-live-schema:{source_key}` を使う
- canonical metadata の保存先は引き続き `db-config` で、`db-lab` と external source は import source として使う
- current code では `live-schema` / `lab-live-schema` は built-in named database source catalog (`db` / `lab_db`) の上に載っており、admin-managed external source も同じ catalog に merge される

### 2.6. Lab DB -> Swagger Try It Out の最短 verified lane

`MTOOL` core seed に `DBTABLE-PROXY-SERVER` / `OPENAPI-JSON` がある前提では、import した canonical-bootstrap function は初回 `sync_project_db_access.php` 時にこの 2 target へ default assignment される。

operator 向けの full flow は [existing-db-to-output.md#e8-publish-output](existing-db-to-output.md#e8-publish-output) と [existing-db-to-output.md#e9-verify-output](existing-db-to-output.md#e9-verify-output) を使う。ここでは verified command lane と support boundary だけを残す。

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_tables.php --project-key=MTOOL --source=lab-live-schema
docker compose exec -T web-admin php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=MTOOL
docker compose exec -T web-admin php /var/www/mtool/scripts/sync_project_db_access.php --project-key=MTOOL
docker compose exec -T web-admin php /var/www/mtool/scripts/create_project_output.php --project-key=MTOOL --source-output-key=DBTABLE-PROXY-SERVER --publish
docker compose exec -T web-admin php /var/www/mtool/scripts/create_project_output.php --project-key=MTOOL --source-output-key=OPENAPI-JSON --publish
```

- lab viewer は `/runs/swagger/MTOOL?source_output_key=OPENAPI-JSON` で開ける。external named source を固定したい時は `&db_source_key={source_key}` を付けるか viewer の `db_source_key` selector を使う
- generated `openapi.json` は `work/source-outputs/...` または artifact bundle 配下の internal artifact として扱う。current local stack では docroot 直下の public static file としては配らない
- `project_source_outputs.spec_visibility` を追加済みで、default は `internal-only`、`disabled` にすると authenticated viewer からも隠す。fixed filename の主防御は random suffix ではなく storage boundary と access control に置く
- public alias key / raw spec route は current では未実装で、supported share lane は authenticated viewer と admin artifact download のみである
- `lab_experiments.Getlab_experimentsList` は `Try It Out` で `HTTP 200 OK` と 2 row を返すところまで確認済みで、viewer 側から `db_source_key` query を付けて runtime DB source を明示できる
- legacy row で `single_proxy_auth_type` が空の endpoint は `project-token` 扱いだが、viewer 側に `Auth Helper Inputs` を追加済みで、`TOKEN` / `LOGIN_COOKIE_TOKEN` の notice と送信時補完を使える
- published proxy relay は `db_source_key` を受け付け、旧 `db_config_key` query も互換で残す。未指定時は named database source catalog の proxy-runtime priority と canonical store fallback で解決する
- explicit `db_source_key` / `db_config_key` は `supports_proxy_runtime_read=1` の source のみ許可する。viewer の invalid query は auto-select notice に戻し、published proxy relay の invalid query は `422` を返す
- canonical-bootstrap で起きた imported table function は `NoSecurity` default なので、`lab_experiments` 系は token なしでそのまま叩ける
- artifact を file として渡したい時は admin 側の `/projects/{project_key}/source-outputs/artifacts/{artifact_key}/download` で tar.gz bundle を取る。raw `openapi.json` の public route は増やさない

### 2.7. External named source -> Lab Swagger / proxy / browser Try It Out の verified smoke

manual lane の operator 手順は [existing-db-to-output.md#e3-register-source](existing-db-to-output.md#e3-register-source) から [existing-db-to-output.md#e9-verify-output](existing-db-to-output.md#e9-verify-output) を使う。default local stack で verified smoke だけ再現したい時は次を使う。

```bash
make mtool-external-source-lab-smoke
```

Lab Swagger viewer の `Try It Out` まで headless Chrome で通す時は次を使う。

```bash
make mtool-external-source-lab-browser-smoke
```

- `make mtool-external-source-lab-smoke` は temporary source 作成、preview / apply、sync、publish、page load、proxy route を確認する
- `make mtool-external-source-lab-browser-smoke` は上の prepare lane を再利用し、`Try It Out` と CRUD cycle まで確認する
- 旧来の list-only smoke に戻したい時は `node mtool/scripts/check_external_database_source_lab_swagger_try_it_out.js --list-only` を使う
- browser 実行結果は `output/playwright/external-source-lab-swagger/<timestamp>/` に残る
- manual query で policy 外 source を渡したい場合でも current viewer は notice で戻し、proxy route は `422` を返す
- default では最後に一時 source を削除する。残したい時だけ `php mtool/scripts/check_external_database_source_lab_swagger_flow.php --keep-source` を使う

### 2.8. canonical metadata の project bundle export / import preview

current first slice は `project-core` scope だけを対象にする。local compose では host 側 PHP ではなく container 内 CLI を使う。  
scope / sidecar / secret separation / fail-closed rule の正本は [project-metadata-bundle.md](project-metadata-bundle.md) を参照する。

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/export_project_metadata.php \
  --project-key=MTOOL \
  --database-sources=<source_key> \
  --output-dir=/tmp/mtool-project-metadata-bundle-MTOOL \
  --requested-by=manual

docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_metadata.php \
  --bundle=/tmp/mtool-project-metadata-bundle-MTOOL \
  --mode=preview \
  --database-source-secrets=/tmp/mtool-project-metadata-secrets.json \
  --requested-by=manual
```

- import は `preview -> apply` の 2 段で使う。`apply` は core scope を replace するので preview を先に確認する
- `--target-project-key` を省略した時は bundle の `source_project_key` を使う
- secret は bundle に含めず、`--database-source-secrets=...` の separate JSON map で渡す
- `database_sources` は optional sidecar で、`--database-sources=...` を指定した時だけ bundle に入る

### 2.9. config DB externalization preflight

local compose default は `db-config` container のまま維持するが、`web-admin` / `web-lab` は `APP_CONFIG_DB_*` override で別 MariaDB を見られる。compose topology / supported target / warning boundary の正本は [config-db-externalization.md](config-db-externalization.md) を参照する。

```bash
make config-db-preflight
```

direct CLI は次でもよい。

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/check_config_db_bootstrap.php --requested-by=manual
```

- migrate apply は次を使う。

```bash
make db-config-migrate
```

- preflight は required table / required column / dropped legacy column を見て `schema_current=true/false` を返す
- `db-config-migrate` は current `APP_CONFIG_DB` target に `docker/mariadb/config-initdb/*.sql` を順番どおり apply する。default local では従来どおり `db-config` container に効き、override 時は external target に効く
- external lane で同じ確認をしたい時は `make config-db-preflight-external-config-db` / `make db-config-migrate-external-config-db` を使う
- canonical metadata repository / admin CRUD は `config_db` を直接読む。built-in `db` は `live-schema` import source と site default DB の意味のまま残る
- host/shared env では `APP_DB_*` と `APP_CONFIG_DB_*` を分けてもよい。preflight は mismatch を diagnostic warning として返すが、`config_db` schema が current なら fail しない
- base `compose.yaml` は `db-config` service を持たない。default local の `make up` は `compose.local-db-config.yaml` を重ねて `db-config` を含める
- root compose の `web-admin` / `web-lab` は `db-config` 起動順依存を持たない。external target を使う時は `make up-external-config-db` で `db-config` を起動せずに `web-admin` / `web-lab` / `db-lab` を上げられる

### 3. tutorial lane の確認

```bash
make sample01-pack-runtime-test
make sample10-pack-runtime-test
```

### 4. full suite

local で旧 stack が default port を掴んでいることがあるため、まずは port override 付きの実行を基準にする。

```bash
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```

`make test` と `make sampleNN-pack-runtime-test` は、default では検証後に sample stack を `down -v` して片付ける。  
stack を残して中を見る時だけ `KEEP_SAMPLE_STACK_RUNNING=1 make test` のように明示する。

### 5. runtime reference と rollout の確認

```bash
make mtool-runtime-reference-status REQUIRE_CURRENT=1
php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only
```

## runtime reference の current rule

- promoted runtime reference の durable root は `mtool/reference/dbclasses/`
- current raw output は `work/source-outputs/MTOOL/RUNTIME-DBCLASSES`
- artifact history は `work/artifacts/source-outputs/`
- promote は `make promote-runtime-reference`
- recover は `make restore-runtime-reference-snapshot ARTIFACT_KEY=...`

### 重要な読み方

- `show_runtime_reference_status.php --require-current` は strict check
- durable snapshot が残っていて JSON 上は `ok=true` でも、`work/` 側の latest artifact history が無いと `status=reference-snapshot-only` になり、CLI exit code は non-zero になる
- これは「durable snapshot はあるが、latest artifact と authoritative reference が current mainline として並んで見えていない」状態で読む

## current で使わない導線

- `make bootstrap-dbclasses`
- `make bootstrap-dbclasses-runtime-reference`
- archived helper を current mainline の一部として扱うこと
- `original-codes/` を Docker runtime input にすること

archive 済み helper は必要になった時だけ archive から明示的に取り出して扱う。

## naming rule の current 読み

- tutorial runtime test の canonical 名は `sampleNN-pack-runtime-test`
- internal pattern output test の canonical 名は `patternNN-output-test`
- historical な `sample9-output-test` から `sample22-output-test`、`Sample9-22...` 命名は互換 layer として残っている
- 新しい tutorial sample には historical `Sample9-22` 命名を再利用しない

## support boundary

- `original-codes/` は host-side reference only
- `work/` は disposable
- date-less な `docs/` が恒久仕様の source of truth
- dated report は判断履歴として読む

## 関連文書

- [start-here.md](start-here.md)
- [common-tasks.md](common-tasks.md)
- [internal/README.md](internal/README.md)
- [../tests/README.md](../tests/README.md)
