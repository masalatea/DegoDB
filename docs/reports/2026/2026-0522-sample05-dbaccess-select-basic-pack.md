# 2026-05-22 Sample05 DBAccess Select Basic Pack

## 結論

- `sample/tutorials/sample05-dbaccess-select-basic/` を追加し、tutorial lane の 5 本目を current 化する。
- sample05 は `Notice` 1 table、`Notice` 1 db access class、`GetNoticeList` 1 function に絞り、最小の selectlist DB Access output を確認する tutorial として固定する。
- `sample01` の CRUD 全量ではなく、DB Access metadata の最小単位だけを切り出して `sample05` の主題にした。

## 追加したもの

- runtime pack
  - `sample/tutorials/sample05-dbaccess-select-basic/README.md`
  - `sample/tutorials/sample05-dbaccess-select-basic/compose.yaml`
  - `sample/tutorials/sample05-dbaccess-select-basic/run.sh`
  - `sample/tutorials/sample05-dbaccess-select-basic/seed/`
  - `sample/tutorials/sample05-dbaccess-select-basic/reference/`
- checker / test
  - `mtool/scripts/check_sample5_dbaccess_select_basic_outputs.php`
  - `mtool/scripts/lib/sample5_dbaccess_select_basic_output_check.php`
  - `tests/Integration/Sample5DbAccessSelectBasicOutputTest.php`
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
CREATE TABLE Notice (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Body TEXT NOT NULL,
    SortOrder INT NOT NULL DEFAULT 0,
    PRIMARY KEY (Id)
);
```

- `project_db_access_classes`
  - `source_name = 'Notice'`
- `project_db_access_functions`
  - `function_name = 'GetNoticeList'`
  - `action_type = 'SELECTLIST'`
  - `sort_order_columns = 'Notice.SortOrder, Notice.Id'`
- `project_db_access_function_select_target_fields`
  - `Id`
  - `Title`
  - `Body`
  - `SortOrder`

`sample05` は where / paging / user-supplied sort をまだ持たない。`da` / `dafunc` と generated `DBACCESS-PHP` の最小対応だけを確認し、条件付き select は `sample06` へ送る。

## verification

- published artifacts
  - `DATACLASS-PHP`: `20260522-030055-1fe3d1ed`
  - `DBACCESS-PHP`: `20260522-030103-08f02ac8`
  - `work/source-outputs/SAMPLE05/{DATACLASS-PHP,DBACCESS-PHP}/` を `sample/tutorials/sample05-dbaccess-select-basic/reference/` へコピーして durable actual output とした
- focused runtime test
  - `make sample05-pack-runtime-test`
  - `OK (1 test, 13 assertions)`
- full suite
  - `make test ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092`
  - `OK (72 tests, 2068 assertions)`
