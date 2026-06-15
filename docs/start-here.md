# Start Here

English companion:
This is the five-minute starting point for users and contributors who want to try building with Mtool. It fixes the reading order, the first commands to run, the repo map, and where to go next when you need deeper internal reference.

この文書は、Mtool を触り始めるユーザと contributor が最初の 5 分で repo の current な読み方を掴むための入口です。  
詳細仕様の正本は各恒久文書に分かれており、この文書は読む順番と boundary を固定する役割だけを持ちます。

## 3 層の読み方

1. 入口 layer
   - `quickstart`
   - `start-here`
   - `choose-your-path`
2. golden path layer
   - `existing-db-to-output`
   - `common-tasks`
   - `current-supported-workflow`
   - `troubleshooting`
3. detail layer
   - `overview`
   - `storage-and-state-model`
   - `project-metadata-bundle`
   - `config-db-externalization`
   - `sample-tutorial-roadmap`
   - `study/README`
   - `internal/README`

入口 layer で読む順番を決め、golden path layer で実行の流れを掴み、その後に detail layer を読むのが current reading model です。

## 最初に読む順番

1. [../README.md](../README.md)
2. [quickstart.md](quickstart.md)
3. [choose-your-path.md](choose-your-path.md)
4. [existing-db-to-output.md](existing-db-to-output.md)
5. [common-tasks.md](common-tasks.md)
6. [current-supported-workflow.md](current-supported-workflow.md)
7. [overview.md](overview.md)
8. [storage-and-state-model.md](storage-and-state-model.md)
9. [sample-tutorial-roadmap.md](sample-tutorial-roadmap.md)
10. [study/README.md](study/README.md)
11. [troubleshooting.md](troubleshooting.md)
12. [internal/README.md](internal/README.md)
13. [../tests/README.md](../tests/README.md)

`docs/reports/` は history / handoff / resume prompt 用です。current supported workflow の正本として最初に読む場所ではありません。

## 途中から再開するなら

