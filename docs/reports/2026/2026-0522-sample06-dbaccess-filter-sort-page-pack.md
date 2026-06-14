# 2026-05-22 Sample06 DBAccess Filter Sort Page Pack

## 結論

- `sample/tutorials/sample06-dbaccess-filter-sort-page/` を追加し、tutorial lane の 6 本目を current 化する。
- sample06 は `Announcement` 1 table、`Announcement` 1 db access class、`GetAnnouncementList` 1 function に絞り、`where + fixed sort + limit` を持つ最小 list query tutorial として固定する。
- `sample05` の select-only から、manual `select where` 1 row と `limit_parameter_type=argument` を足して一覧画面の次段へ進めた。

## 追加したもの

- runtime pack
  - `sample/tutorials/sample06-dbaccess-filter-sort-page/README.md`
  - `sample/tutorials/sample06-dbaccess-filter-sort-page/compose.yaml`
  - `sample/tutorials/sample06-dbaccess-filter-sort-page/run.sh`
  - `sample/tutorials/sample06-dbaccess-filter-sort-page/seed/`
  - `sample/tutorials/sample06-dbaccess-filter-sort-page/reference/`
- checker / test
  - `mtool/scripts/check_sample6_dbaccess_filter_sort_page_outputs.php`
  - `mtool/scripts/lib/sample6_dbaccess_filter_sort_page_output_check.php`
  - `tests/Integration/Sample6DbAccessFilterSortPageOutputTest.php`
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
CREATE TABLE Announcement (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'draft',
    PublishedAt DATETIME NULL,
    PRIMARY KEY (Id)
);
```

- `project_db_access_classes`
  - `source_name = 'Announcement'`
- `project_db_access_functions`
  - `function_name = 'GetAnnouncementList'`
  - `action_type = 'SELECTLIST'`
  - `sort_order_columns = 'Announcement.PublishedAt desc, Announcement.Id desc'`
  - `limit_parameter_type = 'argument'`
- `project_db_access_function_select_target_fields`
  - `Id`
  - `Title`
  - `Status`
  - `PublishedAt`
- `project_db_access_function_select_wheres`
  - `Status = argument`

`sample06` は join や aggregate はまだ持たない。まず `dafuncselectwhere` / `sort_order_columns` / `limit_parameter_type` が generated `DBACCESS-PHP` へどう出るかを固定し、複合 filter や aggregate は `sample08` / `sample09` へ送る。

## verification

- published artifacts
  - durable actual copied to reference
    - `DATACLASS-PHP`: `20260522-045212-1abb31c9`
    - `DBACCESS-PHP`: `20260522-045324-ee96280c`
  - verification rerun latest publish
    - `DATACLASS-PHP`: `20260522-045415-8d4b537f`
    - `DBACCESS-PHP`: `20260522-045415-36f86ad5`
  - `work/source-outputs/SAMPLE06/{DATACLASS-PHP,DBACCESS-PHP}/` を `sample/tutorials/sample06-dbaccess-filter-sort-page/reference/` へコピーして durable actual output とした
- focused runtime test
  - `make sample06-pack-runtime-test`
  - `OK (1 test, 14 assertions)`
- full suite
  - `make test ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092`
  - `OK (73 tests, 2107 assertions)`
