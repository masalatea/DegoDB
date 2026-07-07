# DegoDB

English companion:
DegoDB is a database-first and existing-database-first development toolkit. Start with the Quickstart when you want to verify the local Docker stack before reading the deeper design documents. / DegoDB は、データベース起点・既存データベース起点の開発ツールキットです。手元の Docker 環境を先に確認したい場合は、詳細な設計文書へ進む前に Quickstart から始めます。

DegoDB turns database schemas into canonical metadata, generated code, API surfaces, and verified source output artifacts. / DegoDB は、データベーススキーマを正本メタデータ、生成コード、API 面、検証済み Source Output 成果物へ変換します。

DegoDB helps teams start from the database they already have, or the schema they are designing, then generate aligned development artifacts that humans can review and maintain. / DegoDB は、既存データベースまたは設計中のスキーマを起点に、人間が確認、保守できる一貫した開発成果物を生成するための支援を行います。

Database-first Development Toolkit
データベース起点の開発ツールキット

Codename コードネーム: `mtool`  

DegoDB is a metadata-driven development toolkit built around database schemas.
DegoDB は、データベーススキーマを起点に、その情報を正本となるメタデータとして管理する、メタデータ駆動の開発ツールキットです。

It maintains a canonical metadata model from which Data Classes, DB Access code, APIs, and other supported development artifacts can be generated consistently.
データクラス、データベースアクセスコード、API、その他の対応済み開発成果物を、一貫したメタデータモデルから生成できることを目的としています。

This repository currently focuses on the workflow:  
現在の主線となるワークフローは次のとおりです。

Database Schema -> Import -> Data Class -> DB Access -> Source Output  
DB 構造 -> Import -> Data Class -> DB Access -> Source Output

## Two-Layer Model / 二層モデル

DegoDB's no-code layer is built on the database-first foundation. It does not replace the core tooling. Database schemas are imported into canonical metadata first, then generated artifacts, shared contracts, managed operations, Source Output review, and approval workflow provide the base for no-code previews and delivery.

DegoDB の no-code layer は database-first の基盤の上に載ります。core tooling を置き換えるものではありません。まず DB schema を正本 metadata に取り込み、その上で generated artifacts、shared contract、managed operation、Source Output review、approval workflow が no-code preview と delivery の土台になります。

This two-layer shape is intentional: the no-code preview is useful because it inherits the reviewed database metadata, generated Data Class / DB Access boundary, managed-operation intent shape, and publish candidate approval record. It is not a standalone visual builder with a hidden data model.

この二層構造は意図的です。no-code preview は、review 済みの database metadata、生成された Data Class / DB Access 境界、managed-operation intent、publish candidate approval record を引き継ぐからこそ有用です。隠れた data model を持つ単独の画面ビルダーではありません。

Foundation / 基盤:

Database Schema -> Canonical Metadata -> Data Class / DB Access -> Source Output

No-code layer / no-code 層:

Canonical Metadata -> Managed Operation -> No-Code Runtime -> Publish Candidate -> Public Preview

Current no-code capability boundary / 現在の no-code 対応範囲:

- Generated Web runtime previews can show list/detail/form screens from canonical metadata. / 生成 Web runtime preview は canonical metadata から list / detail / form screen を表示できます。
- Public runtime delivery uses reviewed `NO-CODE-RUNTIME` Source Output artifacts, publish candidates, approval, current revision selection, and custom aliases. / public runtime delivery は review 済み `NO-CODE-RUNTIME` Source Output artifact、publish candidate、approval、current revision selection、custom alias を使います。
- Artifact-key preview URLs remain static for immutable artifact inspection; authenticated current and alias preview URLs can fetch read-only live runtime data through versioned `runtime-data.json`. / artifact-key preview URL は immutable artifact inspection 用に static のままです。authenticated current / alias preview URL は versioned `runtime-data.json` 経由で read-only live runtime data を取得できます。
- Runtime submit uses the managed-operation sync outbox as the production-oriented default. / runtime submit は production-oriented default として managed-operation sync outbox を使います。
- Synchronous processing is demo-only and opt-in; it requires the runtime demo env gate and an explicit request flag. / synchronous processing は demo 専用の opt-in であり、runtime demo env gate と explicit request flag が必要です。

