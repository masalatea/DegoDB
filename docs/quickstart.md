# Quickstart / まず動かしてみる

English companion:
This is the shortest hands-on path after cloning the repository. It focuses on one successful local run before deeper design, migration, or contributor documentation.

この文書は、clone 直後の開発者が最初に 1 周だけ動かすための入口です。  
詳細な設計や背景を読む前に、local stack を起動し、MTOOL の metadata sync を流し、tutorial sample の green check まで確認します。

## 前提

- Docker と Docker Compose が使える
- `make` が使える
- host 側で `php` が使える

`make env` は host 側の PHP で `.env` を生成します。Docker だけの環境で始める場合は、先に host PHP を入れるか、`.env.example` を `.env` にコピーして local 用 credential を調整してください。

## 1. 起動する

## AI-assisted setup の最初の確認

If an AI assistant is guiding a user through setup, ask these two separate questions before generating commands or editing `.env`. The first database stores DegoDB's own design metadata. The second is the user's application / business database that DegoDB imports from or generates access code for.

1. Where should DegoDB persist its own design metadata: a folder-backed SQLite file for lightweight personal use, a local Docker MariaDB volume for trial use, or a managed MySQL / MariaDB server for team / enterprise use?
2. Which user database should DegoDB work with as the source / target schema: an existing MySQL / MariaDB database, the local Lab DB, or a database that will be prepared later?

AI がセットアップを案内する場合は、コマンド生成や `.env` 編集の前に必ず次の 2 点を分けて確認します。1 つ目は DegoDB 自身の設計メタデータ保存先です。2 つ目は DegoDB が import / 生成対象として扱うユーザーのアプリケーション DB / 業務 DB です。

1. DegoDB 自身の設計メタデータをどこに永続化しますか。軽い個人利用向けのフォルダ指定 SQLite、試用向けの local Docker MariaDB volume、チーム / enterprise 向けの管理された MySQL / MariaDB server のどれにしますか。
2. DegoDB が扱うユーザー側 DB はどれですか。既存の MySQL / MariaDB、local Lab DB、または後で準備する DB のどれにしますか。

迷う場合は、個人の軽い利用では `APP_CONFIG_STORE_DIR=work/config-store`、チーム利用や server 管理前提では `APP_CONFIG_STORE_DIR` を空にして MySQL / MariaDB profile を選びます。

```bash
make env
make up-mtool
```

`make up-mtool` は `compose.yaml + compose.local-db-config.yaml + mtool/docker/compose/01_mtool.compose.yaml` で、MTOOL core seed 付きの local stack を起動します。最後に access URL と stub login 情報を表示します。

初回は Docker image の pull と PHP/Apache image build が走るため、数分かかることがあります。途中で長い download / extract / apt install log が出ても、そのまま待ちます。

## 永続化の注意

Persistence note:
By default, the quickstart stores design metadata in the local `db-config` Docker volume. For lightweight personal use, the user-facing target is a folder-only file store setup: set `APP_CONFIG_STORE_DIR` and DegoDB will use `APP_CONFIG_STORE_DIR/config.sqlite` for the SQLite config store. If the SQLite file is empty or missing, DegoDB bootstraps the current config schema automatically. Leave it empty for the MySQL / MariaDB server DB profile.

Quickstart の `make up-mtool` は試用向け local stack です。設計データの正本は local `db-config` の Docker volume に保存されます。

軽く個人利用したい場合の入口は、細かい DB 設定ではなく保存フォルダだけに寄せます。

```env
APP_CONFIG_STORE_DIR=work/config-store
```

この値を指定した場合、設計データの保存先は `APP_CONFIG_STORE_DIR/config.sqlite` になります。SQLite ファイルが未作成または空の場合は、DegoDB が current config schema を初回 bootstrap で自動作成します。server DB を管理したい場合はこの値を空にし、従来どおり local MariaDB または external MySQL / MariaDB を使います。

Lightweight startup without a config DB server is available through the lite lane:

```bash
APP_CONFIG_STORE_DIR=work/config-store make up-mtool-lite
make health-mtool-lite
make config-db-preflight-mtool-lite
```

config DB server を起動しない軽量起動は lite lane を使います。`db-lab` は user / lab DB として残り、DegoDB 自身の設計メタデータだけが SQLite file store に保存されます。

SQLite lightweight lane を一括確認したい場合は、temporary SQLite store で起動、health、admin top page、preflight、migrate、MTOOL core seed、backup / restore まで確認する smoke を使います。

```bash
make mtool-lite-smoke
```

SQLite file store を継続利用する場合は、同じ folder を指定して backup を取ります。

```bash
APP_CONFIG_STORE_DIR=work/config-store make backup-config-db-sqlite-rotate
```

- `make reset-mtool` は DB volume ごと消すため、設計データも消えます。
- server DB profile で継続利用する場合は `make backup-config-db-mtool` で dump を取り、定期運用では `make backup-config-db-mtool-rotate` を使います。
- チーム利用・本気利用では `deploy/durable-config-db.env.example` から `.env.durable` を作り、external config DB を使います。
- 詳細は [common-tasks.md](common-tasks.md) の config DB backup / restore と、[config-db-externalization.md](config-db-externalization.md) を参照してください。

