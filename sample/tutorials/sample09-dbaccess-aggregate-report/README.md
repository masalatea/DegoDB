# sample09-dbaccess-aggregate-report

- canonical project key: `SAMPLE09`
- 役割: `project -> live schema import -> data class sync -> db access output` を、2 live table + 1 report model table + 1 aggregate function で確認する tutorial sample pack
- seed は `SAMPLE09` project と、source schema 側の物理 `sales_category` / `sales_record` / `sales_category_report` table、canonical `project_db_access_*` metadata 1 class / 1 function / select target fields / select wheres / select havings、`DATACLASS-PHP` / `DBACCESS-PHP` source output definition を作る
- canonical `dbtable` / `dataclass` metadata は seed しない。table import と data class sync で current metadata を作る前提
- `SalesCategoryReport` は aggregate report 用の DTO shape table であり、DB access function は物理 `sales_record` と `sales_category` を join して `count` / `sum` / `group by` / `having` をかけた row をこの Data Class へ詰める
- この sample は physical DB name を `snake_case`、generated PHP class/file surface を `SalesRecord` 系に分ける migrated sample として `physical-logical-v1` generated-name policy で検証する
- durable input: `seed/`
- durable actual output sample: `reference/DATACLASS-PHP/data-SalesCategory.php`, `reference/DATACLASS-PHP/data-SalesRecord.php`, `reference/DATACLASS-PHP/data-SalesCategoryReport.php`, `reference/DBACCESS-PHP/dbaccess-SalesRecord.php`
- disposable runtime root: `work/sample-packs/sample09-dbaccess-aggregate-report/`

起動:

```bash
./sample/tutorials/sample09-dbaccess-aggregate-report/run.sh up
```

seed を既存環境へ適用:

```bash
./sample/tutorials/sample09-dbaccess-aggregate-report/run.sh apply-seed
```

検証:

```bash
make sample09-pack-runtime-test
```

`sample09-pack-runtime-test` は container 内 PHPUnit で `tests/Integration/Sample09DbAccessAggregateReportOutputTest.php` を実行します。

seed される代表 row:

- `sales_category`
  - `name=Hardware`, `is_active=1`
  - `name=Software`, `is_active=1`
  - `name=Legacy`, `is_active=0`
- `sales_record`
  - `sales_category_id=1`, `status=closed`, `amount=120.00`
  - `sales_category_id=1`, `status=closed`, `amount=80.00`
  - `sales_category_id=2`, `status=closed`, `amount=40.00`
  - `sales_category_id=2`, `status=open`, `amount=500.00`
  - `sales_category_id=3`, `status=closed`, `amount=999.00`

最小フロー:

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample09-dbaccess-aggregate-report/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE09 --source=live-schema --table=sales_category

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample09-dbaccess-aggregate-report/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE09 --source=live-schema --table=sales_record

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample09-dbaccess-aggregate-report/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE09 --source=live-schema --table=sales_category_report

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample09-dbaccess-aggregate-report/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE09

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample09-dbaccess-aggregate-report/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE09 --source-output-key=DATACLASS-PHP --requested-by=sample09-pack --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample09-dbaccess-aggregate-report/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE09 --source-output-key=DBACCESS-PHP --requested-by=sample09-pack --publish
```

生成物:

```text
work/source-outputs/SAMPLE09/DATACLASS-PHP/data-SalesCategory.php
work/source-outputs/SAMPLE09/DATACLASS-PHP/data-SalesRecord.php
work/source-outputs/SAMPLE09/DATACLASS-PHP/data-SalesCategoryReport.php
work/source-outputs/SAMPLE09/DBACCESS-PHP/dbaccess-SalesRecord.php
```
