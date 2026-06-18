# DegoDB

English companion:
DegoDB is a metadata-driven development workbench. Start with the Quickstart when you want to verify the local Docker stack before reading the deeper design documents.

Metadata-driven Development Workbench  
メタデータ駆動の開発ワークベンチ

Codename コードネーム: `mtool`  

DegoDB is a metadata-driven development workbench built around imported schemas.  
DegoDB は、既存のスキーマを取り込み、その情報を正本となるメタデータとして管理する Metadata-driven Development Workbench です。

It maintains a canonical metadata model from which Data Classes, DB Access code, APIs, and other development artifacts can be generated consistently.  
Data Class、DB Access、API、その他の開発成果物を、一貫したメタデータモデルから生成できることを目的としています。

This repository currently focuses on the workflow:  
現在の主線となるワークフローは次のとおりです。

Database Schema -> Import -> Data Class -> DB Access -> Source Output  
DB 構造 -> Import -> Data Class -> DB Access -> Source Output

Original concept note: users who only have JSON files, JSON API cache, or JSON config can use [JSON To DB Entrance / JSON から DB 設計へ入る入口](docs/json-to-db-entrance.md) as an AI-assisted preparation layer. This is documentation and design guidance, not a JSON auto-import feature.  
初期構想の補足: JSON file、JSON API cache、JSON config しかまだ持っていない利用者は、AI-assisted な準備 layer として [JSON To DB Entrance / JSON から DB 設計へ入る入口](docs/json-to-db-entrance.md) を使えます。これは documentation と設計指針であり、JSON 自動 import 機能ではありません。

## How to Read This / 文書の読み方

The documentation in this repository is intended to be read in the following three layers.  
この repo の docs は、次の 3 層で読む前提にします。

1. Entry layer / 入口 layer
   - [Quickstart / まず動かしてみる](docs/quickstart.md)
   - [Start Here / 最初の入口](docs/start-here.md)
   - [Choose Your Path / 目的別の読み方](docs/choose-your-path.md)
2. Golden path layer / ゴールデンパス layer
   - [JSON To DB Entrance / JSON から DB 設計へ入る入口](docs/json-to-db-entrance.md) optional pre-design entrance
   - [Existing DB To Output / 既存 DB から出力まで](docs/existing-db-to-output.md)
   - [Common Tasks / よく使う作業](docs/common-tasks.md)
   - [Current Supported Workflow / 現在サポートするワークフロー](docs/current-supported-workflow.md)
   - [Troubleshooting / トラブルシューティング](docs/troubleshooting.md)
3. Detail layer / 詳細 layer
   - [Concept Overview / 概念概要](docs/overview.md)
   - [Storage And State Model / 保存先と状態モデル](docs/storage-and-state-model.md)
   - [Project Metadata Bundle / プロジェクトメタデータ bundle](docs/project-metadata-bundle.md)
   - [Config DB Externalization / config DB 外部化](docs/config-db-externalization.md)
   - [Glossary / 用語集](docs/glossary.md)
   - [Sample Tutorial Roadmap / sample 学習導線](docs/sample-tutorial-roadmap.md)
   - [Study Guide / sample で学ぶ](docs/study/README.md)
   - [Internal Documentation Index / 内部ドキュメント索引](docs/internal/README.md)

Do not reconstruct the mainline by reading the detail docs first. Choose a reading order from the entry layer, understand the execution flow through the golden path layer, and then consult the detail layer.  
先に detail doc を横断して mainline を再構成するのではなく、入口 layer で読む順番を決め、golden path layer で実行の流れを掴み、その後に detail layer を参照します。

## Start Here / まず読む文書

1. [Quickstart / まず動かしてみる](docs/quickstart.md)
2. [Start Here / 最初の入口](docs/start-here.md)
3. [Choose Your Path / 目的別の読み方](docs/choose-your-path.md)
4. [JSON To DB Entrance / JSON から DB 設計へ入る入口](docs/json-to-db-entrance.md) optional
5. [Existing DB To Output / 既存 DB から出力まで](docs/existing-db-to-output.md)
6. [Common Tasks / よく使う作業](docs/common-tasks.md)
7. [Current Supported Workflow / 現在サポートするワークフロー](docs/current-supported-workflow.md)
8. [Concept Overview / 概念概要](docs/overview.md)
9. [Sample Tutorial Roadmap / sample 学習導線](docs/sample-tutorial-roadmap.md)
10. [Study Guide / sample で学ぶ](docs/study/README.md)
11. [Troubleshooting / トラブルシューティング](docs/troubleshooting.md)
12. [Storage And State Model / 保存先と状態モデル](docs/storage-and-state-model.md)
13. [Internal Documentation Index / 内部ドキュメント索引](docs/internal/README.md)

