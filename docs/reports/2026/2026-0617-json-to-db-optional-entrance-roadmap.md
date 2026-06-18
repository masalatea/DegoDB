# 2026-06-17 JSON to DB Optional Entrance Roadmap

## Status

- first pass: `DONE`
- status updated at: `2026-06-17`
- scope: optional entrance / AI-facing instruction / tutorial roadmap
- completion basis:
  - `BACKGROUND.md`
  - `README.md`
  - `docs/json-to-db-entrance.md`
  - `docs/internal/json-to-db-ai-contract.md`
  - `docs/start-here.md`
  - `docs/choose-your-path.md`
  - `docs/README.md`
  - `docs/internal/README.md`
  - `docs/overview.md`
- baseline kept:
  - DegoDB の基本導線は引き続き `DB design -> import -> Data Class -> DB Access -> Source Output` とする
  - この roadmap は、初期構想に含まれていた前段の optional entrance を明文化する
- remaining optional future work:
  - tutorial sample の実体化
  - `docs/json-to-db-tutorial.md` の追加
  - UI helper の検討

## 結論

JSON 運用から DB 管理へ移行したい利用者向けの `JSON-first optional entrance` は、初期構想に含まれていた重要な入口である。

現在の主導線は、利用者がある程度データベースを理解している前提に寄っている。これは設計ツールとして正しい。一方で、実際には次のような利用者も多い。

- 既存データを JSON ファイルとして持っている
- サーバ上で JSON を parse / encode して処理している
- キーや配列構造は読めば分かる
- しかし正規化、永続化、トランザクション、スケールアウトを自力で設計するのは難しい

この層に対して、DegoDB を `最初から DB 設計を書くツール` としてだけ見せるのではなく、初期構想どおり `JSON で動いている処理を AI / 技術者が DB 設計へ落とすための入口` も持つものとして提示する。

重要なのは、DegoDB 自体を JSON 自動変換ツールに寄せすぎないことである。まずは UI 機能ではなく、初期構想に含まれていた AI 向け指示、技術者向け設計指針、optional tutorial として整えるのがよい。

最低目標は、`AI がこの文書群を読めば、ユーザから渡された JSON sample と現在の処理説明をもとに、DegoDB の既存 mainline へ接続可能な DB 設計案を作れること` である。

つまり、最初の完成判定は UI ではなく AI 実行可能性で見る。

## 位置づけ

### 既存 mainline

DegoDB の基本導線は維持する。

1. DB table を設計する
2. Data Class を生成する
3. DB Access を生成する
4. Source Output を publish する
5. runtime / API / artifact として利用する

### optional entrance

その前に、任意で次の入口を置く。

1. 現在の JSON データと処理を棚卸しする
2. JSON の key / nested object / array / id / reference / lifecycle を読み解く
3. AI または技術者が DB 設計案へ落とす
4. DegoDB の DB table / Data Class / DB Access 設計に接続する
5. 必要に応じて migration / import / export / API 化の方針を決める

この optional entrance は、DegoDB runtime が JSON を直接入力にするという意味ではない。JSON は `設計入力の材料` であり、DegoDB の canonical metadata へ落とし込むための前段資料として扱う。

## 想定ユースケース

### Case 1. 小規模アプリの JSON 保存を卒業する

- 現状:
  - `users.json`
  - `posts.json`
  - `settings.json`
  - PHP / Node / Python などで read / modify / write している
- 課題:
  - 同時更新に弱い
  - relation が暗黙的
  - ID 採番や整合性が壊れやすい
  - 管理画面や API の拡張がつらい
- DegoDB での出口:
  - users / posts / settings などを table 化する
  - foreign key 相当の参照を明示する
  - Data Class / DB Access / OpenAPI output へ進める

### Case 2. JSON API のレスポンスを永続データモデルへ整理する

- 現状:
  - 外部 API の JSON を cache している
  - 必要な項目を ad hoc に抜き出している
- 課題:
  - API response shape とアプリ内部 model が混ざる
  - 履歴管理、差分更新、検索、集計が難しい
