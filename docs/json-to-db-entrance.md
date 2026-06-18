# JSON To DB Entrance / JSON から DB 設計へ入る入口

English companion:
This optional entrance is part of the original DegoDB concept for users who currently store data as JSON and want an AI or engineer to translate that working shape into a DegoDB-ready database design draft. It does not replace the DB-first mainline; it prepares the user and the AI to enter it.

この文書は、現在 JSON file / JSON API cache / JSON config としてデータを扱っている人が、DegoDB の `DB 構造 -> import -> Data Class -> DB Access -> Source Output` へ入るための optional entrance です。この入口は後付けの新機能ではなく、初期構想に含まれていた前段の考え方を明文化したものです。

DegoDB の基本導線は DB-first のままです。  
ただし、DB 設計に慣れていない利用者でも、JSON の形と現在の処理を AI / 技術者へ渡せば、DegoDB に接続可能な DB 設計案へ進めることを最低目標にします。

この文書は思想と指示の文書です。  
JSON 自動変換機能、JSON import 機能、runtime 機能の説明ではありません。

## この入口の役割

この入口は、JSON を runtime input として直接使う機能ではありません。

役割は次の 3 つです。

1. ユーザが現在の JSON と処理を説明できる形にする
2. AI / 技術者が entity、column、relationship、lifecycle、transaction boundary を判断できる形にする
3. その判断結果を DegoDB の table / Data Class / DB Access / Source Output candidate へ接続する

## 思想と機能の境界

この入口は、DegoDB の思想上の入口です。  
DegoDB の機能上の入口は、引き続き import 可能な DB schema または canonical metadata です。

| 区分 | この文書で扱うか | 説明 |
| --- | --- | --- |
| JSON sample を読んで設計案を作る | yes | AI / 技術者向けの思考手順 |
| raw JSON と canonical table を分ける | yes | 設計判断のための分類 |
| table / column / relation candidate を出す | yes | reviewable draft として出す |
| JSON を自動で DB schema に変換する | no | DegoDB 機能として約束しない |
| JSON を runtime / generator の canonical input にする | no | mainline は変えない |
| import / sync / publish を実行する | existing docs | [existing-db-to-output.md](existing-db-to-output.md) の範囲 |

文書上は、次の表現を使います。

- `JSON to DB design draft`
- `AI-assisted translation`
- `optional entrance`
- `reviewable draft`

次の表現は避けます。

- `automatic JSON conversion`
- `JSON import feature`
- `final schema generation`
- `JSON-first mainline`

## 対象ユーザ

- JSON なら見ればなんとなく分かる
- サーバ上で JSON を parse / encode して処理している
- JSON file や JSON API cache が増えて管理がつらくなってきた
- DB の正規化、永続化、transaction、scale out はよく分からない
- AI に設計を手伝わせて、DegoDB の mainline に入りたい

## 対象外

- JSON をそのまま magic import して完全な DB 設計へ自動変換すること
- DegoDB runtime / generator が JSON file を canonical input として読むこと
- raw JSON と canonical table を混ぜたまま運用すること
- DB 設計レビューなしで production migration まで進めること

## 最短の流れ

1. ユーザが JSON sample と現在の処理を用意する
2. AI が [internal/json-to-db-ai-contract.md](internal/json-to-db-ai-contract.md) を読んで design draft を作る
3. 人間が blocking questions と assumptions を確認する
4. table / column / relation candidate を DegoDB の DB design として入力する
5. 以降は [existing-db-to-output.md](existing-db-to-output.md) の mainline に入る

## ユーザが AI に渡すもの

最初は完璧でなくてよいです。次の情報があるほど、AI は実装可能な設計案に近づけます。

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

## AI が返すもの

AI は、少なくとも次の design draft を返します。  
詳細な判断順は [internal/json-to-db-ai-contract.md](internal/json-to-db-ai-contract.md) を正本にします。

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

## 判断の考え方

### raw JSON と canonical table を分ける

JSON は次のどちらかに分類します。

- raw JSON として保存するもの
  - 外部 API response の snapshot
  - audit / debug / replay 用の原本
  - 変化が激しく、まだ canonical model にできない領域
- canonical table にするもの
  - アプリが検索、更新、集計、参照する主要 entity
  - ID や lifecycle があるもの
  - relation や transaction boundary を持つもの

### entity を見つける

- top-level object は table 候補か
- array item は record 候補か
- nested object は column にするか、別 table にするか
- key-value 的な可変構造は metadata table / JSON column / separate table のどれがよいか

### identity を決める

- 既存 JSON に安定した ID があるか
- ID がない場合、DB 側で surrogate key を持つか
- 外部 ID と内部 ID を分ける必要があるか
- unique constraint に相当する条件は何か

### relationship を明示する

- parent-child
- one-to-many
- many-to-many
- lookup / enum
- denormalized snapshot

### lifecycle と transaction boundary を読む

- 作成、更新、削除の単位
- soft delete が必要か
- 履歴を残すか
- import の再実行に耐えるか
- 1 回の操作で同時に更新される record 群は何か
- partial write を許してよいか

## DegoDB mainline への接続

AI の draft ができたら、次は DegoDB の通常導線に入ります。

1. `Canonical Tables` と `Columns` を table / column 設計として確定する
2. 必要なら Lab DB で schema を試す
3. [existing-db-to-output.md](existing-db-to-output.md) に沿って canonical metadata へ import する
4. Data Class sync を実行する
5. DB Access function を設計する
6. Source Output を publish / verify する

Lab DB で schema を試してから取り込む short loop は [common-tasks.md#lab-db-の-schema-を試してから取り込む](common-tasks.md#lab-db-の-schema-を試してから取り込む) を参照します。

## チュートリアル化の方向

最初の tutorial は、機能追加ではなく、初期構想の使い方を示す読み物 + sample で十分です。

- JSON file から table 設計へ
- nested JSON を正規化する
- JSON API cache を raw snapshot と canonical table に分ける
- JSON config を project metadata / config DB / source output の責務へ分ける

この入口の目的は、DB 初心者に完全な DB 理論を教えることではありません。  
JSON の見た目から DegoDB の DB-first mainline に入れるところまで、AI が翻訳できる状態にすることです。