## Important Invariants / 重要な不変条件

- `mtool/` is the source of truth for the current runtime, generator, and scripts.  
  `mtool/` が current runtime / generator / script の正本です。
- `sample/tutorials/` is the user-facing tutorial lane, ordered from simple to complex.  
  `sample/tutorials/` は simple-to-complex の user-facing tutorial lane です。
- `sample/internal-patterns/` is the internal sample lane for rewrite and migration guards.  
  `sample/internal-patterns/` は rewrite / migration guard 用の internal sample lane です。
- `sample/legacy-projects/` stores representative project packs.  
  `sample/legacy-projects/` は representative project pack の置き場です。
- `tests/` contains integration, scenario, and fixture verification assets.  
  `tests/` は integration / scenario / fixture の検証資産です。
- `work/` stores disposable outputs and compare workspaces.  
  `work/` は disposable output と compare workspace の置き場です。
- Legacy full source such as `original-codes/` is host-side reference only; curated legacy reference lives under `mtool/reference/`, for example `mtool/reference/legacy-dbclasses/`.
  `original-codes/` のような旧実装全体は host-side reference only です。現在の curated legacy reference は `mtool/reference/legacy-dbclasses/` など `mtool/reference/` 配下に限定して置きます。
- The current runtime, generator, and Docker containers must not use `original-codes/` directly as input.  
  新実装の runtime / generator / Docker container は `original-codes/` を直接入力として使いません。

## Shortest Entry Path / 最短の入口

### Understand the Repository / repo を把握する

- Documentation navigator: [Documentation Index / 文書索引](docs/README.md)  
  文書ナビゲータ: [Documentation Index / 文書索引](docs/README.md)
- First hands-on run: [Quickstart / まず動かしてみる](docs/quickstart.md)
  clone 直後に 1 周だけ動かす入口: [Quickstart / まず動かしてみる](docs/quickstart.md)
- Five-minute overview: [Start Here / 最初の入口](docs/start-here.md)  
  5 分で全体を掴む入口: [Start Here / 最初の入口](docs/start-here.md)
- Goal-oriented reverse lookup: [Choose Your Path / 目的別の読み方](docs/choose-your-path.md)  
  目的別の逆引き入口: [Choose Your Path / 目的別の読み方](docs/choose-your-path.md)
- Main path from existing DB to output: [Existing DB To Output / 既存 DB から出力まで](docs/existing-db-to-output.md)  
  existing DB から output までの主導線: [Existing DB To Output / 既存 DB から出力まで](docs/existing-db-to-output.md)
- Original optional path from JSON to DB design draft: [JSON To DB Entrance / JSON から DB 設計へ入る入口](docs/json-to-db-entrance.md)  
  JSON file / JSON API cache / JSON config から DB 設計案へ入る、初期構想上の optional pre-design entrance: [JSON To DB Entrance / JSON から DB 設計へ入る入口](docs/json-to-db-entrance.md)
- Common commands: [Common Tasks / よく使う作業](docs/common-tasks.md)  
  よく使うコマンド集: [Common Tasks / よく使う作業](docs/common-tasks.md)
- Supported lane for the current mainline: [Current Supported Workflow / 現在サポートするワークフロー](docs/current-supported-workflow.md)  
  current mainline の supported lane: [Current Supported Workflow / 現在サポートするワークフロー](docs/current-supported-workflow.md)
- State and persistence map: [Storage And State Model / 保存先と状態モデル](docs/storage-and-state-model.md)  
  何がどこに残るか: [Storage And State Model / 保存先と状態モデル](docs/storage-and-state-model.md)
- Warning and error triage: [Troubleshooting / トラブルシューティング](docs/troubleshooting.md)  
  warning / error の切り分け: [Troubleshooting / トラブルシューティング](docs/troubleshooting.md)
- Sample learning path: [Sample Tutorial Roadmap / sample 学習導線](docs/sample-tutorial-roadmap.md)  
  sample 学習導線: [Sample Tutorial Roadmap / sample 学習導線](docs/sample-tutorial-roadmap.md)
- Internal contributor reference: [Internal Documentation Index / 内部ドキュメント索引](docs/internal/README.md)  
  contributor 向け内部 reference: [Internal Documentation Index / 内部ドキュメント索引](docs/internal/README.md)
