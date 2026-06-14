# 2026-05-22 Sample07 DBAccess CRUD Basic Pack

## 結論

- `sample/tutorials/sample07-dbaccess-crud-basic/` を追加し、tutorial lane の 7 本目を current 化する。
- sample07 は `TodoItem` 1 table、`TodoItem` 1 db access class、`InsertTodoItem` / `UpdateTodoItem` / `DeleteTodoItem` 3 function に絞り、`insert + update + delete` の最小 write tutorial として固定する。
- `sample06` の list query tutorial から read を増やさず、write metadata で必要になる `insert target fields` / `update target fields` / `update-delete where` だけを次段として追加する。

## 追加したもの

- runtime pack
  - `sample/tutorials/sample07-dbaccess-crud-basic/README.md`
  - `sample/tutorials/sample07-dbaccess-crud-basic/compose.yaml`
  - `sample/tutorials/sample07-dbaccess-crud-basic/run.sh`
  - `sample/tutorials/sample07-dbaccess-crud-basic/seed/`
  - `sample/tutorials/sample07-dbaccess-crud-basic/reference/`
- checker / test
  - `mtool/scripts/check_sample7_dbaccess_crud_basic_outputs.php`
  - `mtool/scripts/lib/sample7_dbaccess_crud_basic_output_check.php`
  - `tests/Integration/Sample7DbAccessCrudBasicOutputTest.php`
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
CREATE TABLE TodoItem (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'open',
    Body TEXT NOT NULL,
    PRIMARY KEY (Id)
);
```

- `project_db_access_classes`
  - `source_name = 'TodoItem'`
- `project_db_access_functions`
  - `InsertTodoItem`
    - `action_type = 'INSERT'`
    - `parameter_type = 'classobject'`
  - `UpdateTodoItem`
    - `action_type = 'UPDATE'`
    - `parameter_type = 'classobject'`
  - `DeleteTodoItem`
    - `action_type = 'DELETE'`
    - `parameter_type = 'classobject'`
- `project_db_access_function_insert_target_fields`
  - `Title`
  - `Status`
  - `Body`
- `project_db_access_function_update_target_fields`
  - `Title`
  - `Status`
  - `Body`
- `project_db_access_function_update_delete_wheres`
  - `Id = argument`

`sample07` は select / join / aggregate をまだ持たない。まず `INSERT` / `UPDATE` / `DELETE` の canonical metadata が generated `DBACCESS-PHP` へどう出るかを固定し、複数 table を読む flow は `sample08` へ送る。

## verification

- published artifacts
  - durable actual copied to reference
    - `DATACLASS-PHP`: `20260522-050835-3fb08fd3`
    - `DBACCESS-PHP`: `20260522-050839-3f161d0e`
  - verification rerun latest publish
    - `DATACLASS-PHP`: `20260522-050919-babbc8ba`
    - `DBACCESS-PHP`: `20260522-050919-02e151fe`
  - `work/source-outputs/SAMPLE07/{DATACLASS-PHP,DBACCESS-PHP}/` を `sample/tutorials/sample07-dbaccess-crud-basic/reference/` へコピーして durable actual output とした
- focused runtime test
  - `make sample07-pack-runtime-test`
  - `OK (1 test, 17 assertions)`
- full suite
  - `make test ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092`
  - `OK (74 tests, 2149 assertions)`
