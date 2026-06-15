# Common Tasks

English companion:
This document is a command-first task catalog for day-to-day work in the current repo. It keeps reusable shortcuts, smoke checks, and verification commands in one place, while the full end-to-end narrative stays in `existing-db-to-output.md`.

この文書は、Mtool を試したいユーザや contributor が最初に実行しやすい task を短い手順でまとめたものです。  
細かい背景説明より、current repo でそのまま使う導線を優先します。

existing DB から canonical metadata 永続化、設計、output verify までを 1 本で辿る時は [existing-db-to-output.md](existing-db-to-output.md) を正本にします。  
何がどこに保存されるかは [storage-and-state-model.md](storage-and-state-model.md) を参照してください。

この文書では end-to-end の背景を繰り返さず、reusable command と smoke shortcut だけを残します。

warning / error の意味を切り分けたい時は [troubleshooting.md](troubleshooting.md) を参照してください。

途中再開なら、先に [existing-db-to-output.md#e10-handoff-payload](existing-db-to-output.md#e10-handoff-payload) で handoff payload を確認し、[storage-and-state-model.md#s1-resume-checkpoints](storage-and-state-model.md#s1-resume-checkpoints) で `config_db` / artifact 側の残存 state を見ます。  
共同作業や AI handoff の内部 contract が必要な時だけ [internal/README.md](internal/README.md) を参照してください。

## 環境を起動する

```bash
make env
make up-mtool
```

`make up-mtool` は MTOOL core seed 付きで `compose.yaml` に `compose.local-db-config.yaml` と `mtool/docker/compose/01_mtool.compose.yaml` を重ねて起動する。空の local config DB だけを起動したい時は `make up` を使う。

external/shared env で local `db-config` を起動せず、`APP_CONFIG_DB_*` で external MariaDB を使う時は次を使う。

```bash
APP_CONFIG_DB_HOST=external-db.example \
APP_CONFIG_DB_PORT=3306 \
APP_CONFIG_DB_NAME=config_app \
APP_CONFIG_DB_USER=config_app \
APP_CONFIG_DB_PASSWORD=secret \
make up-external-config-db
```

継続利用・チーム利用では、env file を分けて durable lane を使う。

```bash
cp deploy/durable-config-db.env.example .env.durable
# .env.durable の APP_CONFIG_DB_* と password 類を実値に変更する
make up-durable-config-db DURABLE_ENV_FILE=.env.durable
make config-db-preflight-durable-config-db DURABLE_ENV_FILE=.env.durable
make db-config-migrate-durable-config-db DURABLE_ENV_FILE=.env.durable
```

`.env.durable` は `.gitignore` 対象で、Git に入れない。

起動後の確認や teardown は次を使う。

```bash
make ps-external-config-db
make health-external-config-db
make config-db-preflight-external-config-db
make db-config-migrate-external-config-db
make down-external-config-db
```

durable lane の確認や teardown は次を使う。

```bash
make ps-durable-config-db DURABLE_ENV_FILE=.env.durable
make health-durable-config-db DURABLE_ENV_FILE=.env.durable
make logs-durable-config-db DURABLE_ENV_FILE=.env.durable
make down-durable-config-db DURABLE_ENV_FILE=.env.durable
```

`make start` / `make stop` / `make ps` / `make logs` / `make db-config-shell` / `make config-db-preflight` / `make db-config-migrate` は local default overlay lane 用で、external lane には混ぜない。

external lane で shell や一時 stop が必要な時だけ raw base compose を使う。

```bash
docker compose -f compose.yaml exec web-admin bash
COMPOSE_PROFILES=lab-db-ui docker compose -f compose.yaml stop
```

MTOOL の canonical metadata を揃えるところまで進める場合は次を流す。

```bash
make mtool-canonical-sync
```

`make up-mtool` の出力には `lab-db-ui` も含まれる。
ここから `db-lab` の table 定義を変更し、admin 側の import source として使える。

external DB を named source として足す時は、admin 側の `/settings/database-sources` で接続先を登録してから import source に出す。

## Lab DB の schema を試してから取り込む

full flow の正本:

- source 登録: [existing-db-to-output.md#e3-register-source](existing-db-to-output.md#e3-register-source)
- import preview: [existing-db-to-output.md#e4-review-import-preview](existing-db-to-output.md#e4-review-import-preview)
- import apply: [existing-db-to-output.md#e5-apply-import](existing-db-to-output.md#e5-apply-import)
- output publish: [existing-db-to-output.md#e8-publish-output](existing-db-to-output.md#e8-publish-output)
- output verify: [existing-db-to-output.md#e9-verify-output](existing-db-to-output.md#e9-verify-output)

この section では short loop と smoke command だけを残します。

### Lab DB quick loop

1. `make up-mtool` で表示された `lab-db-ui` を開く
2. `db-lab` 上の table / column を編集する
3. admin 側で `/projects/{project_key}/tables/import?source=lab-live-schema` を開く
4. もしくは CLI で次を使う

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_tables.php --project-key=<target-project> --source=lab-live-schema
```

canonical metadata は `db-config` に保存され、`db-lab` は import source のまま残る。

external named source を使う場合は、`/settings/database-sources` で source を作成してから `named-live-schema:{source_key}` を import page か CLI apply へ渡す。手順そのものは golden path の stage 3-5 を使う。

### External named source smoke

default local stack でこの flow を一時 source 作成込みでまとめて確認する時は次を使う。

```bash
make mtool-external-source-lab-smoke
```

この smoke は admin UI で temporary external source を作成し、`lab_experiments` import、`OPENAPI-JSON` / `DBTABLE-PROXY-SERVER` publish、lab の Swagger page load と published proxy route を確認してから source を削除する。

Lab Swagger viewer の `Try It Out` まで headless Chrome で通す時は次を使う。

```bash
make mtool-external-source-lab-browser-smoke
```

この 2 本で確認すること:

- admin UI で temporary external source を作成できる
- `named-live-schema:{source_key}` の preview / apply が通る
- `OPENAPI-JSON` / `DBTABLE-PROXY-SERVER` publish が通る
- lab Swagger page load と published proxy route が通る
- browser smoke では `lab_experiments` の `Try It Out` と CRUD cycle まで確認できる

artifact は `output/playwright/external-source-lab-swagger/<timestamp>/` に残る。旧来の list-only smoke が必要な時は `node mtool/scripts/check_external_database_source_lab_swagger_try_it_out.js --list-only` を使う。

### Verification notes

lab の Swagger viewer で external named source を固定したい時は次の query を使う。

```text
/runs/swagger/{project_key}?source_output_key=OPENAPI-JSON&db_source_key={source_key}
```

この `source_key` は `supports_proxy_runtime_read=1` の source に限る。viewer では policy 外 key を渡すと notice 付きで auto-select に戻り、published proxy route では `422` を返す。

OpenAPI spec の共有は current では authenticated viewer か admin artifact download に限る。`work/source-outputs/.../openapi.json` を public route に直接載せず、`/artifacts/openapi/...` のような raw alias route も持たない。

#### OpenAPI Delivery時の注意

- `openapi.json` が固定 filename のまま internal artifact として存在すること自体は、current policy ではセキュリティホールではない
- 問題になるのは `work/source-outputs/.../openapi.json` を docroot 配下や auth なし raw route にそのまま出す場合である
- 外部へ渡す時は authenticated viewer か admin artifact download を使い、one-off な public hosting はしない
- anonymous または semi-private な share URL が本当に必要になった時だけ、public alias key / raw delivery route の設計を再開する

## canonical metadata bundle を export / import preview する

current first slice は `project-core` scope のみで、local compose では container 内 CLI を使う。  
scope / sidecar / secret separation / env reference の正本は [project-metadata-bundle.md](project-metadata-bundle.md) を参照する。

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

- `--target-project-key` を省略した時は bundle の `source_project_key` を使う
- `--mode=apply` は core metadata を replace するので、first slice では preview を先に確認する
- secret は bundle に入れず、`--database-source-secrets=...` の separate JSON map で渡す
- `database_sources` は optional sidecar で、`--database-sources=...` を指定した時だけ export/import する

## config DB を backup / restore する

local Docker volume だけに設計データを閉じ込めないため、継続利用では config DB dump を取る。

MTOOL core seed stack の場合:

```bash
make backup-config-db-mtool
make restore-config-db-mtool BACKUP_FILE=work/backups/config-db/config_db-mtool-YYYYMMDD-HHMMSS.sql CONFIRM_RESTORE=yes
```

local default stack の場合:

```bash
make backup-config-db
make restore-config-db BACKUP_FILE=work/backups/config-db/config_db-YYYYMMDD-HHMMSS.sql CONFIRM_RESTORE=yes
```

- backup は `work/backups/config-db/` に timestamp 付き SQL dump を作る
- `work/` は Git 管理しない
- restore は config DB state を上書きするため `CONFIRM_RESTORE=yes` を必須にしている
- external config DB を使う場合は、この local container dump ではなく managed DB / vendor-native backup を優先する

## config DB externalization を preflight する

local compose default は `db-config` container だが、current web app は `APP_CONFIG_DB_*` override を受けられる。compose topology / supported target / warning boundary の正本は [config-db-externalization.md](config-db-externalization.md) を参照する。

```bash
make config-db-preflight
```

direct CLI:

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/check_config_db_bootstrap.php --requested-by=manual
```

- migrate apply:

```bash
make db-config-migrate
```

- `ok=true` かつ `schema_current=true` を current ready と読む
- preflight は required table / required column / unexpected legacy column を確認する
- `db-config-migrate` は current `APP_CONFIG_DB` target に `docker/mariadb/config-initdb/*.sql` を順番どおり apply する
- external lane で同じ確認をしたい時は `make config-db-preflight-external-config-db` / `make db-config-migrate-external-config-db` を使う
- canonical metadata repository / admin CRUD は `config_db` を直接読む。built-in `db` は `live-schema` import source と site default DB の意味のまま残る
- host/shared env では `APP_DB_*` と `APP_CONFIG_DB_*` を分けてもよい。preflight は mismatch を warning で返すが、`config_db` schema が current なら fail しない
- base `compose.yaml` は `db-config` service を持たない。default local の `make up` は `compose.local-db-config.yaml` を重ねて `db-config` を含める
- root compose の `web-admin` / `web-lab` は `db-config` 起動順依存を持たない。external target を使う時は `make up-external-config-db` で `db-config` を起動せずに `web-admin` / `web-lab` / `db-lab` を上げられる

## tutorial sample を 1 本動かす

最初の入口は `sample/tutorials/sample01-simple-table-runtime`。

```bash
./sample/tutorials/sample01-simple-table-runtime/run.sh up
./sample/tutorials/sample01-simple-table-runtime/run.sh apply-seed
make sample01-pack-runtime-test
```

DB Access tutorial の capstone を見るなら `sample/tutorials/sample10-dbaccess-mini-crud-flow` を使う。

```bash
make sample10-pack-runtime-test
```

## full suite を回す

local で旧 stack と port が衝突しやすいので、まずは次の override 付き実行を使う。

```bash
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```

default では検証後に sample stack を `down -v` する。  
確認のために残したい時だけ `KEEP_SAMPLE_STACK_RUNNING=1 make test` を使う。

## runtime reference の状態を確認する

```bash
make mtool-runtime-reference-status REQUIRE_CURRENT=1
php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only
```

`REQUIRE_CURRENT=1` は strict check。  
durable snapshot が残っていても、`work/` 側の latest artifact history が無いと `reference-snapshot-only` 扱いで non-zero になることがある。

## latest artifact を durable reference へ promote する

```bash
make promote-runtime-reference
```

特定 artifact を指定する場合:

```bash
make promote-runtime-reference ARTIFACT_KEY=20260521-023351-d52e8c8b
```

## durable snapshot から runtime reference を restore する

```bash
make restore-runtime-reference-snapshot ARTIFACT_KEY=20260521-023351-d52e8c8b
```

current mainline の recover はこれを使う。  
`bootstrap-dbclasses*` 系は archive 済みで、通常導線には含めない。

## sample pack を追加するときに触る場所

新しい tutorial sample を追加する場合の最小セット:

- `sample/tutorials/sampleNN-<slug>/README.md`
- `sample/tutorials/sampleNN-<slug>/compose.yaml`
- `sample/tutorials/sampleNN-<slug>/run.sh`
- `sample/tutorials/sampleNN-<slug>/seed/`
- 必要なら `sample/tutorials/sampleNN-<slug>/reference/`
- `tests/Integration/SampleNN...OutputTest.php`
- `Makefile`
- `mtool/app/sample_pack_catalog.php`
- `docs/sample-tutorial-roadmap.md`
- `sample/README.md`
- `sample/tutorials/README.md`
- `tests/README.md`
- `tests/Integration/README.md`

### naming rule

- tutorial lane は `sampleNN-pack-runtime-test`
- internal pattern lane は `patternNN-output-test`
- historical `Sample9-22` / `sample9-22-output-test` 命名とは衝突させない

### pack 設計 rule

- `1 pack = 1 主テーマ`
- simple-to-complex の順番を壊さない
- `reference/` には actual output か provenance を確認できる curated source だけを置く
- current raw output は `work/` に出す

## どこまで読めばよいか迷ったとき

1. [start-here.md](start-here.md)
2. [internal/README.md](internal/README.md)
3. [current-supported-workflow.md](current-supported-workflow.md)
4. [sample-tutorial-roadmap.md](sample-tutorial-roadmap.md)
5. [../tests/README.md](../tests/README.md)
