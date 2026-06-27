# sample02-dataclass-nullable-default-status

- canonical project key: `SAMPLE02`
- 役割: `project -> live schema import -> data class sync -> data class output` を、nullable / default / status-like column を含む 1 table で確認する tutorial sample pack
- seed は `SAMPLE02` project と、source schema 側の物理 `task` table、`DATACLASS-PHP` source output definition を作る
- `project_db_access_*` metadata は seed しない。`sample01` の次に、Data Class metadata と generated output の読み方へ絞って進む
- durable input: `seed/`
- durable actual output sample: `reference/DATACLASS-PHP/data-Task.php`, `reference/DATACLASS-PHP/base/data-TaskBase.php`
- disposable runtime root: `work/sample-packs/sample02-dataclass-nullable-default-status/`

起動:

```bash
./sample/tutorials/sample02-dataclass-nullable-default-status/run.sh up
```

seed を既存環境へ適用:

```bash
./sample/tutorials/sample02-dataclass-nullable-default-status/run.sh apply-seed
```

検証:

```bash
make sample02-pack-runtime-test
```

`sample02-pack-runtime-test` は container 内 PHPUnit で `tests/Integration/Sample2DataclassNullableDefaultStatusOutputTest.php` を実行します。

最小フロー:

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample02-dataclass-nullable-default-status/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE02 --source=live-schema --table=task

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample02-dataclass-nullable-default-status/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE02

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample02-dataclass-nullable-default-status/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE02 --source-output-key=DATACLASS-PHP --requested-by=sample02-pack --publish
```

生成物:

```text
work/source-outputs/SAMPLE02/DATACLASS-PHP/data-Task.php
work/source-outputs/SAMPLE02/DATACLASS-PHP/base/data-TaskBase.php
```
