# JSON To DB AI Contract / JSON から DB 設計案を作る AI contract

English companion:
This contract defines the minimum behavior expected from an AI operator when a user provides JSON samples and asks for a database design draft that can enter the DegoDB mainline.

この文書は、ユーザが JSON sample と現在の処理説明を渡した時に、AI が DegoDB に接続可能な DB 設計案を作るための最小 contract です。

AI は、[../json-to-db-entrance.md](../json-to-db-entrance.md) を user-facing entrance として読み、この文書を実行時の判断順として使います。

## 最低目標

AI が未知の JSON sample を受け取った時、次を返せることを最低目標にします。

- raw JSON として残すもの
- canonical table にするもの
- table / column candidate
- relationship candidate
- lifecycle / transaction boundary
- DegoDB の Data Class / DB Access / Source Output candidate
- blocking questions
- non-blocking assumptions

AI は情報不足を理由に全面停止しません。合理的な仮定で進められる範囲は draft として出し、実装前に確認が必須の不明点だけを blocking に分けます。

## 実行順

1. ユーザの goal、JSON sample、現在の処理説明を読む
2. JSON の用途を分類する
   - persistent application data
   - external API cache
   - configuration / metadata
   - log / audit / raw archive
3. raw JSON として残すものと canonical table にするものを分ける
4. entity candidate を抽出する
5. identity / unique condition を推定する
6. relationship candidate を抽出する
7. lifecycle / transaction boundary を整理する
8. query / update / output requirements を整理する
9. DegoDB target に落とす
   - table
   - Data Class
   - DB Access function
   - Source Output
10. blocking questions と non-blocking assumptions を分ける

## 出力フォーマット

AI は、最低限この形で返します。

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

## 判断ルール

### raw JSON を残すべき場合

- 外部 API response の原本である
- audit / debug / replay に必要である
- shape が頻繁に変わり、canonical model にするには早い
- 一部だけ canonical table へ抽出し、全体は archive したい

### canonical table にすべき場合

- アプリが検索、更新、集計、参照する
- ID または unique condition がある
- lifecycle がある
- 他の object と relationship がある
- transaction boundary に含まれる

### nested object の扱い

- 値のまとまりで、単独の lifecycle がなければ column 群の候補にする
- 繰り返し array なら child table の候補にする
- 他 entity から参照されるなら separate table の候補にする
- shape が不安定なら raw JSON / JSON column / metadata table を候補にする

### ID の扱い

- JSON 内の既存 ID が安定しているなら external id として扱う
- DB 内部の primary key は別に持つ案を優先してよい
- update / delete が必要な entity は identity を曖昧なまま確定しない
- unique condition が推定できない場合は blocking question にする

### transaction boundary の扱い

- 1 回の write で同時に変わる record 群を探す
- order + order_items のように partial write が危険なものは同一 transaction candidate にする
- import / migration と runtime write は分けて書く
- scale out や concurrent update を語る時は conflict policy も書く

## 禁止する近道

- JSON 全体を 1 table の `json` column に閉じ込めて終わらせない
- nested object を常に別 table にするなど、機械的な正規化をしない
- raw import data と canonical runtime data を混ぜない
- migration / import / runtime write を同じ処理として扱わない
- ID が不明なまま update / delete 設計を確定しない
- DegoDB runtime / generator が JSON file を直接 canonical input として読む前提にしない
- `original-codes/` を runtime input に戻さない

## DegoDB への接続条件

draft は、次の条件を満たすと DegoDB mainline に渡せる。

- table candidate が entity 単位で分かれている
- column candidate に source JSON key が残っている
- relationship candidate に根拠がある
- update / delete が必要な table の identity が説明されている
- import / migration と runtime write が分けられている
- Data Class / DB Access / Source Output candidate がある
- 未確定事項が blocking questions と assumptions に分かれている

## 読む順番

JSON から DB 設計案を作る AI は、最低限次の順で読む。

1. [../json-to-db-entrance.md](../json-to-db-entrance.md)
2. この文書
3. [../overview.md](../overview.md)
4. [../existing-db-to-output.md](../existing-db-to-output.md)
5. [../storage-and-state-model.md](../storage-and-state-model.md)

実装内部や repo boundary が必要な時だけ [README.md](README.md) へ戻る。