- Test guide: [Test Guide / テストガイド](tests/README.md)  
  test 導線: [Test Guide / テストガイド](tests/README.md)

When in doubt, keep the current rule: `entry layer -> golden path layer -> detail layer`.  
読む順番に迷った時は、`入口 layer -> golden path layer -> detail layer` の順を崩さないのが current rule です。

### Start the Environment / 環境を起動する

```bash
make env
make up-mtool
make mtool-canonical-sync
```

`make up-mtool` uses `compose.yaml + compose.local-db-config.yaml + mtool/docker/compose/01_mtool.compose.yaml` and includes the MTOOL core seed needed by `make mtool-canonical-sync`. When using an external config DB, set `APP_CONFIG_DB_*` and run `make up-external-config-db`. After startup, use `make ps-external-config-db`, `make health-external-config-db`, and `make config-db-preflight-external-config-db` for checks. Use raw `docker compose -f compose.yaml ...` only when the external lane needs a shell or temporary stop.
`make up-mtool` は `make mtool-canonical-sync` に必要な MTOOL core seed を含めて `compose.yaml + compose.local-db-config.yaml + mtool/docker/compose/01_mtool.compose.yaml` を使います。external config DB を使う時は `APP_CONFIG_DB_*` を指定して `make up-external-config-db` を使います。起動後の確認は `make ps-external-config-db` / `make health-external-config-db` / `make config-db-preflight-external-config-db` を使います。external lane で shell や一時 stop が必要な時だけ raw `docker compose -f compose.yaml ...` を使います。

Persistence note: local quickstart stores design metadata in the local `db-config` Docker volume by default. The lightweight personal-use path is folder-only: set `APP_CONFIG_STORE_DIR=work/config-store` and DegoDB resolves the SQLite file as `APP_CONFIG_STORE_DIR/config.sqlite`. Missing or empty SQLite files are bootstrapped automatically from the current config schema. Leave that value empty for the MySQL / MariaDB server DB profile. Use `make backup-config-db-mtool` before destructive reset, or use `deploy/durable-config-db.env.example` with `make up-durable-config-db DURABLE_ENV_FILE=.env.durable` for durable/team server DB use.
永続化の注意: local quickstart の設計データは既定では local `db-config` Docker volume に保存されます。軽い個人利用では `APP_CONFIG_STORE_DIR=work/config-store` のように保存フォルダだけを指定し、`APP_CONFIG_STORE_DIR/config.sqlite` を file store として使います。SQLite ファイルが未作成または空の場合は current config schema から自動 bootstrap されます。server DB 運用ではこの値を空にし、MySQL / MariaDB profile を維持します。破壊的な reset 前には `make backup-config-db-mtool` を使い、チーム利用・server DB 運用では `deploy/durable-config-db.env.example` と `make up-durable-config-db DURABLE_ENV_FILE=.env.durable` を使います。

`make up-mtool` also shows the URL for `lab-db-ui` in addition to admin and lab.
`make up-mtool` は admin / lab に加えて `lab-db-ui` の URL も表示します。

`lab-db-ui` is a lightweight UI for editing `db-lab` in a browser. After changing the schema, admin can import it into canonical metadata from the `lab-live-schema` source.  
`lab-db-ui` は `db-lab` をブラウザで編集するための軽量 UI で、schema を変えた後は admin 側の `lab-live-schema` source から canonical metadata へ取り込めます。

### Try One Tutorial Sample / tutorial sample を 1 本触る

- Entry sample: `sample/tutorials/sample01-simple-table-runtime`  
  入口 sample: `sample/tutorials/sample01-simple-table-runtime`
- Current tutorial lane: `sample01` through `sample17`
  current tutorial lane: `sample01` から `sample17`
- Latest sample: `sample/tutorials/sample17-multi-output-project`
  latest sample: `sample/tutorials/sample17-multi-output-project`

```bash
make sample01-pack-runtime-test
make sample17-pack-runtime-test
```

See [Study Guide / sample で学ぶ](docs/study/README.md) for the hands-on reading order, and [Sample Tutorial Roadmap / sample 学習導線](docs/sample-tutorial-roadmap.md) for the sample catalog and role split.
sample を教材として読む順番は [Study Guide / sample で学ぶ](docs/study/README.md)、sample の一覧と役割分担は [Sample Tutorial Roadmap / sample 学習導線](docs/sample-tutorial-roadmap.md) を参照してください。

### Current Verified Full Suite / 現在の検証済み full suite

