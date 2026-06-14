# 2026-05-22 Sample09 DBAccess Aggregate Report Pack

## 結論

- `sample/tutorials/sample09-dbaccess-aggregate-report/` を追加し、tutorial lane の 9 本目を current 化した。
- sample09 は `SalesCategory` / `SalesRecord` の 2 live table と、report DTO shape 用の `SalesCategoryReport` table を使い、join + `group by` + `count` + `sum` + `having` を 1 function へまとめた最小 aggregate tutorial に固定した。
- `sample08` の joined select tutorial の次段として、`project_db_access_function_select_target_fields` の `group_by_target`、`project_db_access_function_select_havings`、aggregate expression prefix/suffix をまとめて確認できる状態にした。

## 追加したもの

- runtime pack
  - `sample/tutorials/sample09-dbaccess-aggregate-report/README.md`
  - `sample/tutorials/sample09-dbaccess-aggregate-report/compose.yaml`
  - `sample/tutorials/sample09-dbaccess-aggregate-report/run.sh`
  - `sample/tutorials/sample09-dbaccess-aggregate-report/seed/`
  - `sample/tutorials/sample09-dbaccess-aggregate-report/reference/`
- checker / test
  - `mtool/scripts/check_sample09_dbaccess_aggregate_report_outputs.php`
  - `mtool/scripts/lib/sample09_dbaccess_aggregate_report_output_check.php`
  - `tests/Integration/Sample09DbAccessAggregateReportOutputTest.php`
- catalog / docs / target
  - `mtool/app/sample_pack_catalog.php`
  - `tests/Integration/SamplePackCatalogTest.php`
  - `tests/bootstrap.php`
  - `Makefile`
  - `sample/README.md`
  - `sample/tutorials/README.md`
  - `tests/README.md`
  - `tests/Integration/README.md`
  - `docs/sample-tutorial-roadmap.md`

## schema / metadata

```sql
CREATE TABLE SalesCategory (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Name VARCHAR(255) NOT NULL,
    IsActive TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (Id)
);

CREATE TABLE SalesRecord (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    SalesCategoryId BIGINT UNSIGNED NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'open',
    Amount DECIMAL(10, 2) NOT NULL,
    PRIMARY KEY (Id)
);

CREATE TABLE SalesCategoryReport (
    SalesCategoryId BIGINT UNSIGNED NOT NULL,
    SalesCategoryName VARCHAR(255) NOT NULL,
    ClosedSaleCount BIGINT UNSIGNED NOT NULL,
    ClosedSaleTotalAmount DECIMAL(10, 2) NOT NULL,
    PRIMARY KEY (SalesCategoryId)
);
```

- `project_db_access_classes`
  - `source_name = 'SalesRecord'`
- `project_db_access_functions`
  - `GetClosedSalesCategoryReportList`
    - `action_type = 'SELECTLIST'`
    - `data_class_base_name = 'SalesCategoryReport'`
    - `target_table_name = 'SalesRecord'`
- `project_db_access_function_select_target_fields`
  - `SalesRecord.SalesCategoryId -> SalesCategoryId` (`group_by_target = 1`)
  - `SalesCategory.Name -> SalesCategoryName` (`group_by_target = 1`)
  - `count(SalesRecord.Id) -> ClosedSaleCount`
  - `sum(SalesRecord.Amount) -> ClosedSaleTotalAmount`
- `project_db_access_function_select_wheres`
  - `SalesRecord.SalesCategoryId = SalesCategory.Id` (`parameter_type = anotherfield`, `join_type = inner`)
  - `SalesRecord.Status = 'closed'`
  - `SalesCategory.IsActive = 1` (`parameter_data_type = raw`)
- `project_db_access_function_select_havings`
  - `count(SalesRecord.Id) >= 2`
  - `sum(SalesRecord.Amount) >= 100`

生成された canonical SQL は次の形になった。

```sql
select SalesRecord.SalesCategoryId, SalesCategory.Name, count(SalesRecord.Id), sum(SalesRecord.Amount)
from SalesRecord join SalesCategory on SalesRecord.SalesCategoryId = SalesCategory.Id
where SalesRecord.Status = 'closed' and SalesCategory.IsActive = 1
group by SalesRecord.SalesCategoryId, SalesCategory.Name
having count(SalesRecord.Id) >= 2 and sum(SalesRecord.Amount) >= 100
order by sum(SalesRecord.Amount) desc, SalesRecord.SalesCategoryId asc
```

## verification

- published artifacts
  - `DATACLASS-PHP`: `20260522-055235-40d3ee04`
  - `DBACCESS-PHP`: `20260522-055235-b7c52644`
  - `work/source-outputs/SAMPLE09/{DATACLASS-PHP,DBACCESS-PHP}/` を `sample/tutorials/sample09-dbaccess-aggregate-report/reference/` へコピーして durable actual output とした
- focused runtime test
  - `make sample09-pack-runtime-test`
  - `OK (1 test, 24 assertions)`
- full suite
  - `ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test`
  - `OK (76 tests, 2246 assertions)`