Original concept note: users who only have JSON files, JSON API cache, or JSON config can use [JSON To DB Entrance](docs/json-to-db-entrance.md) as an AI-assisted preparation layer. This is documentation and design guidance, not a JSON auto-import feature.
初期構想の補足: JSON ファイル、JSON API キャッシュ、JSON 設定しかまだ持っていない利用者は、AI 支援の準備レイヤーとして [JSON から DB 設計へ入る入口](docs/json-to-db-entrance.md) を使えます。これはドキュメントと設計指針であり、JSON 自動取り込み機能ではありません。

## Core Capabilities / 主要機能

- Import database schemas into canonical metadata / データベーススキーマを正本メタデータへ取り込む
- Generate Data Classes / データクラスを生成する
- Generate DB Access layers / データベースアクセス層を生成する
- Generate Source Output artifacts / ソース出力成果物を生成する
- Generate OpenAPI / API surfaces / OpenAPI や API 面を生成する
- Export and import project metadata bundles / プロジェクトメタデータ一式を書き出し、取り込む
- Keep generated code, API definitions, and documentation aligned / 生成コード、API 定義、ドキュメントの対応関係を保つ
- Support repeatable verification through tutorial samples and reference outputs / チュートリアルサンプルと参照出力による再現可能な検証を支援する

## Use Cases / ユースケース

### Database-first development / データベース起点開発

- Start a new application from a database-first schema / データベース起点のスキーマから新しいアプリケーションを始める
- Generate application code from an existing production database / 既存の本番データベースからアプリケーションコードを生成する
- Generate API surfaces from database-backed metadata / データベースに基づくメタデータから API 面を生成する
- Build internal tools around existing business data / 既存の業務データを中心に社内向けツールを構築する

### Legacy modernization / レガシーシステムの現代化

- Modernize a legacy database-backed application / レガシーデータベースを使うアプリケーションを現代化する
- Document an undocumented or messy database / 文書化されていない、または整理されていないデータベースを文書化する
- Help teams understand schema relationships before changing code / コードを変更する前にスキーマ上の関係を理解できるようにする
- Compare generated artifacts during migration work / 移行作業中に生成成果物を比較する

### Consulting and implementation support / コンサルティング・実装支援

- Prepare technical handoff material for consulting or implementation projects / コンサルティングや実装支援向けの技術引き継ぎ資料を準備する
- Create repeatable schema documentation for client systems / 顧客システム向けに再現可能なスキーマドキュメントを作る

### No-code on database metadata / DB metadata の上に載る no-code

- Generate no-code list/detail/form previews from canonical metadata / 正本 metadata から no-code の list/detail/form preview を生成する
- Review generated runtime artifacts before public preview / public preview の前に生成 runtime artifact を review する
- Approve publish candidates and expose current/alias preview URLs / publish candidate を承認し、current / alias preview URL を公開する
- Explore authenticated current/alias previews with read-only live runtime data while keeping artifact-key previews immutable / artifact-key preview を immutable に保ったまま、authenticated current / alias preview で read-only live runtime data を確認する
- Keep no-code output inspectable and regeneratable through Source Output artifacts / no-code output を Source Output artifact として inspect / regenerate 可能に保つ

## How to Read This / 文書の読み方

The documentation in this repository is intended to be read in the following three layers.  
この repo の docs は、次の 3 層で読む前提にします。

1. Entry layer / 入口 layer
   - [Quickstart / まず動かしてみる](docs/quickstart.md)
   - [No-Code Tryout / no-code をまず試す](docs/no-code-tryout.md)
   - [Start Here / 最初の入口](docs/start-here.md)
   - [Choose Your Path / 目的別の読み方](docs/choose-your-path.md)