- DegoDB での出口:
  - raw response と normalized tables の境界を分ける
  - import job / sync job / read model を設計する
  - DB Access で検索、集計、ページングを扱う

### Case 3. 設定 JSON を project metadata へ昇格する

- 現状:
  - 設定、テンプレート、画面定義を JSON で持っている
- 課題:
  - 変更履歴や環境差分の管理が難しい
  - UI から編集しづらい
- DegoDB での出口:
  - config_db / project metadata / source output の責務へ分解する
  - export / import bundle と組み合わせる

## AI / 技術者向け入力テンプレート

ユーザが AI または技術者へ渡す情報は、最初は厳密でなくてよい。ただし、次の形に揃えると DB 設計へ落としやすい。

````md
# JSON to DB Design Request

## Goal

- 何をしたいか:
- 現在困っていること:
- DB 化した後に得たいこと:

## Current JSON Files or Shapes

### <name>.json

```json
{
  "example": "paste a small representative sample"
}
```

## Current Processing

- 読み込みタイミング:
- 書き込みタイミング:
- 更新単位:
- 削除の扱い:
- 同時更新の可能性:

## Known Keys

| Key | Meaning | Required | Unique | Notes |
| --- | --- | --- | --- | --- |
| id | internal id | yes | yes | current string id |

## Relationships

- どの key が他の object / array / file を参照しているか:
- 親子関係:
- many-to-many になりそうな関係:

## Queries Needed

- 一覧:
- 詳細:
- 検索:
- 集計:
- 更新:

## Non-Goals

- DB 化しないもの:
- raw JSON のまま残すもの:
````

## AI が判断する観点

AI / 技術者は、JSON をそのまま table に写すのではなく、次の観点で DegoDB の設計へ変換する。

### 1. entity を見つける

- top-level object は table 候補か
- array item は table 候補か
- nested object は column にするか、別 table にするか
- key-value 的な可変構造は metadata table / JSON column / separate table のどれがよいか

### 2. identity を決める

- 既存 JSON に安定した ID があるか
- ID がない場合、DB 側で surrogate key を持つか
- 外部 ID と内部 ID を分ける必要があるか
- unique constraint に相当する条件は何か

### 3. relationship を明示する

- parent-child
- one-to-many
- many-to-many
- lookup / enum
- denormalized snapshot

### 4. lifecycle を読む

- 作成、更新、削除の単位
- soft delete が必要か
- 履歴を残すか
- raw JSON import の再実行に耐えるか
- sync と manual edit が衝突しないか

### 5. transactional boundary を決める

- 1 回の操作で同時に更新される record 群は何か
- partial write を許してよいか
- rollback すべき単位はどこか
- import / migration / runtime write を分けるか

### 6. output を決める

- Data Class だけでよいか
- DB Access が必要か
- OpenAPI / custom proxy が必要か
- HTML output / template output が必要か

## AI 向け最低実行 contract

AI がこの optional entrance を実行するときは、最低限、次の順番で作業する。

1. ユーザの JSON sample と現在の処理説明を読む
2. `raw JSON として残すもの` と `canonical table にするもの` を分ける
3. entity candidate を抽出する
4. identity / unique condition を推定する
5. relationship candidate を抽出する
6. lifecycle / transaction boundary を整理する
7. query / update / output requirements を整理する
8. DegoDB の table / Data Class / DB Access / Source Output candidate に落とす
9. 不明点を `blocking questions` と `non-blocking assumptions` に分ける

AI は、情報が足りない場合でも完全に停止しない。合理的な仮定で進められる部分は draft として出し、DB 設計を危険にする不明点だけを blocking として返す。

### AI output format

AI は、最低限次の format で返す。

