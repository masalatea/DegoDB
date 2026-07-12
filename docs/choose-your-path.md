# Choose Your Path / 目的別の読み方

English companion:
This guide is a reverse index from goals to the right permanent docs and first commands. Use it when you know what you want to do but do not yet know which lane, document, or bootstrap step should be treated as current.

この文書は、「今やりたいこと」から current な入口を選ぶための逆引きガイドです。  
細かい背景や履歴を先に追うのではなく、date-less な恒久文書と最初のコマンドをすぐ見つけることだけに絞ります。  
`start-here` と合わせて入口 layer を構成し、ここでゴールを決めたら golden path layer へ進み、detail layer は必要な時だけ開きます。

## 3 層で迷わない読み方

1. 入口 layer
   - [quickstart.md](quickstart.md)
   - [start-here.md](start-here.md)
   - [choose-your-path.md](choose-your-path.md)
2. golden path layer
   - [JSON To DB Entrance / JSON から DB 設計へ入る入口](json-to-db-entrance.md) optional pre-design entrance
   - [existing-db-to-output.md](existing-db-to-output.md)
   - [common-tasks.md](common-tasks.md)
   - [goal-based-help-and-wrapper-cli-roadmap.md](goal-based-help-and-wrapper-cli-roadmap.md)
   - [current-supported-workflow.md](current-supported-workflow.md)
   - [troubleshooting.md](troubleshooting.md)
3. detail layer
   - [overview.md](overview.md)
   - [storage-and-state-model.md](storage-and-state-model.md)
   - [project-metadata-bundle.md](project-metadata-bundle.md)
   - [config-db-externalization.md](config-db-externalization.md)
   - [sample-tutorial-roadmap.md](sample-tutorial-roadmap.md)
   - [study/README.md](study/README.md)
   - [internal/README.md](internal/README.md)

existing DB を current repo に自然につなぎ、canonical metadata 永続化から output verify まで進める時は、入口 layer の次に golden path layer を優先します。

## 先に知っておくこと

- `original-codes/` は host-side reference only。runtime input には戻さない
- curated legacy reference は `mtool/reference/legacy-dbclasses/` など `mtool/reference/` 配下に限定して置く
- current mainline は `DB 構造 -> import -> Data Class -> DB Access -> Source Output`
- no-code は current mainline の上位 layer。DB metadata、managed operation、Source Output、publish approval を通して preview へ進む
- JSON file / JSON API cache / JSON config から始める場合は、初期構想に含まれる optional pre-design entrance として [json-to-db-entrance.md](json-to-db-entrance.md) で AI-readable な design draft を作ってから mainline に入る
- default local stack は `compose.yaml + compose.local-db-config.yaml` を使う
- external config DB を使う時だけ `APP_CONFIG_DB_*` と `make up-external-config-db` を使う
- local stack の設計データは `db-config` Docker volume に保存される。継続利用では backup、本気利用では durable env file + external config DB を使う
- tutorial lane の current は `sample01` から `sample17`
- top-level `docs/` は外部ユーザ向け導線で、実装内部は [internal/README.md](internal/README.md) から辿る
- `docs/reports/` は history / handoff 用であり、current spec の正本ではない

## ゴールから選ぶ