- runbook 側の handoff payload:
  - [existing-db-to-output.md#e10-handoff-payload](existing-db-to-output.md#e10-handoff-payload)
- state の確認先:
  - [storage-and-state-model.md#s1-resume-checkpoints](storage-and-state-model.md#s1-resume-checkpoints)
- contributor / AI handoff の内部 contract:
  - [internal/README.md](internal/README.md)

dated report は補助であり、まずはこの 3 か所で `project_key`、chosen lane、`source_key`、`artifact_key`、`config_db` / artifact 側の残存 state を確認します。

## この repo は何か

- 外部で決めた DB 構造を import し、`Data Class`、`DB Access`、`Source Output` を生成する内部ツールの新実装
- current runtime / generator / sample / test を 1 repo で育てる作業場所
- 主線は `DB 構造 -> import -> Data Class -> DB Access -> Source Output`

## この repo がしないこと

- DB 構造そのものの設計
- `original-codes/` を runtime input として直接使うこと
- historical report を current spec の代わりに読むこと

## repo map

| path | 役割 | 最初に見るとき |
| --- | --- | --- |
| `mtool/` | current runtime / generator / admin / lab / scripts | 実装を読むとき |
| `sample/tutorials/` | user-facing tutorial lane (`sample01-10`) | 学習用 sample を触るとき |
| `sample/internal-patterns/` | internal rewrite / migration guard (`pattern01-14`) | complex form / generator guard を確認するとき |
| `sample/legacy-projects/` | sanitized representative project pack | 実 project 由来の pack を見るとき |
| `tests/` | PHPUnit integration test と scenario | 現在の gate を確認するとき |
| `docs/` | user-facing permanent docs と internal/reference index | まず date-less な正本を追うとき |
| `work/` | disposable output、artifact history、compare workspace | runtime output や compare 作業を追うとき |
| `mtool/reference/legacy-dbclasses/` | curated legacy DB class reference | 限定された比較・移行文脈を見るとき |
| `original-codes/` | host-side reference only の旧実装 snapshot | full legacy source の差分確認が必要なとき |

## 重要な boundary

- `original-codes/` は host-side reference only
- curated legacy reference は `mtool/reference/legacy-dbclasses/` など `mtool/reference/` 配下に限定して置く
- 新実装の runtime / generator / Docker container は `original-codes/` を直接入力として使わない
- tutorial lane は `sample/tutorials/` に固定し、simple-to-complex の順番を `sample01` から積む
- internal pattern guard は `sample/internal-patterns/` に分離し、tutorial lane と混ぜない
- internal architecture / migration map は [internal/README.md](internal/README.md) から 1 段内側で辿る
- dated report は履歴であり、恒久仕様は日付なしファイルへ昇格させる

## current supported workflow

1. clone 直後は [quickstart.md](quickstart.md) で最初の 1 周を通す
2. `make env` と `make up-mtool` で MTOOL seed 付き environment を起動する
3. `MTOOL` に対して table import / data class sync / db access sync を流す
4. tutorial sample か full suite で current gate を確認する
5. 必要なら runtime reference / rollout status を script で確認する

既存 DB を named source として登録し、canonical metadata 永続化から output publish / verify まで 1 本で辿る時は [existing-db-to-output.md](existing-db-to-output.md) を正本にします。  
何がどこに保存されるかは [storage-and-state-model.md](storage-and-state-model.md) を参照してください。

最初の確認対象としては、`sample01-simple-table-runtime` か `sample10-dbaccess-mini-crud-flow` が読みやすいです。  
sample を教材として読む順番は [study/README.md](study/README.md)、tutorial lane の正本は [sample-tutorial-roadmap.md](sample-tutorial-roadmap.md) にあります。

## 最初のコマンド

### 0. clone 直後に 1 周する

```bash
make env
make up-mtool
make health-mtool
make config-db-preflight-mtool
make mtool-canonical-sync
make sample01-pack-runtime-test
```

期待結果と次の読み先は [quickstart.md](quickstart.md) を参照します。

### 1. help と環境起動

```bash
make help | sed -n '1,80p'
make env
make up-mtool
```

`make up-mtool` は MTOOL core seed 付きで `compose.yaml + compose.local-db-config.yaml + mtool/docker/compose/01_mtool.compose.yaml` を使います。external config DB を使う時は `APP_CONFIG_DB_*` を指定して `make up-external-config-db` を使います。起動後の確認は `make ps-external-config-db` / `make health-external-config-db` / `make config-db-preflight-external-config-db` を使います。external lane で shell や一時 stop が必要な時だけ raw `docker compose -f compose.yaml ...` を使います。

local `make up-mtool` の設計データは `db-config` Docker volume に保存されます。継続利用では `make backup-config-db-mtool`、本気利用では `deploy/durable-config-db.env.example` から作った `.env.durable` と `make up-durable-config-db DURABLE_ENV_FILE=.env.durable` を使います。

`make up-mtool` の出力には `admin` / `lab` に加えて `lab-db-ui` も含まれます。
`lab-db-ui` で `db-lab` の table 定義を触り、その後 admin 側の `lab-live-schema` source から canonical metadata へ取り込む流れを試せます。

### 2. MTOOL の canonical import / sync

```bash
make mtool-canonical-sync
```

### 3. tutorial sample を 1 本確認する

```bash
make sample01-pack-runtime-test
make sample10-pack-runtime-test
```

### 4. full suite を回す

local で旧 stack が default port を掴んでいることがあるため、まずは port override 付きの実行を基準にします。

```bash
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```

### 5. runtime reference と rollout を確認する

```bash
php mtool/scripts/show_runtime_reference_status.php --require-current
php mtool/scripts/show_runtime_replacement_rollout.php --non-plain-only
```

`show_runtime_reference_status.php --require-current` は strict mode です。  
durable snapshot が残っていて JSON 上は `ok=true` でも、`work/` 側の latest artifact history が無いと status が `reference-snapshot-only` になり、CLI exit code は non-zero になります。

## どの文書を正本として読むか

- ツールの概念モデル: [overview.md](overview.md)
- 目的別の入口と最初のコマンド: [choose-your-path.md](choose-your-path.md)
- existing DB から output までの主導線: [existing-db-to-output.md](existing-db-to-output.md)
- task 単位の最短手順: [common-tasks.md](common-tasks.md)
- current mainline と archived 導線の切り分け: [current-supported-workflow.md](current-supported-workflow.md)
- 何がどこに保存されるか: [storage-and-state-model.md](storage-and-state-model.md)
- project-scoped canonical metadata bundle の current rule: [project-metadata-bundle.md](project-metadata-bundle.md)
- `config_db` local overlay / external lane の current rule: [config-db-externalization.md](config-db-externalization.md)
- current supported lane の warning / error 切り分け: [troubleshooting.md](troubleshooting.md)
- 用語の短い定義集: [glossary.md](glossary.md)
- sample の学習順と役割分担: [sample-tutorial-roadmap.md](sample-tutorial-roadmap.md)
- 実装内部 / architecture / migration map: [internal/README.md](internal/README.md)
- 文書ナビゲータ: [README.md](README.md)
- test gate と sample test の一覧: [../tests/README.md](../tests/README.md)

## history が必要なとき

- 2026 年の report 索引: [reports/2026/README.md](reports/2026/README.md)
- 日次 status や handoff を見たいときだけ `docs/reports/` を読む
- report にしか書かれていない stable rule が見つかったら、date-less な恒久文書へ移す
