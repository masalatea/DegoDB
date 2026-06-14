# 2026-05-22 AI-Facing Docs Entrance Plan

## Status

- first pass: `DONE`
- status updated at: `2026-05-27`
- completion basis:
  - `README.md`
  - `docs/start-here.md`
  - `docs/README.md`
  - `docs/internal/repo-boundaries.md`
  - `docs/current-supported-workflow.md`
  - `docs/glossary.md`
  - `docs/common-tasks.md`
  - `tests/Integration/DocsEntranceContractTest.php`
- note:
  - この plan の入口整備は完了した。
  - flow-first な既存 DB 導線の追加拡張は `2026-0527-human-ai-existing-db-journey-plan.md` で扱う。

## 結論

- Git 公開を前提にした「AI が最初に読む入口ドキュメント」は整備した方がよい。
- 現状の repo には有用な恒久文書がすでに揃っているが、入口が分散しており、`README.md` に再構築途中の詳細・歴史的文脈・運用補足が混ざっていて、AI が短時間で source of truth を掴みにくい。
- 方針としては、新しい巨大文書を 1 枚増やすのではなく、既存の恒久 doc を正本のまま活かしつつ、その上に「AI 用の薄い導線レイヤ」を追加する。

## 現状の評価

### 良い点

- `docs/overview.md`
  - ツール本来の `DB 構造 -> import -> Data Class -> DB Access -> Source Output` を説明しており、概念の正本として強い
- `docs/internal/runtime-architecture.md`
  - runtime / Docker / path 境界が整理されている
- `docs/internal/generated-code-strategy.md`
  - generated runtime / promote / restore / host-only helper の考え方がまとまっている
- `docs/sample-tutorial-roadmap.md`
  - user-facing sample の学習順が明確
- `docs/README.md`
  - 恒久 doc と dated report を分けるルールはすでにある

### 足りない点

- root `README.md`
  - 情報量は多いが、AI が最初の 2-3 分で読む入口としては重い
  - 恒久情報、実装途中の状態、歴史的経緯、細かなコマンド例が 1 ファイルに混ざっている
- `docs/README.md`
  - 索引としては有用だが、「AI はまず何を読み、どこで止まり、どの文書を source of truth として優先するか」がまだ弱い
- dated report が多い
  - 履歴としては正しいが、公開 repo を読む AI は historical report を恒久仕様と誤読しやすい
- 「この repo の不変条件」
  - 例: `original-codes/` は host-side reference only、sample/tutorial と internal-pattern は別物、など
  - これらが複数文書に散っている
- 「よくある作業の最短導線」
  - AI が「まず起動」「sample を 1 本動かす」「test を回す」「どこを読めばよいか」をすぐ判断できる入口がない

## 目標

AI がこの repo を初見で読んだとき、次の 5 つを 5 分以内に把握できる状態にする。

1. この repo は何をするツールか
2. 今どこまで完成していて、何が current supported workflow か
3. どの directory が runtime code / sample / historical reference なのか
4. 最初に何のコマンドを打てばよいか
5. 深掘りするとき、どの恒久 doc を source of truth として読むべきか

## 設計方針

### 1. AI 用入口は「薄く」作る

- 新しい入口 doc は、既存の恒久 doc を置き換えるのではなく、読む順番と責務を明示するレイヤにする
- 新規の入口 doc 自体に詳細仕様を大量転記しない
- 原則として 1 doc 1 役割に留め、深い説明は既存 doc にリンクする

### 2. stable facts と dated history を分ける

- 入口 doc から直接 historical report を大量参照しない
- まず恒久 doc を示し、history が必要なときだけ report を読む形にする
- dated report は「判断の根拠」「変更履歴」「handoff」に限定して読む導線へ寄せる

### 3. AI は「正本」「補足」「履歴」を区別できるようにする

- 各入口 doc に次を明記する
  - この文書は何の正本か
  - 何はこの文書のスコープ外か
  - 次に読むべき文書は何か

### 4. command-first ではなく orientation-first にする

- AI 向け入口は、いきなり全コマンド一覧を並べるより先に repo map と不変条件を示す
- ただし、最小操作の quickstart は必須で置く

## 追加・整理する文書の案

### [DONE] Phase 1. 入口レイヤ

1. `README.md` を public / AI entrance 用に slim 化する
   - 役割
   - 3 行要約
   - 最初に読む文書 3-5 本
   - 最小 quickstart
   - sample tutorial 入口
   - detailed history は `docs/` / `docs/reports/` へ逃がす

2. `docs/start-here.md` を新設する
   - 想定読者は人間 + AI
   - 5 分で把握するための reading order
   - repo map
   - current supported workflow
   - 最初のコマンド
   - 深掘り先

