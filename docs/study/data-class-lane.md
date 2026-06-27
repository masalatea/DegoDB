# Study: Data Class lane

English companion:
This study note covers the Data Class tutorial lane from `sample02` through `sample04`. It focuses on table shape, field metadata, lookup tables, and parent-child schemas before moving into DB Access design.

Data Class lane は、`sample02` から `sample04` までです。  
ここでは DB Access の設計に進む前に、table shape と Data Class output の読み方を覚えます。

## 対象 sample

| sample | 主テーマ | test |
| --- | --- | --- |
| `sample02-dataclass-nullable-default-status` | nullable / default / status | `make sample02-pack-runtime-test` |
| `sample03-dataclass-lookup-and-helper` | lookup / caption 向き table | `make sample03-pack-runtime-test` |
| `sample04-dataclass-parent-child-basic` | parent-child table / FK | `make sample04-pack-runtime-test` |

コマンドは repository root で、1 本ずつ順番に実行します。

## sample02: nullable / default / status

```bash
make sample02-pack-runtime-test
```

読む場所:

- [../../sample/tutorials/sample02-dataclass-nullable-default-status/README.md](../../sample/tutorials/sample02-dataclass-nullable-default-status/README.md)
- [../../sample/tutorials/sample02-dataclass-nullable-default-status/seed/](../../sample/tutorials/sample02-dataclass-nullable-default-status/seed/)
- [../../sample/tutorials/sample02-dataclass-nullable-default-status/reference/DATACLASS-PHP/](../../sample/tutorials/sample02-dataclass-nullable-default-status/reference/DATACLASS-PHP/)

見る点:

- nullable column が Data Class field としてどう出るか
- default 値や status-like column が field metadata にどう残るか
- DB Access にはまだ進まない、という境界

## sample03: lookup / caption

```bash
make sample03-pack-runtime-test
```

読む場所:

- [../../sample/tutorials/sample03-dataclass-lookup-and-helper/README.md](../../sample/tutorials/sample03-dataclass-lookup-and-helper/README.md)
- [../../sample/tutorials/sample03-dataclass-lookup-and-helper/seed/](../../sample/tutorials/sample03-dataclass-lookup-and-helper/seed/)
- [../../sample/tutorials/sample03-dataclass-lookup-and-helper/reference/DATACLASS-PHP/](../../sample/tutorials/sample03-dataclass-lookup-and-helper/reference/DATACLASS-PHP/)

見る点:

- physical `task_status` / `task_priority` から generated `TaskStatus` / `TaskPriority` Data Class がどう出るか読む
- generated Data Class に application-specific caption logic を詰め込まない
- lookup 表現は後段の formatter / service / custom layer に逃がす前提を理解する

## sample04: parent-child

```bash
make sample04-pack-runtime-test
```

読む場所:

- [../../sample/tutorials/sample04-dataclass-parent-child-basic/README.md](../../sample/tutorials/sample04-dataclass-parent-child-basic/README.md)
- [../../sample/tutorials/sample04-dataclass-parent-child-basic/seed/](../../sample/tutorials/sample04-dataclass-parent-child-basic/seed/)
- [../../sample/tutorials/sample04-dataclass-parent-child-basic/reference/DATACLASS-PHP/](../../sample/tutorials/sample04-dataclass-parent-child-basic/reference/DATACLASS-PHP/)

見る点:

- 1 table ではなく、複数 table を import / sync する
- current Data Class sync では FK から relation object を自動生成しない
- physical `post_comment.post_id` は generated `PostComment.postId` scalar field として同期される

## 終了条件

次を説明できれば DB Access lane に進めます。

- Data Class output は table / column metadata をどう反映しているか
- seed されるものと import / sync で作られるものの違い
- relation や lookup の application logic を generated Data Class に寄せすぎない理由

次は [db-access-lane.md](db-access-lane.md) です。