| ゴール | まず読む | 最初に打つ |
| --- | --- | --- |
| clone 直後にまず 1 周動かす | [quickstart.md](quickstart.md), [start-here.md](start-here.md) | `make env`<br>`make up-mtool`<br>`make mtool-canonical-sync`<br>`make sample01-pack-runtime-test` |
| repo 全体像を掴む | [../README.md](../README.md), [start-here.md](start-here.md), [overview.md](overview.md) | `make help` |
| 目的から command を探す / 将来 wrapper CLI の形を確認する | [goal-based-help-and-wrapper-cli-roadmap.md](goal-based-help-and-wrapper-cli-roadmap.md), [common-tasks.md](common-tasks.md) | `make help` |
| 現在の計画と残件を確認する | [current-plans.md](current-plans.md), [../examples/README.md](../examples/README.md), [reports/2026/2026-0621-plan-inventory.md](reports/2026/2026-0621-plan-inventory.md) | command なし。active / TODO / parked を確認する |
| ユースケースから読む | [use-cases.md](use-cases.md), [compatibility-and-output-support.md](compatibility-and-output-support.md), [../examples/README.md](../examples/README.md), [existing-db-to-output.md](existing-db-to-output.md) | command なし。用途と current examples を確認する |
| JSON file / JSON API cache / JSON config から DB 管理へ移行したい | [json-to-db-entrance.md](json-to-db-entrance.md), [internal/json-to-db-ai-contract.md](internal/json-to-db-ai-contract.md), [overview.md](overview.md), [existing-db-to-output.md](existing-db-to-output.md) | command なし。初期構想上の pre-design step として JSON sample と現在の処理説明から design draft を作る |
| Codex / Claude と Mtool 用 workspace を安全に初期化したい | [ai-workspace-onboarding-command-guide.md](ai-workspace-onboarding-command-guide.md), [ai-task-packet-workflow.md](ai-task-packet-workflow.md), [storage-and-state-model.md](storage-and-state-model.md) | `php mtool/scripts/init_ai_workspace.php --project-root=/path/to/user-project --json` |
| existing DB に接続して canonical metadata を永続化し、設計と output まで進める | [existing-db-to-output.md](existing-db-to-output.md), [common-tasks.md](common-tasks.md), [storage-and-state-model.md](storage-and-state-model.md), [troubleshooting.md](troubleshooting.md) | `make env`<br>`make up`<br>`make config-db-preflight` |
| no-code preview をまず触る | [no-code-tryout.md](no-code-tryout.md), [../sample/tutorials/sample28-no-code-data-app-mvp/README.md](../sample/tutorials/sample28-no-code-data-app-mvp/README.md) | `./sample/tutorials/sample28-no-code-data-app-mvp/run.sh up`<br>`./sample/tutorials/sample28-no-code-data-app-mvp/run.sh apply-seed` |
| 共同作業の handoff から resume する | [existing-db-to-output.md#e10-handoff-payload](existing-db-to-output.md#e10-handoff-payload), [storage-and-state-model.md#s1-resume-checkpoints](storage-and-state-model.md#s1-resume-checkpoints), [internal/README.md](internal/README.md), [troubleshooting.md](troubleshooting.md) | `make help | sed -n '1,80p'`<br>`make config-db-preflight`<br>`php mtool/scripts/show_runtime_reference_status.php --require-current` |
| local default stack を起動する | [start-here.md](start-here.md), [common-tasks.md](common-tasks.md) | `make env`<br>`make up` |
| external config DB で起動する | [config-db-externalization.md](config-db-externalization.md), [common-tasks.md](common-tasks.md) | `APP_CONFIG_DB_HOST=... make up-external-config-db`<br>`make ps-external-config-db`<br>`make health-external-config-db` |
| 継続利用・本気利用の永続化を設定する | [config-db-externalization.md](config-db-externalization.md), [common-tasks.md](common-tasks.md), [storage-and-state-model.md](storage-and-state-model.md) | `cp deploy/durable-config-db.env.example .env.durable`<br>`make up-durable-config-db DURABLE_ENV_FILE=.env.durable` |
| `MTOOL` の canonical import / sync を流す | [current-supported-workflow.md](current-supported-workflow.md), [common-tasks.md](common-tasks.md) | `make up-mtool`<br>`make mtool-canonical-sync` |
| canonical metadata bundle を export / import preview する | [project-metadata-bundle.md](project-metadata-bundle.md), [common-tasks.md](common-tasks.md) | `docker compose exec -T web-admin php /var/www/mtool/scripts/export_project_metadata.php ...`<br>`docker compose exec -T web-admin php /var/www/mtool/scripts/import_project_metadata.php ... --mode=preview` |
| tutorial sample を教材として順に学ぶ | [study/README.md](study/README.md), [sample-tutorial-roadmap.md](sample-tutorial-roadmap.md), [../sample/tutorials/README.md](../sample/tutorials/README.md) | `make sample01-pack-runtime-test` |
| green state を確認する | [common-tasks.md](common-tasks.md), [../tests/README.md](../tests/README.md) | `make config-db-preflight`<br>`ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test` |
| external named source から Lab Swagger まで確認する | [existing-db-to-output.md](existing-db-to-output.md), [current-supported-workflow.md](current-supported-workflow.md), [common-tasks.md](common-tasks.md) | `make mtool-external-source-lab-smoke`<br>`make mtool-external-source-lab-browser-smoke` |
| runtime reference の状態や emitted layout を確認する | [current-supported-workflow.md](current-supported-workflow.md), [internal/README.md](internal/README.md) | `php mtool/scripts/show_runtime_reference_status.php --require-current`<br>`php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only` |
| 実装 boundary や migration map を確認する | [internal/README.md](internal/README.md), [overview.md](overview.md) | `rg --files docs/internal` |
| warning / error の意味を切り分ける | [troubleshooting.md](troubleshooting.md), [common-tasks.md](common-tasks.md) | `make config-db-preflight`<br>`php mtool/scripts/show_runtime_reference_status.php --require-current` |

## 典型的な 3 ルート

existing DB を named source としてつなぎ、canonical metadata 永続化から output verify まで 1 本で辿る時は [existing-db-to-output.md](existing-db-to-output.md) を正本にします。  
JSON しかまだ無い場合は [json-to-db-entrance.md](json-to-db-entrance.md) を先に使い、AI が `JSON to DB Design Draft` を作ってから table / column / relation candidate を DegoDB の mainline に渡します。これは runtime 機能ではなく、初期構想に含まれる設計準備 step です。  
no-code preview を見たい場合は [no-code-tryout.md](no-code-tryout.md) から sample28 へ進みます。このルートは DB tooling と別物ではなく、canonical metadata / Source Output / publish candidate approval の上に載る上位 layer です。
この文書では「最初にどこを開くか」と「最初のコマンド」だけを残します。

途中再開なら [existing-db-to-output.md#e10-handoff-payload](existing-db-to-output.md#e10-handoff-payload) で payload を確認し、[storage-and-state-model.md#s1-resume-checkpoints](storage-and-state-model.md#s1-resume-checkpoints) で `config_db` / artifact 側の残存 state を確認してから、必要な stage へ戻ります。内部 contract が必要になった時だけ [internal/README.md](internal/README.md) へ進みます。

### 新しい contributor が local で最初の 1 周をする

```bash
make help | sed -n '1,80p'
make env
make up-mtool
make mtool-canonical-sync
make sample01-pack-runtime-test
```

まず end-to-end の流れを 1 回通したいだけなら `sample01` で十分です。  
DB Access の小さい CRUD flow まで見たい時は `make sample10-pack-runtime-test`、HTML Source Output まで見たい時は `make sample11-pack-runtime-test`、external DB source import まで見たい時は `make sample12-pack-runtime-test`、OpenAPI API surface まで見たい時は `make sample13-pack-runtime-test`、custom proxy runtime まで見たい時は `make sample14-pack-runtime-test`、project metadata bundle まで見たい時は `make sample15-pack-runtime-test`、authenticated proxy まで見たい時は `make sample16-pack-runtime-test`、multi-output capstone まで見たい時は `make sample17-pack-runtime-test`、instruction-driven demo まで見たい時は `make sample18-pack-runtime-test` へ進みます。`sample18` の web-lab page まで触る場合は `make sample18-http-runtime-smoke` を使います。

### shared / external config DB につなぐ

```bash
APP_CONFIG_DB_HOST=external-db.example \
APP_CONFIG_DB_PORT=3306 \
APP_CONFIG_DB_NAME=config_app \
APP_CONFIG_DB_USER=config_app \
APP_CONFIG_DB_PASSWORD=secret \
make up-external-config-db

make ps-external-config-db
make health-external-config-db
make config-db-preflight-external-config-db
```

external lane で shell や一時 stop が必要な時だけ raw base compose を使います。

```bash
docker compose -f compose.yaml exec web-admin bash
COMPOSE_PROFILES=lab-db-ui docker compose -f compose.yaml stop
```

### 変更前後で green state を確認する

```bash
make config-db-preflight
make sample-pack-compose-smoke
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
php mtool/scripts/show_runtime_reference_status.php --require-current
php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only
```

`make test` だけでは「runtime reference が今の promoted state とどうずれているか」は分からないので、最後の 2 本もセットで見ます。

## 迷った時の優先順位

1. まず [start-here.md](start-here.md) と [overview.md](overview.md) を読む
2. existing DB をつなぐ主導線なら [existing-db-to-output.md](existing-db-to-output.md) と [storage-and-state-model.md](storage-and-state-model.md) を先に読む
3. JSON から始めるなら [json-to-db-entrance.md](json-to-db-entrance.md) と [internal/json-to-db-ai-contract.md](internal/json-to-db-ai-contract.md) を先に読む
4. 目的から command を探す時は [goal-based-help-and-wrapper-cli-roadmap.md](goal-based-help-and-wrapper-cli-roadmap.md) を見てから [common-tasks.md](common-tasks.md) で current command を確認する
5. 学習目的なら [study/README.md](study/README.md) に進み、`sample01` から順に触る
6. architecture / migration / contributor contract が必要な時だけ [internal/README.md](internal/README.md) を開く
7. 履歴や判断経緯が必要な時だけ [reports/2026/README.md](reports/2026/README.md) を開く

## 使わない導線

- `original-codes/` を Docker runtime input に戻す
- archived helper や旧 alias target を current mainline として扱う
- external lane のために local overlay lane の target を混ぜる
- `openapi.json` を public static file や raw alias route として配る前提で進める