2. Golden path layer / ゴールデンパス layer
   - [JSON To DB Entrance / JSON から DB 設計へ入る入口](docs/json-to-db-entrance.md) optional pre-design entrance / 任意の設計準備入口
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
4. [Current Plans / 現在の計画](docs/current-plans.md)
5. [Use Cases / ユースケース](docs/use-cases.md)
6. [Examples / 例の索引](examples/README.md)
7. [JSON To DB Entrance / JSON から DB 設計へ入る入口](docs/json-to-db-entrance.md) optional / 任意
8. [Existing DB To Output / 既存 DB から出力まで](docs/existing-db-to-output.md)
9. [Common Tasks / よく使う作業](docs/common-tasks.md)
10. [Current Supported Workflow / 現在サポートするワークフロー](docs/current-supported-workflow.md)
11. [Concept Overview / 概念概要](docs/overview.md)
12. [Sample Tutorial Roadmap / sample 学習導線](docs/sample-tutorial-roadmap.md)
13. [Study Guide / sample で学ぶ](docs/study/README.md)
14. [Troubleshooting / トラブルシューティング](docs/troubleshooting.md)
15. [Storage And State Model / 保存先と状態モデル](docs/storage-and-state-model.md)
16. [Internal Documentation Index / 内部ドキュメント索引](docs/internal/README.md)

## Important Invariants / 重要な不変条件

- `mtool/` is the source of truth for the current runtime, generator, and scripts. / `mtool/` は、現行ランタイム、生成器、スクリプトの正本です。
- `sample/tutorials/` is the user-facing tutorial lane, ordered from simple to complex. / `sample/tutorials/` は、簡単な例から複雑な例へ順に並べる利用者向けチュートリアル置き場です。
- `sample/internal-patterns/` is the internal sample lane for rewrite and migration guards. / `sample/internal-patterns/` は、書き換えや移行時の崩れを検知するための内部向けサンプル置き場です。
- `sample/legacy-projects/` stores representative project packs. / `sample/legacy-projects/` は、代表的なプロジェクトパックの置き場です。
- `tests/` contains integration, scenario, and fixture verification assets. / `tests/` には、結合テスト、シナリオ、検証用 fixture を置きます。
- `work/` stores disposable outputs and compare workspaces. / `work/` は、一時的な出力や比較作業用の置き場です。
- Curated legacy references live under `mtool/reference/`: generated DB class references under `mtool/reference/legacy-dbclasses/`, legacy mtool build logic under `mtool/reference/legacy-mtool-build/`, and legacy templates under `mtool/reference/legacy-mtool-templates/`. / 整理済みの旧実装参照は `mtool/reference/` 配下に置きます。旧生成 DB class は `mtool/reference/legacy-dbclasses/`、旧 mtool build ロジックは `mtool/reference/legacy-mtool-build/`、旧テンプレートは `mtool/reference/legacy-mtool-templates/` に分けます。
- The current runtime, generator, and Docker containers must not use curated legacy reference directories directly as runtime input. / 現行ランタイム、生成器、Docker コンテナは、整理済み legacy reference directory を直接の runtime input として使いません。

## Shortest Entry Path / 最短の入口

### Understand the Repository / repo を把握する

- Documentation navigator / 文書ナビゲータ: [Documentation Index / 文書索引](docs/README.md)
- First hands-on run / clone 直後に 1 周だけ動かす入口: [Quickstart / まず動かしてみる](docs/quickstart.md)
- Five-minute overview / 5 分で全体を掴む入口: [Start Here / 最初の入口](docs/start-here.md)
- Goal-oriented reverse lookup / 目的別の逆引き入口: [Choose Your Path / 目的別の読み方](docs/choose-your-path.md)
- Main path from existing DB to output / 既存 DB から出力までの主導線: [Existing DB To Output / 既存 DB から出力まで](docs/existing-db-to-output.md)
- Original optional path from JSON to DB design draft / JSON から DB 設計案へ進む任意の入口: [JSON To DB Entrance / JSON から DB 設計へ入る入口](docs/json-to-db-entrance.md)
- Common commands / よく使うコマンド集: [Common Tasks / よく使う作業](docs/common-tasks.md)
- Supported lane for the current mainline / 現在の主線でサポートする導線: [Current Supported Workflow / 現在サポートするワークフロー](docs/current-supported-workflow.md)
- State and persistence map / 何がどこに残るかを見る地図: [Storage And State Model / 保存先と状態モデル](docs/storage-and-state-model.md)
- Warning and error triage / 警告やエラーの切り分け: [Troubleshooting / トラブルシューティング](docs/troubleshooting.md)
- Sample learning path / サンプル学習導線: [Sample Tutorial Roadmap / sample 学習導線](docs/sample-tutorial-roadmap.md)
- Internal contributor reference / 開発者向け内部資料: [Internal Documentation Index / 内部ドキュメント索引](docs/internal/README.md)
- Test guide / テスト導線: [Test Guide / テストガイド](tests/README.md)

