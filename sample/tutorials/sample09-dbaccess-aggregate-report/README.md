# sample09-dbaccess-aggregate-report

- canonical project key: `SAMPLE09`
- 役割: `project -> live schema import -> data class sync -> db access output` を、2 live table + 1 report model table + 1 aggregate function で確認する tutorial sample pack
- seed は `SAMPLE09` project と、source schema 側の物理 `SalesCategory` / `SalesRecord` / `SalesCategoryReport` table、canonical `project_db_access_*` metadata 1 class / 1 function / select target fields / select wheres / select havings、`DATACLASS-PHP` / `DBACCESS-PHP` source output definition を作る
- canonical `dbtable` / `dataclass` metadata は seed しない。table import と data class sync で current metadata を作る前提
- `SalesCategoryReport` は aggregate report 用の DTO shape table であり、DB access function は `SalesRecord` と `SalesCategory` を join して `count` / `sum` / `group by` / `having` をかけた row をこの Data Class へ詰める
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

- `SalesCategory`
  - `Name=Hardware`, `IsActive=1`
  - `Name=Software`, `IsActive=1`
  - `Name=Legacy`, `IsActive=0`
- `SalesRecord`
  - `SalesCategoryId=1`, `Status=closed`, `Amount=120.00`
  - `SalesCategoryId=1`, `Status=closed`, `Amount=80.00`
  - `SalesCategoryId=2`, `Status=closed`, `Amount=40.00`
  - `SalesCategoryId=2`, `Status=open`, `Amount=500.00`
  - `SalesCategoryId=3`, `Status=closed`, `Amount=999.00`

最小フロー:

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample09-dbaccess-aggregate-report/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE09 --source=live-schema --table=SalesCategory

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample09-dbaccess-aggregate-report/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE09 --source=live-schema --table=SalesRecord

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample09-dbaccess-aggregate-report/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE09 --source=live-schema --table=SalesCategoryReport

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