Because the old stack can conflict with local ports, the full suite uses the following override as the baseline.  
local で旧 stack と port 衝突することがあるため、full suite は次の override 付き実行を基準にします。

```bash
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```

## Directory Guide / ディレクトリの見方

- `mtool/`
  - Current implementation.  
    現行実装
- `sample/`
  - Tutorial, internal pattern, and representative project packs.  
    tutorial / internal pattern / representative project pack の置き場
- `tests/`
  - PHPUnit integration tests and scenarios.  
    PHPUnit integration test と scenario の置き場
- `docs/`
  - Top-level docs are date-less permanent documents for external users.  
    top-level は外部ユーザ向けの date-less な恒久文書
  - Internal contributor references are under [Internal Documentation Index / 内部ドキュメント索引](docs/internal/README.md).  
    [Internal Documentation Index / 内部ドキュメント索引](docs/internal/README.md) 配下に contributor 向け内部 reference をまとめる
  - `docs/reports/` stores history, progress, and handoff records.  
    `docs/reports/` は履歴、progress、handoff の保存先
- `work/`
  - Disposable runtime output, artifact history, and compare workspace.  
    disposable runtime output、artifact history、compare workspace
- `mtool/reference/legacy-dbclasses/`
  - Curated legacy DB class reference used for limited comparison and migration context.
    限定された比較・移行文脈で使う curated legacy DB class reference
- `original-codes/`
  - Host-side reference only when a full legacy source snapshot is present; not a current runtime input.
    旧実装全体の snapshot がある場合も host-side reference only であり、current runtime input ではない

## Deep Dives / 深掘り先

- Tool concept model: [Concept Overview / 概念概要](docs/overview.md)  
  ツールの概念モデル: [Concept Overview / 概念概要](docs/overview.md)
- Main path from existing DB to output: [Existing DB To Output / 既存 DB から出力まで](docs/existing-db-to-output.md)  
  existing DB から output までの主導線: [Existing DB To Output / 既存 DB から出力まで](docs/existing-db-to-output.md)
- State and persistence map: [Storage And State Model / 保存先と状態モデル](docs/storage-and-state-model.md)  
  state / persistence map: [Storage And State Model / 保存先と状態モデル](docs/storage-and-state-model.md)
- Current workflow: [Current Supported Workflow / 現在サポートするワークフロー](docs/current-supported-workflow.md)  
  current workflow: [Current Supported Workflow / 現在サポートするワークフロー](docs/current-supported-workflow.md)
- Common task collection: [Common Tasks / よく使う作業](docs/common-tasks.md)  
  common task 集: [Common Tasks / よく使う作業](docs/common-tasks.md)
- Canonical metadata bundle: [Project Metadata Bundle / プロジェクトメタデータ bundle](docs/project-metadata-bundle.md)  
  canonical metadata bundle: [Project Metadata Bundle / プロジェクトメタデータ bundle](docs/project-metadata-bundle.md)
- Config DB externalization: [Config DB Externalization / config DB 外部化](docs/config-db-externalization.md)  
  config DB externalization: [Config DB Externalization / config DB 外部化](docs/config-db-externalization.md)
- Troubleshooting: [Troubleshooting / トラブルシューティング](docs/troubleshooting.md)  
  troubleshooting: [Troubleshooting / トラブルシューティング](docs/troubleshooting.md)
- Glossary: [Glossary / 用語集](docs/glossary.md)  
  用語集: [Glossary / 用語集](docs/glossary.md)
- Sample learning path: [Sample Tutorial Roadmap / sample 学習導線](docs/sample-tutorial-roadmap.md)  
  sample 学習導線: [Sample Tutorial Roadmap / sample 学習導線](docs/sample-tutorial-roadmap.md)
- Internal implementation, architecture, and migration map: [Internal Documentation Index / 内部ドキュメント索引](docs/internal/README.md)  
  実装内部 / architecture / migration map: [Internal Documentation Index / 内部ドキュメント索引](docs/internal/README.md)
- Release history: [History](HISTORY.md)
  release history: [History](HISTORY.md)
- History and handoff records: [2026 Reports / 2026 年の履歴](docs/reports/2026/README.md)  
  履歴・handoff: [2026 Reports / 2026 年の履歴](docs/reports/2026/README.md)

Read `docs/reports/` only when history is needed. For everyday source of truth, prefer the date-less docs under `docs/`.  
履歴が必要なときだけ `docs/reports/` を読み、普段の source of truth は date-less な `docs/` 側を優先してください。