When in doubt, keep the current rule: `entry layer -> golden path layer -> detail layer`.  
読む順番に迷った時は、`入口 layer -> golden path layer -> detail layer` の順を崩さないのが現在の基本ルールです。

### Start the Environment / 環境を起動する

```bash
make env
make up-mtool
make mtool-canonical-sync
```

Boot profile / 起動構成:

- `make up-mtool` uses `compose.yaml + compose.local-db-config.yaml + mtool/docker/compose/01_mtool.compose.yaml` and includes the MTOOL core seed needed by `make mtool-canonical-sync`. / `make up-mtool` は `make mtool-canonical-sync` に必要な MTOOL コア seed を含み、`compose.yaml + compose.local-db-config.yaml + mtool/docker/compose/01_mtool.compose.yaml` を使います。
- For an external config DB, set `APP_CONFIG_DB_*` and run `make up-external-config-db`. / 外部の設定 DB を使う場合は、`APP_CONFIG_DB_*` を指定して `make up-external-config-db` を実行します。
- After startup, check the external lane with `make ps-external-config-db`, `make health-external-config-db`, and `make config-db-preflight-external-config-db`. / 起動後の外部導線確認には、`make ps-external-config-db`、`make health-external-config-db`、`make config-db-preflight-external-config-db` を使います。
- Use raw `docker compose -f compose.yaml ...` only when the external lane needs a shell or temporary stop. / 外部導線でシェル操作や一時停止が必要な場合だけ、直接 `docker compose -f compose.yaml ...` を使います。

Persistence note / 永続化の注意:

- Local quickstart stores design metadata in the local `db-config` Docker volume by default. / ローカルの quickstart では、設計メタデータは既定でローカルの `db-config` Docker volume に保存されます。
- For lightweight personal use, set only the folder path, such as `APP_CONFIG_STORE_DIR=work/config-store`; DegoDB resolves the SQLite file as `APP_CONFIG_STORE_DIR/config.sqlite`. / 軽い個人利用では、`APP_CONFIG_STORE_DIR=work/config-store` のように保存フォルダだけを指定します。DegoDB は SQLite ファイルを `APP_CONFIG_STORE_DIR/config.sqlite` として扱います。
- Missing or empty SQLite files are bootstrapped automatically from the current config schema. / SQLite ファイルが未作成または空の場合は、現在の設定スキーマから自動で初期化されます。
- Leave `APP_CONFIG_STORE_DIR` empty for the MySQL / MariaDB server DB profile. / MySQL / MariaDB のサーバー DB 構成を使う場合は、`APP_CONFIG_STORE_DIR` を空にします。
- Before a destructive reset, use `make backup-config-db-mtool`. / 破壊的なリセットの前には、`make backup-config-db-mtool` でバックアップします。
- For durable or team server DB use, start from `deploy/durable-config-db.env.example` and run `make up-durable-config-db DURABLE_ENV_FILE=.env.durable`. / 継続利用やチーム向けのサーバー DB 運用では、`deploy/durable-config-db.env.example` を元にして、`make up-durable-config-db DURABLE_ENV_FILE=.env.durable` を使います。

Lab DB UI / Lab DB UI:

- `make up-mtool` also shows the URL for `lab-db-ui` in addition to admin and lab. / `make up-mtool` は admin / lab に加えて `lab-db-ui` の URL も表示します。
- `lab-db-ui` is a lightweight UI for editing `db-lab` in a browser. / `lab-db-ui` は、ブラウザで `db-lab` を編集するための軽量 UI です。
- After changing the schema, admin can import it into canonical metadata from the `lab-live-schema` source. / スキーマを変更した後は、admin 側で `lab-live-schema` source から正本メタデータへ取り込めます。