3. `docs/README.md` を「文書索引」から「文書ナビゲータ」へ強化する
   - 正本 / 補足 / 履歴の区別
   - AI が report を読む前に恒久 doc を読むよう誘導する

### [DONE] Phase 2. AI が誤解しやすい点の恒久化

4. `docs/internal/repo-boundaries.md` を新設する
   - `mtool/`
   - `sample/`
   - `tests/`
   - `work/`
   - `original-codes/`
   - host-side only / runtime input / disposable output の区別

5. `docs/current-supported-workflow.md` を新設する
   - current で信頼してよい main flow
   - 起動、sample、test、runtime reference restore/promote
   - historical helper と current mainline の区別

6. `docs/glossary.md` を新設する
   - `dbtable`
   - `dataclass`
   - `da` / `dafunc`
   - `single-function proxy`
   - `custom proxy`
   - `source output`
   - `runtime reference`
   - `sample-test`

### [DONE] Phase 3. タスク導線

7. `docs/common-tasks.md` を新設する
   - 環境を起動する
   - tutorial sample を 1 本動かす
   - full test を回す
   - runtime reference 状態を確認する
   - sample を追加するときに触るファイル

8. `docs/samples.md` を新設するか、`docs/sample-tutorial-roadmap.md` の入口要約 section を強化する
   - 学習目的の tutorial lane
   - internal pattern lane
   - legacy project lane
   - どれを最初に触るべきか

### [DONE] Phase 4. 維持運用

9. docs 更新ルールを明文化する
   - 新しい恒久仕様を入れたらどの doc を更新するか
   - report の内容が恒久仕様へ昇格したらどこへ転記するか
   - `README` に何を書き、何を書かないか

10. 必要なら docs contract test を追加する
   - `README.md` が `docs/start-here.md` を指している
   - `docs/start-here.md` が主要恒久 doc を指している
   - `original-codes/` host-side only の説明が入口 doc に存在する
   - tutorial lane の current sample 数が入口 doc と roadmap で一致する

## 推奨 reading order

公開後に AI へまず読ませる順番は次を想定する。

1. `README.md`
2. `docs/start-here.md`
3. `docs/overview.md`
4. `docs/internal/repo-boundaries.md`
5. `docs/current-supported-workflow.md`
6. `docs/internal/runtime-architecture.md`
7. `docs/internal/generated-code-strategy.md`
8. `docs/sample-tutorial-roadmap.md`

この順で、入口 -> 概念 -> repo 境界 -> current workflow -> 技術詳細 -> sample 導線へ進める。

## 今ある文書との責務分担案

- `README.md`
  - public repo の最上位入口
- `docs/start-here.md`
  - AI / contributor 用の最初の 1 枚
- `docs/overview.md`
  - ツールの概念モデルの正本
- `docs/internal/runtime-architecture.md`
  - 技術構成の正本
- `docs/internal/generated-code-strategy.md`
  - generated runtime / runtime reference 運用の正本
- `docs/sample-tutorial-roadmap.md`
  - sample 学習導線の正本
- `docs/reports/`
  - 判断履歴、slice report、handoff、resume prompt

## 受け入れ条件

- 初見の AI が historical report を掘らなくても、入口 doc だけで current mainline を把握できる
- `README.md` を 200 行前後まで圧縮し、詳細を `docs/` へ逃がせる
- `original-codes/` の扱いを入口 doc で誤解なく説明できる
- sample / pattern / legacy project の違いを入口 doc で即判別できる
- test 実行の成功例ポートが 1 箇所で見つかる
- docs の正本が「どこか」が人にも AI にも明確になる

## 実装順の提案

1. `[DONE]` `README.md` の再設計
2. `[DONE]` `docs/start-here.md` の追加
3. `[DONE]` `docs/README.md` のナビゲータ化
4. `[DONE]` `docs/internal/repo-boundaries.md` の追加
5. `[DONE]` `docs/current-supported-workflow.md` の追加
6. `[DONE]` `docs/glossary.md` の追加
7. `[DONE]` `docs/common-tasks.md` の追加
8. `[DONE]` docs contract test

## 非目標

- すべての dated report を整理し直すこと
- 旧実装資料を AI 向けに再編集すること
- public marketing 用サイトのような prose にすること
- 恒久 doc の中へ日次 status を戻すこと

## 次アクション

- この plan を採用するなら、まず `README.md` と `docs/start-here.md` の 2 枚から着手するのがよい。
- その時点で、既存の恒久 doc を壊さずに入口品質だけ大きく改善できる。