通常の default port は次の通りです。

| 用途 | URL |
| --- | --- |
| admin | `http://127.0.0.1:8081` |
| lab | `http://127.0.0.1:8082` |
| lab-db-ui | `http://127.0.0.1:8083` |

port を変えている場合は、`make up-mtool` の出力を正として見ます。

## 2. 起動確認する

```bash
make ps-mtool
make health-mtool
make config-db-preflight-mtool
```

`make health-mtool` で admin / lab が返り、`make config-db-preflight-mtool` で config DB schema が current なら、local stack は使える状態です。

## 3. MTOOL の metadata を同期する

```bash
make mtool-canonical-sync
```

ここで `DB 構造 -> import -> Data Class -> DB Access` の主線を 1 回流します。  
既存 DB を接続する本番手順は [existing-db-to-output.md](existing-db-to-output.md) を使います。

## 4. tutorial sample を 1 本通す

```bash
make sample01-pack-runtime-test
```

これは `sample/tutorials/sample01-simple-table-runtime` の import / sync / output / reference compare を通す最小 tutorial gate です。  
SQLite config store profile でも同じ最小 tutorial を確認できます。

```bash
make sample01-pack-runtime-test-sqlite
```

DB Access の CRUD flow まで見たい場合は次に進みます。

```bash
make sample10-pack-runtime-test
```

SQLite config store profile でも同じ DB Access tutorial gate を確認できます。

```bash
make sample10-pack-runtime-test-sqlite
```

User DB dialect contract の最小比較を確認する場合:

```bash
make user-db-contract-test
```

これは sample10 の DBACCESS-PHP / DATACLASS-PHP output を MySQL / MariaDB lane と SQLite lane で capture し、raw file 完全一致ではなく class / method / parameter / SQL / bind / result shape / runtime CRUD behavior の normalized contract を比較します。

HTML Source Output まで見たい場合は次も使えます。

```bash
make sample11-pack-runtime-test
```

SQLite config store profile でも同じ HTML Source Output tutorial gate を確認できます。

```bash
make sample11-pack-runtime-test-sqlite
```

external DB source import まで見たい場合は次を使います。

```bash
make sample12-pack-runtime-test
```

SQLite config store profile でも同じ external DB source import tutorial gate を確認できます。ここでも `db-lab` は user / external DB として残り、SQLite は DegoDB 自身の設計メタデータ保存先です。

```bash
make sample12-pack-runtime-test-sqlite
```

OpenAPI API surface まで見たい場合は次を使います。

```bash
make sample13-pack-runtime-test
```

SQLite config store profile でも同じ OpenAPI API surface tutorial gate を確認できます。

```bash
make sample13-pack-runtime-test-sqlite
```

custom proxy runtime まで見たい場合は次を使います。

```bash
make sample14-pack-runtime-test
```

SQLite config store profile でも同じ custom proxy runtime tutorial gate を確認できます。

```bash
make sample14-pack-runtime-test-sqlite
```

project metadata bundle export / import まで見たい場合は次を使います。

```bash
make sample15-pack-runtime-test
```

SQLite config store profile でも同じ project metadata bundle export / import tutorial gate を確認できます。

```bash
make sample15-pack-runtime-test-sqlite
```

authenticated proxy まで見たい場合は次を使います。

```bash
make sample16-pack-runtime-test
```

SQLite config store profile でも同じ authenticated proxy tutorial gate を確認できます。

```bash
make sample16-pack-runtime-test-sqlite
```

multi-output capstone まで見たい場合は次を使います。

```bash
make sample17-pack-runtime-test
```

SQLite config store profile でも同じ multi-output capstone tutorial gate を確認できます。

```bash
make sample17-pack-runtime-test-sqlite
```

## 5. 停止する

```bash
make stop-mtool
```

コンテナと network を削除する場合は次を使います。

```bash
make down-mtool
```

DB volume も含めて fresh start に戻す場合だけ次を使います。

```bash
make reset-mtool
```

## 成功したら次に読むもの

- 目的別に選ぶ: [choose-your-path.md](choose-your-path.md)
- 全体像を掴む: [start-here.md](start-here.md)
- 既存 DB から output まで進める: [existing-db-to-output.md](existing-db-to-output.md)
- よく使う操作を見る: [common-tasks.md](common-tasks.md)
- sample を教材として読む: [study/README.md](study/README.md)
- sample の順番を見る: [sample-tutorial-roadmap.md](sample-tutorial-roadmap.md)

## legacy reference について

旧実装全体を current runtime input として使う導線はありません。
現在の curated legacy reference は、旧生成 DB class を `mtool/reference/legacy-dbclasses/`、旧 build ロジックを `mtool/reference/legacy-mtool-build/`、旧テンプレートを `mtool/reference/legacy-mtool-templates/` に分けて置きます。
current runtime / generator / Docker container は、これらの legacy reference directory を直接の実行入力として読みません。
