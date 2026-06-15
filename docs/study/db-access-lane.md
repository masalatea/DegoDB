# Study: DB Access lane

English companion:
This study note covers the DB Access tutorial lane from `sample05` through `sample09`. It introduces select metadata, filters, pagination, write functions, joins, and aggregate reports in small steps.

DB Access lane は、`sample05` から `sample09` までです。  
Data Class output を読める前提で、select、filter、write、join、aggregate を小さく足していきます。

## 対象 sample

| sample | 主テーマ | test |
| --- | --- | --- |
| `sample05-dbaccess-select-basic` | single-table select | `make sample05-pack-runtime-test` |
| `sample06-dbaccess-filter-sort-page` | filter / sort / pagination | `make sample06-pack-runtime-test` |
| `sample07-dbaccess-crud-basic` | insert / update / delete | `make sample07-pack-runtime-test` |
| `sample08-dbaccess-join-read-model` | join read model | `make sample08-pack-runtime-test` |
| `sample09-dbaccess-aggregate-report` | aggregate / report | `make sample09-pack-runtime-test` |

## study の流れ

各 sample で同じ順番を守ります。

1. `make sampleNN-pack-runtime-test` を実行する
2. sample README で「何を seed しているか」を読む
3. `seed/*db_access_seed.sql` を読む
4. `reference/DBACCESS-PHP/` を読む
5. 前の sample から何が 1 段増えたかだけを言語化する

コマンドは repository root で、1 本ずつ順番に実行します。  
`make sampleNN-pack-runtime-test` が isolated Docker stack を起動して片付けるため、手作業で `run.sh up` を先に実行する必要はありません。

## sample05: select の最小形

```bash
make sample05-pack-runtime-test
```

見る点:

- DB Access class は 1 つ、function も 1 つに絞られている
- where / paging / user supplied sort はまだ入っていない
- `project_db_access_function_select_target_fields` が output の返却 field を決める

## sample06: filter / sort / page

```bash
make sample06-pack-runtime-test
```

見る点:

- `Status` filter が where metadata として足される
- fixed sort と limit を扱う
- 一覧画面で最初に必要になる DB Access の形を見る

## sample07: write flow

```bash
make sample07-pack-runtime-test
```

見る点:

- `InsertTodoItem` / `UpdateTodoItem` / `DeleteTodoItem` の 3 function を見る
- insert / update target fields と update-delete where の違いを見る
- write 系 function は対象 field を絞る、という設計を確認する

## sample08: join read model

```bash
make sample08-pack-runtime-test
```

見る点:

- live table 2 つと read model table 1 つを分けて読む
- join condition は `anotherfield` where として表現される
- output は join result を read model DTO に詰める

## sample09: aggregate report

```bash
make sample09-pack-runtime-test
```

見る点:

- `count` / `sum` / `group by` / `having` の metadata を読む
- report model table は集計結果の受け皿として使う
- aggregate は raw SQL を増やしすぎず、固定された report shape として学ぶ

## 終了条件

次を説明できれば capstone に進めます。

- DB Access class と function の違い
- select target field、where、having、write target field の役割
- single-table、join、aggregate の output がどう違うか

次は [mini-crud-flow.md](mini-crud-flow.md) です。
