# sample03-dataclass-lookup-and-helper

- canonical project key: `SAMPLE03`
- 役割: `project -> live schema import -> data class sync -> data class output` を、lookup / caption 向きの 2 table で確認する tutorial sample pack
- seed は `SAMPLE03` project と、source schema 側の物理 `TaskStatus` / `TaskPriority` table、`DATACLASS-PHP` source output definition を作る
- この sample でいう `helper` は generated Data Class へ追加する独自メソッドではなく、lookup/caption を後段の formatter / service / custom layer へ逃がす前提を指す
- `project_db_access_*` metadata は seed しない。`sample04` の parent/child や `sample05` の DB Access へ進む前に、複数 Data Class の同期と naming を小さく確認する
- durable input: `seed/`
- durable actual output sample: `reference/DATACLASS-PHP/data-TaskPriority.php`, `reference/DATACLASS-PHP/base/data-TaskPriorityBase.php`, `reference/DATACLASS-PHP/data-TaskStatus.php`, `reference/DATACLASS-PHP/base/data-TaskStatusBase.php`
- disposable runtime root: `work/sample-packs/sample03-dataclass-lookup-and-helper/`

起動:

```bash
./sample/tutorials/sample03-dataclass-lookup-and-helper/run.sh up
```

seed を既存環境へ適用:

```bash
./sample/tutorials/sample03-dataclass-lookup-and-helper/run.sh apply-seed
```

検証:

```bash
make sample03-pack-runtime-test
```

`sample03-pack-runtime-test` は container 内 PHPUnit で `tests/Integration/Sample3DataclassLookupAndHelperOutputTest.php` を実行します。

seed される代表 row:

- `TaskStatus`
  - `draft`
  - `ready`
  - `done`
- `TaskPriority`
  - `low`
  - `normal`
  - `high`

最小フロー:

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample03-dataclass-lookup-and-helper/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE03 --source=live-schema --table=TaskStatus

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample03-dataclass-lookup-and-helper/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE03 --source=live-schema --table=TaskPriority

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample03-dataclass-lookup-and-helper/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE03

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample03-dataclass-lookup-and-helper/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE03 --source-output-key=DATACLASS-PHP --requested-by=sample03-pack --publish
```

生成物:

```text
work/source-outputs/SAMPLE03/DATACLASS-PHP/data-TaskPriority.php
work/source-outputs/SAMPLE03/DATACLASS-PHP/base/data-TaskPriorityBase.php
work/source-outputs/SAMPLE03/DATACLASS-PHP/data-TaskStatus.php
work/source-outputs/SAMPLE03/DATACLASS-PHP/base/data-TaskStatusBase.php
```
