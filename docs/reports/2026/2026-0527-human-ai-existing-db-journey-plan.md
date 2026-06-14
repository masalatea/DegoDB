# 2026-05-27 Human / AI Existing DB Journey Plan

## Status

- first pass: `DONE`
- status updated at: `2026-05-27`
- completion basis:
  - `docs/existing-db-to-output.md`
  - `docs/storage-and-state-model.md`
  - `docs/internal/ai-operator-contract.md`
  - entrance / detail docs の resume 導線更新
  - `DocsEntranceContractTest` の拡張と green

## 目的

- 他の contributor と AI が、`report を掘らずに` current supported workflow を自然に実行できる状態へ寄せる。
- 特に次の主要導線を、1 回目でも迷わず進められるようにする。
  - `既存 DB に接続`
  - `canonical metadata として永続化`
  - `Data Class / DB Access / Source Output まで進める`
  - `publish / verify まで到達する`

## 結論

- いまの docs は「情報量」と「正本の整理」はかなり進んでいる。
- ただし `既存 DB -> import -> sync -> output` の primary journey は、まだ複数文書を頭の中で合成しないと自然には辿れない。
- したがって次の改善対象は、文書の追加量ではなく `flow-first な再編` である。

## current の強み

- `README.md` / `docs/start-here.md` / `docs/choose-your-path.md` で入口はある。
- `docs/current-supported-workflow.md` / `docs/common-tasks.md` で current mainline と task guide はある。
- `docs/project-metadata-bundle.md` / `docs/config-db-externalization.md` / `docs/troubleshooting.md` で stable rule は date-less 化済み。
- external named source、`config_db` externalization、OpenAPI viewer / publish lane も current doc に反映されている。

## current の弱み

この section は計画立案時点の gap を残した記録です。  
下記の項目は first pass 完了に伴い、恒久文書側では解消済みです。

### 1. primary journey が 1 本に束ねられていない

- `既存 DB に接続して取り込む` 手順は存在する。
- しかし実際には以下を横断して読む必要がある。
  - `start-here`
  - `choose-your-path`
  - `common-tasks`
  - `current-supported-workflow`
  - `config-db-externalization`
- 初見の人や AI には「どこからどこまでが 1 本の流れか」が見えにくい。

### 2. 状態モデルが頭の中依存になっている

- `config_db`
- built-in `db`
- `lab_db`
- external named source
- published output / artifact

これらの役割は個別 docs にはあるが、`この journey で何がどこに保存されるか` が 1 枚で見えない。

### 3. UI lane と CLI lane の往復が分かりにくい

- 現状でも UI / CLI の両方で進められる。
- ただし `同じ step の UI 版と CLI 版` が隣接して書かれていないことが多い。
- AI は CLI を選びやすく、人は UI を選びやすいので、同一 step の dual lane 表示が必要である。

### 4. success marker が散っている

- `ok=true`
- `schema_current=true`
- import preview/apply の見方
- sync 後の期待状態
- output publish 後の確認先

これらが step ごとにまとまっていないため、実行した人が「今どこまで成功したか」を判断しづらい。

### 5. AI が参照すべき最小 contract がまだ無い

- AI 向け入口はあるが、`この順番で読み、この doc を正本とし、このコマンドで checkpoint を取る` という運用 contract はまだ散在している。
- 結果として、AI は正しい docs は読めても、primary journey を最短距離で実行しにくい。

## target experience

次を満たした状態を目標にする。

1. 初見の contributor が `1 本の恒久文書` を主に読めば、既存 DB 接続から output publish まで辿れる
2. AI が `1 本の AI 向け contract doc` と `1 本の journey doc` だけで current lane を再現できる
3. 各 step に `何をする / 何が永続化される / 成功条件 / 次に進む条件` がある
4. local default lane と external config DB lane の違いが、journey の先頭で明示される
5. troubleshooting は `段階別` にリンクされ、失敗時の戻り先が明確である

## primary journey の定義

まず human / AI 共通で、次の journey を `最優先導線` として固定する。

1. 起動 topology を選ぶ
   - local default
   - external `config_db`
2. stack / health / preflight を通す
3. existing DB を import source として登録する
4. import preview を確認する
5. import apply で canonical metadata を保存する
6. `sync_project_data_classes.php`
7. `sync_project_db_access.php`
8. source output を create/publish する
9. lab / artifact download / proxy / swagger で確認する
10. どこに state が残ったかを理解する
11. 次回更新時の rerun path を辿れる

## 計画

### [DONE] Phase 1. docs の役割を primary journey 基準で再整理する

やること:

- 新しい恒久文書として `docs/existing-db-to-output.md` を追加する
- 既存 docs の役割を次に固定する
  - `README.md`
    - repo の public 入口
  - `start-here.md`
    - 最初の 5 分で boundary を掴む
  - `choose-your-path.md`
    - goal-based な逆引き
  - `existing-db-to-output.md`
    - primary journey の正本
  - `config-db-externalization.md`
    - topology / persistence boundary の詳細
  - `project-metadata-bundle.md`
    - bundle lane の詳細
  - `troubleshooting.md`
    - failure cut

成果物:

- `docs/existing-db-to-output.md`
- `README.md` / `docs/start-here.md` / `docs/choose-your-path.md` / `docs/README.md` のリンク更新