```md
# JSON to DB Design Draft

## Summary

- Current JSON usage:
- Recommended DB direction:
- Main risks:

## Raw JSON To Keep

| JSON Path / File | Reason | Retention Policy |
| --- | --- | --- |

## Canonical Tables

| Table | Purpose | Source JSON | Notes |
| --- | --- | --- | --- |

## Columns

### <table_name>

| Column | Type Guess | Required | Unique | Source JSON Key | Notes |
| --- | --- | --- | --- | --- | --- |

## Relationships

| From | To | Cardinality | Source Evidence | Notes |
| --- | --- | --- | --- | --- |

## Lifecycle And Transactions

| Operation | Records Affected | Transaction Boundary | Notes |
| --- | --- | --- | --- |

## DegoDB Targets

### Data Classes

- <candidate>

### DB Access Functions

| Function | Purpose | Input | Output |
| --- | --- | --- | --- |

### Source Outputs

- <candidate>

## Import / Migration Notes

- Initial import:
- Rerun policy:
- Conflict policy:

## Blocking Questions

- Questions that must be answered before implementation.

## Non-Blocking Assumptions

- Assumptions used for the current draft.
```

### AI が避けること

- JSON 全体を 1 table の `json` column に閉じ込めて終わらせない
- nested object を常に別 table にするなど、機械的な正規化をしない
- raw import data と canonical runtime data を混ぜない
- migration / import / runtime write を同じ処理として扱わない
- ID が不明なまま update / delete 設計を確定しない
- scale out を語る前に transaction boundary と conflict policy を省略しない

## DegoDB に落とす成果物

optional entrance の出口は、次のような DegoDB 側の設計成果物にする。

- table candidate list
- column candidate list
- relation notes
- validation notes
- import / migration plan
- Data Class target list
- DB Access function target list
- Source Output target list
- unresolved questions

この時点では、完璧な正規化を目指すよりも、`DegoDB の既存 mainline に進める粒度` まで整理することを優先する。

## チュートリアル化の方針

### Tutorial A. JSON file から table 設計へ

目的:

- JSON を見ながら entity / column / relation を抽出する流れを示す
- DB 初心者が `JSON のどこが table になるのか` を理解できるようにする

想定 sample:

- `users.json`
- `articles.json`
- `comments.json`

到達点:

- users / articles / comments table
- article -> user
- comment -> article / user
- Data Class publish

### Tutorial B. nested JSON を正規化する

目的:

- nested object / array の扱いを説明する
- そのまま JSON column に残す場合と、別 table にする場合の判断を示す

想定 sample:

- order JSON
- customer
- line items
- shipping address
- payment summary

到達点:

- orders
- order_items
- customers
- addresses または embedded address policy
- DB Access の一覧 / 詳細 / 集計

### Tutorial C. JSON API cache から import / sync model へ

目的:

- external API response と internal canonical table を分ける
- raw snapshot、normalized table、last synced marker を説明する

到達点:

- external source registration
- import preview
- import apply
- sync rerun policy

### Tutorial D. JSON 設定を project metadata に寄せる

目的:

- runtime data と design metadata の違いを説明する
- config_db / project metadata bundle / source output の境界を示す

到達点:

- metadata bundle export / import
- config_db persistence
- generated artifact との境界理解

## ドキュメントとして明文化する候補

### 恒久 doc 候補

- `docs/json-to-db-entrance.md`
  - user / AI / technician が最初に読む optional entrance
  - JSON を DegoDB 設計へ変換する考え方の正本

- `docs/internal/json-to-db-ai-contract.md`
  - AI が JSON から DB 設計案を作るときの判断順
  - source of truth / forbidden shortcuts / output format を定義

- `docs/json-to-db-tutorial.md`
  - optional tutorial の user-facing guide
  - sample pack と接続するなら後で `sample/` へ昇格

### report / roadmap 候補

- この文書:
  - `docs/reports/2026/2026-0617-json-to-db-optional-entrance-roadmap.md`
  - 初期構想に含まれていた位置づけ、明文化方針、段階計画の履歴

## 実装ロードマップ

### Phase 1. 入口思想を恒久文書として明文化する

やること:

- `docs/json-to-db-entrance.md` として初期構想の入口を明文化する
- `docs/choose-your-path.md` に `JSON から DB 管理へ移行したい` 導線を明記する
- `docs/start-here.md` から optional entrance としてリンクする
- この roadmap の `AI 向け最低実行 contract` と `AI output format` を恒久文書へ昇格する

acceptance:

- DB をよく知らないが JSON は読める利用者が、最初の入口を見つけられる
- 既存 DB-first mainline が置き換えられたように見えない
- AI が `docs/json-to-db-entrance.md` だけを読んでも、最低限の design draft を返せる

### Phase 2. AI 向け contract を明文化する

やること:

- `docs/internal/json-to-db-ai-contract.md` として AI 向け最低実行 contract を明文化する
- AI が出すべき成果物 format を固定する
- AI が避けるべきことを明記する
  - JSON を無批判に 1 table / 1 column 化しない
  - raw JSON と canonical table を混ぜない
  - migration と runtime write を同一視しない
  - `original-codes/` を runtime input にしない

acceptance:

- ユーザが JSON sample と処理説明を渡せば、AI が table / relation / DB Access candidate を返せる
- AI の出力が DegoDB の既存 workflow に接続できる
- AI の出力に `blocking questions` と `non-blocking assumptions` が分かれている
- AI が raw JSON retention と canonical table design を区別している

### Phase 3. optional tutorial sample を作る

やること:

- 小さな JSON sample を `sample/` の tutorial lane に置く
- JSON sample から table design notes を作る
- DegoDB metadata へ落として Data Class / DB Access / output publish まで進める

acceptance:

- チュートリアルが `JSON sample -> design notes -> DegoDB project -> generated output` の流れを示せる
- DB 初心者でも、JSON のどこが table / relation / query になったか追える

### Phase 4. UI への接続を検討する

やること:

- UI helper を検討する前に、manual / AI-assisted lane で十分か確認する
- 必要なら `JSON sample paste -> design checklist` 程度の lightweight helper を検討する
- 自動変換ではなく、reviewable draft を補助する UI helper として扱う

acceptance:

- UI が DegoDB の mainline を複雑化しない
- JSON conversion が magic に見えず、設計レビュー可能な draft として提示される

### Phase 5. migration / import lane と統合する

やること:

- JSON から初期データを import する場合の境界を整理する
- schema design と data migration を分ける
- raw JSON archive / normalized import / rerun policy を説明する

acceptance:

- JSON 由来の初期データを安全に DB へ入れる道筋がある
- 既存の external DB source import / project metadata bundle と矛盾しない

## 受け入れ条件

- 既存の DB-first workflow を維持したまま、JSON-first の optional entrance が説明できる
- DB 初心者が `自分の JSON をどう渡せばよいか` を理解できる
- AI / 技術者が `JSON から何を判断すべきか` を共通認識にできる
- optional tutorial として sample 化できる
- DegoDB が担う範囲と、AI / 技術者が担う設計判断の範囲が分かれている
- 最低目標として、AI が恒久 doc を読んだ後に、未知の JSON sample から `JSON to DB Design Draft` を作れる
- AI の draft が DegoDB の table / Data Class / DB Access / Source Output candidate へ接続できる
- AI が情報不足を理由に丸投げせず、進められる仮定と実装前に必要な質問を分離できる

## 判断メモ

この方向性は、DegoDB の利用者層を広げる入口として有効である。

特に `JSON ならなんとなく分かるが、DB 設計は怖い` という利用者に対して、AI が翻訳者として入る余地がある。DegoDB はその翻訳結果を受け取る設計 / 生成 / runtime の出口を提供できる。

したがって、最初の実装は機能追加ではなく、初期構想を次の 3 点として明文化することから始めるのがよい。

1. `docs/json-to-db-entrance.md`
2. `docs/internal/json-to-db-ai-contract.md`
3. optional tutorial sample

この 3 点が揃うと、DegoDB は `DB を分かっている人のための設計ツール` だけでなく、初期構想どおり `JSON 運用を卒業したい人が AI と一緒に DB 設計へ入るためのツール` としても説明できる。
