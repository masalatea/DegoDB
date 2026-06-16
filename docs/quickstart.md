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

```bash
make env
make up-mtool
```

`make up-mtool` は `compose.yaml + compose.local-db-config.yaml + mtool/docker/compose/01_mtool.compose.yaml` で、MTOOL core seed 付きの local stack を起動します。最後に access URL と stub login 情報を表示します。

初回は Docker image の pull と PHP/Apache image build が走るため、数分かかることがあります。途中で長い download / extract / apt install log が出ても、そのまま待ちます。

## 永続化の注意

Quickstart の `make up-mtool` は試用向け local stack です。設計データの正本は local `db-config` の Docker volume に保存されます。

- `make reset-mtool` は DB volume ごと消すため、設計データも消えます。
- 継続利用する場合は `make backup-config-db-mtool` で dump を取ります。
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
DB Access の CRUD flow まで見たい場合は次に進みます。

```bash
make sample10-pack-runtime-test
```

HTML Source Output まで見たい場合は次も使えます。

```bash
make sample11-pack-runtime-test
```

external DB source import まで見たい場合は次を使います。

```bash
make sample12-pack-runtime-test
```

OpenAPI API surface まで見たい場合は次を使います。

```bash
make sample13-pack-runtime-test
```

custom proxy runtime まで見たい場合は次を使います。

```bash
make sample14-pack-runtime-test
```

project metadata bundle export / import まで見たい場合は次を使います。

```bash
make sample15-pack-runtime-test
```

authenticated proxy まで見たい場合は次を使います。

```bash
make sample16-pack-runtime-test
```

multi-output capstone まで見たい場合は次を使います。

```bash
make sample17-pack-runtime-test
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

旧実装全体を current runtime input として使う導線はありません。`original-codes/` は host-side reference only です。  
現在の curated legacy reference は `mtool/reference/legacy-dbclasses/` など `mtool/reference/` 配下に限定して置きます。`original-codes/` という名前が docs や provenance metadata に出てくる場合も、current runtime / generator / Docker container が直接読む path ではありません。