acceptance:

- 「既存 DB につないで output まで行きたい」人が、最初の 2 分で読むべき doc を 1 本に特定できる

### [DONE] Phase 2. existing DB journey を dual-lane で書く

やること:

- `existing-db-to-output.md` に step-by-step の主導線を書く
- 各 step は次の形式に揃える
  - 目的
  - CLI
  - UI
  - 永続化先
  - 成功条件
  - よくある失敗と戻り先

必ず含める step:

- topology decision
- `make up` / `make up-external-config-db`
- `make config-db-preflight`
- `/settings/database-sources` または equivalent CLI
- `/projects/{project_key}/tables/import?source=named-live-schema:{source_key}`
- `sync_project_data_classes.php`
- `sync_project_db_access.php`
- `create_project_output.php --publish`
- `/runs/swagger/{project_key}?source_output_key=...&db_source_key=...`

acceptance:

- 人が UI 寄りでも CLI 寄りでも、同じ journey 上で迷わず選べる
- AI が CLI lane を選んだとき、別 doc を大きく横断しなくても進める

### [DONE] Phase 3. state / persistence model を 1 枚にする

やること:

- 新しい恒久文書として `docs/storage-and-state-model.md` を追加する
- 少なくとも次を 1 枚にまとめる
  - `config_db` に入るもの
  - import source として読む DB
  - runtime read に使う DB
  - `db-lab` の位置づけ
  - artifact / work / published output の位置づけ
  - 何が durable で何が disposable か

この文書は `runtime-architecture.md` の詳細版ではなく、journey 実行者向けの `state map` として書く。

acceptance:

- 「いま保存した設定はどこに残るのか」
- 「既存 DB そのものを書き換えるのか」
- 「output はどこに出るのか」

この 3 問に 1 枚で答えられる。

### [DONE] Phase 4. AI 向け execution contract を追加する

やること:

- 新しい恒久文書として `docs/internal/ai-operator-contract.md` を追加する
- 内容は次に絞る
  - AI が最初に読む doc の順番
  - source of truth の優先順位
  - `original-codes/` を runtime input に戻さない
  - journey 実行時の標準 checkpoint
  - 実行前 / 実行後に取る status command
  - 追加 docs が必要になった時の昇格ルール

acceptance:

- AI が resume prompt なしでも current docs だけで大筋を再現できる
- 人が AI に依頼するとき、doc 名を 1-2 本指定すれば足りる

### [DONE] Phase 5. success marker を段階別 checklist にする

やること:

- `existing-db-to-output.md` か `common-tasks.md` に stage checklist を追加する
- stage は少なくとも次に分ける
  - boot
  - preflight
  - source registration
  - import preview
  - import apply
  - sync
  - output publish
  - lab verification

各 stage に `expected success marker` を書く。

例:

- `make config-db-preflight`
  - `ok=true`
  - `schema_current=true`
- import preview
  - target project / table count / stale delete interpretation
- publish
  - artifact path / viewer path / supported share lane

acceptance:

- 実行者が「いま詰まっているのか、次に進んでよいのか」を command output から判断できる

### [DONE] Phase 6. troubleshooting を stage-based に寄せる

やること:

- `docs/troubleshooting.md` を flow 連動で補強する
- 既存 DB journey に対して少なくとも次を持つ
  - source 登録失敗
  - config DB preflight warning
  - import preview/apply confusion
  - named source が Swagger / proxy で見えない
  - output publish 後に viewer で見えない

acceptance:

- journey doc の各 step から troubleshooting の該当節へ 1 hop で飛べる

### [DONE] Phase 7. docs contract test を増やす

やること:

- `DocsEntranceContractTest` か同等 test に次を追加する
  - `existing-db-to-output.md` が入口 docs から辿れる
  - `ai-operator-contract.md` が `start-here` か `docs/README` から辿れる
  - existing DB journey の主要 anchor / wording が消えていない

acceptance:

- 導線が壊れても test で気づける

## 実装順

優先度は次の順にする。

1. `[DONE]` `existing-db-to-output.md`
2. `[DONE]` 入口 docs のリンク整理
3. `[DONE]` `storage-and-state-model.md`
4. `[DONE]` `ai-operator-contract.md`
5. `[DONE]` `troubleshooting.md` の stage 化
6. `[DONE]` docs contract test 拡張

## 完了条件

次を満たしたら、この計画は first pass 完了とする。

1. `[DONE]` 新しい contributor が `README -> existing-db-to-output` だけで current lane を辿れる
2. `[DONE]` AI が `start-here -> ai-operator-contract -> existing-db-to-output` の順で current lane を再現できる
3. `[DONE]` `既存 DB に接続して persistent canonical metadata を作り、Data Class / DB Access / Output まで進める` 主要手順が 1 本の恒久文書にまとまっている
4. `[DONE]` local default lane と external `config_db` lane の違いが先頭で説明されている
5. `[DONE]` success marker と troubleshooting link が各 stage にある

first pass verdict: `DONE`

## 判断

- 「docs をさらに増やすか」より、「primary journey を 1 本にするか」が本質である。
- 今の repo は機能と stable rule は揃っているので、次段は UX 再編の仕事である。
- 既存 DB 接続から output までを本当に `迷わず` にしたいなら、この journey を first-class に昇格させるべきである。
