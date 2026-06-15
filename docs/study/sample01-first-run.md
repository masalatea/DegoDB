# Study: sample01 first run

English companion:
This study note uses `sample01-simple-table-runtime` as the smallest end-to-end walkthrough. It shows what to run, what to inspect, and what the first generated Data Class and DB Access files mean.

`sample01-simple-table-runtime` は、最初に触る 1 table の end-to-end sample です。  
ここでは「DegoDB が何を入力にして、何を canonical metadata にし、何を出力するのか」を最小単位で見ます。

## 目的

- `Article` table を import する流れを見る
- Data Class と DB Access の output がどこに出るか確認する
- seed / reference / actual output の役割を分けて理解する

## まず実行する

repository root で実行します。

```bash
make sample01-pack-runtime-test
```

この test は専用 Docker stack を起動し、seed を入れ、import / sync / output / reference compare を通します。  
成功したら、`sample01` の教材としての前提は整っています。

## 次に読むファイル

- sample の説明: [../../sample/tutorials/sample01-simple-table-runtime/README.md](../../sample/tutorials/sample01-simple-table-runtime/README.md)
- seed: [../../sample/tutorials/sample01-simple-table-runtime/seed/](../../sample/tutorials/sample01-simple-table-runtime/seed/)
- expected output: [../../sample/tutorials/sample01-simple-table-runtime/reference/](../../sample/tutorials/sample01-simple-table-runtime/reference/)

## 見る順番

1. `seed/900_010_sample1_project_seed.sql`
   - `SAMPLE1` project と source output 定義の入口を見る
2. `seed/900_020_sample1_table_and_data_class_seed.sql`
   - source schema 側の `Article` table を見る
3. `seed/900_025_sample1_db_access_seed.sql`
   - DB Access class / function の最小 metadata を見る
4. `reference/DATACLASS-PHP/`
   - `Article` table が Data Class としてどう出るか見る
5. `reference/DBACCESS-PHP/`
   - CRUD 入口の DB Access class がどう出るか見る

## 学習ポイント

- `dbtable` / `dataclass` metadata は seed ではなく import / sync で作られる
- `project_db_access_*` metadata は DB Access output の設計情報になる
- `reference/` は説明用の手書き例ではなく、actual output の比較対象である

## 次へ

`sample01` が読めたら、[data-class-lane.md](data-class-lane.md) に進みます。  
Data Class だけに集中し、nullable、lookup、親子 table を順番に増やします。