### Try One Tutorial Sample / tutorial sample を 1 本触る

- Entry sample / 入口サンプル: `sample/tutorials/sample01-simple-table-runtime`
- Current tutorial lane / 現在のチュートリアル導線: `sample01` through `sample17` / `sample01` から `sample17`
- Latest sample / 最新サンプル: `sample/tutorials/sample17-multi-output-project`

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
  - Current implementation / 現行実装
- `sample/`
  - Tutorial, internal pattern, and representative project packs / チュートリアル、内部検証パターン、代表的なプロジェクトパックの置き場
- `examples/`
  - Scenario-oriented sales and modernization examples / 営業・現代化説明向けのシナリオ型の例の置き場
- `tests/`
  - PHPUnit integration tests and scenarios / PHPUnit の結合テストとシナリオの置き場
- `docs/`
  - Top-level docs are date-less permanent documents for external users / 直下の文書は、外部利用者向けの日付なし恒久文書
  - Internal contributor references live under [Internal Documentation Index / 内部ドキュメント索引](docs/internal/README.md) / 開発者向け内部資料は [Internal Documentation Index / 内部ドキュメント索引](docs/internal/README.md) 配下に置く
  - `docs/reports/` stores history, progress, and handoff records / `docs/reports/` は履歴、進捗、引き継ぎ記録の保存先
- `work/`
  - Disposable runtime output, artifact history, and compare workspace / 一時的な実行出力、成果物履歴、比較作業用の置き場
- `mtool/reference/legacy-dbclasses/`
  - Curated legacy DB class reference used for limited comparison and migration context / 限定的な比較や移行確認に使う、整理済みの旧 DB クラス参照
- `mtool/reference/legacy-mtool-build/`
  - Curated legacy mtool build logic reference used for output-language and generation-path inspection / 出力言語や生成経路を確認するための、整理済み旧 mtool build ロジック参照
- `mtool/reference/legacy-mtool-templates/`
  - Curated legacy template and project-setting reference used for output support inspection / 出力対応範囲を確認するための、整理済み旧テンプレートと project setting 参照

## Deep Dives / 深掘り先

Concept and workflow / 概念と主導線:

- Tool concept model / ツールの概念モデル: [Concept Overview / 概念概要](docs/overview.md)
- Main path from existing DB to output / 既存 DB から出力までの主導線: [Existing DB To Output / 既存 DB から出力まで](docs/existing-db-to-output.md)
- Current workflow / 現在サポートするワークフロー: [Current Supported Workflow / 現在サポートするワークフロー](docs/current-supported-workflow.md)

Operations and state / 運用と状態管理:

- State and persistence map / 保存先と状態の地図: [Storage And State Model / 保存先と状態モデル](docs/storage-and-state-model.md)
- Common task collection / よく使う作業集: [Common Tasks / よく使う作業](docs/common-tasks.md)
- Canonical metadata bundle / 正本メタデータ一式: [Project Metadata Bundle / プロジェクトメタデータ bundle](docs/project-metadata-bundle.md)
- Config DB externalization / 設定 DB の外部化: [Config DB Externalization / config DB 外部化](docs/config-db-externalization.md)
- Troubleshooting / トラブルシューティング: [Troubleshooting / トラブルシューティング](docs/troubleshooting.md)

Learning and reference / 学習と参照:

- Glossary / 用語集: [Glossary / 用語集](docs/glossary.md)
- Sample learning path / サンプル学習導線: [Sample Tutorial Roadmap / sample 学習導線](docs/sample-tutorial-roadmap.md)
- Internal implementation, architecture, and migration map / 内部実装、アーキテクチャ、移行地図: [Internal Documentation Index / 内部ドキュメント索引](docs/internal/README.md)

History / 履歴:

- Release history / リリース履歴: [History](HISTORY.md)
- History and handoff records / 履歴と引き継ぎ記録: [2026 Reports / 2026 年の履歴](docs/reports/2026/README.md)

Read `docs/reports/` only when history is needed. For everyday source of truth, prefer the date-less docs under `docs/`. / 履歴が必要なときだけ `docs/reports/` を読み、普段の正本としては日付なしの `docs/` 配下の文書を優先してください。
