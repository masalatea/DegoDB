# 2026-05-22 Sample02 Slice Plan

## Status

- slice: `DONE`
- status updated at: `2026-05-27`
- completion basis:
  - `sample/tutorials/sample02-dataclass-nullable-default-status/`
  - `tests/Integration/Sample2DataclassNullableDefaultStatusOutputTest.php`
  - `make sample02-pack-runtime-test`
  - `docs/sample-tutorial-roadmap.md`
- note:
  - `sample02-dataclass-nullable-default-status` は tutorial lane の current pack として定着済み。

## 結論

- tutorial lane の次着手は `sample02-dataclass-nullable-default-status` とする。
- `sample01` が end-to-end の first touch なので、`sample02` は DB Access を入れず、Data Class に絞った最小 tutorial pack にする。
- 題材は 1 table だけにし、nullable / default / bool / status-like column を 1 つずつ含める。

## pack 概要

- pack 名: `sample02-dataclass-nullable-default-status`
- project key: `SAMPLE02`
- 役割:
  - live schema import から `dataclass` / `dataclassfields` sync を行い、nullable / default / status-like column がどう Data Class output に写るかを見る
  - `sample01` より一歩進んだ column variation を user-facing に説明する
- 非対象:
  - DB Access metadata
  - join / relation
  - proxy / HTML / LanguageResource

## 想定 schema

```sql
CREATE TABLE Task (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'draft',
    SortOrder INT NOT NULL DEFAULT 0,
    IsPinned TINYINT(1) NOT NULL DEFAULT 0,
    PublishedAt DATETIME NULL,
    Note TEXT NULL,
    PRIMARY KEY (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

この 1 table で次を一度に見られる。

- `Status`
  - status-like string + default 値
- `SortOrder`
  - numeric default 値
- `IsPinned`
  - bool 相当の tinyint default 値
- `PublishedAt`
  - nullable datetime
- `Note`
  - nullable text

## source output 範囲

- `DATACLASS-PHP` のみ seed する
- `DBACCESS-PHP` は入れない
- これにより tutorial の論点を `import -> data class sync -> data class output` に限定する

## 想定ファイル

- `sample/tutorials/sample02-dataclass-nullable-default-status/README.md`
- `sample/tutorials/sample02-dataclass-nullable-default-status/compose.yaml`
- `sample/tutorials/sample02-dataclass-nullable-default-status/run.sh`
- `sample/tutorials/sample02-dataclass-nullable-default-status/seed/900_010_sample2_project_seed.sql`
- `sample/tutorials/sample02-dataclass-nullable-default-status/seed/900_020_sample2_table_seed.sql`
- `sample/tutorials/sample02-dataclass-nullable-default-status/seed/900_030_sample2_source_output_seed.sql`
- `sample/tutorials/sample02-dataclass-nullable-default-status/reference/DATACLASS-PHP/data-Task.php`
- `sample/tutorials/sample02-dataclass-nullable-default-status/reference/DATACLASS-PHP/base/data-TaskBase.php`
- `mtool/scripts/check_sample2_dataclass_nullable_default_status_outputs.php`
- `mtool/scripts/lib/sample2_dataclass_nullable_default_status_output_check.php`
- `tests/Integration/Sample2DataclassNullableDefaultStatusOutputTest.php`

## 想定 Make target

- canonical:
  - `sample02-pack-runtime-test`
- `make test` への編入は、実装後に suite 時間を見て判断する

## 最小実行フロー

```bash
./sample/tutorials/sample02-dataclass-nullable-default-status/run.sh up
./sample/tutorials/sample02-dataclass-nullable-default-status/run.sh apply-seed

docker compose -f compose.yaml -f sample/tutorials/sample02-dataclass-nullable-default-status/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE02 --source=live-schema --table=Task

docker compose -f compose.yaml -f sample/tutorials/sample02-dataclass-nullable-default-status/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE02

docker compose -f compose.yaml -f sample/tutorials/sample02-dataclass-nullable-default-status/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE02 --source-output-key=DATACLASS-PHP --requested-by=sample02-pack --publish
```

## テスト観点

- generated file compare
  - `reference/DATACLASS-PHP/data-Task.php`
  - `reference/DATACLASS-PHP/base/data-TaskBase.php`
- canonical metadata sanity
  - `dataclass` が `Task` 1 件だけ生成される
  - `dataclassfields` が 7 field 分できる
  - nullable 列 (`PublishedAt`, `Note`) が nullable として残る
  - default 値持ち列 (`Status`, `SortOrder`, `IsPinned`) が field metadata と generated output に反映される

## 実装メモ

- `run.sh` と `compose.yaml` は `sample01` を土台にしてよい
- `sample01` と違い `project_db_access_*` seed は不要
- `project_source_outputs` は `DATACLASS-PHP` 1 件だけでよい
- test helper も `sample1_simple_table` 系を最小コピーして `DATACLASS-PHP` 単独比較に寄せる
- `mtool/app/sample_pack_catalog.php` へ pack を追加したら、`tests/Integration/SamplePackCatalogTest.php` の tutorials order も更新する

## 次アクション

1. `[DONE]` pack directory を作る
2. `[DONE]` `sample01` を複製して `SAMPLE02` / `Task` / `DATACLASS-PHP only` に絞る
3. `[DONE]` reference を生成して `tests/Integration/Sample2DataclassNullableDefaultStatusOutputTest.php` を追加する
4. `[DONE]` `Makefile` と catalog / README を更新する
5. `[DONE]` `make sample02-pack-runtime-test` を green にする
