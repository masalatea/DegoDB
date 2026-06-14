# 2026-05-22 Sample10 DBAccess Mini CRUD Flow Pack

## 結論

- `sample/tutorials/sample10-dbaccess-mini-crud-flow/` を追加し、tutorial lane の 10 本目を current 化した。
- sample10 は `SupportTicket` 1 table と、`GetSupportTicketList` / `GetSupportTicket` / `InsertSupportTicket` / `UpdateSupportTicket` / `DeleteSupportTicket` の 5 function を 1 class にまとめ、small but real な `list + detail + create + update + delete` flow を固定した。
- historical な `sample10-output-test` が pattern lane で使われ続けているため、tutorial 側の canonical target は `sample10-pack-runtime-test` / `sample10-runtime-output-test` として分離した。

## 追加したもの

- runtime pack
  - `sample/tutorials/sample10-dbaccess-mini-crud-flow/README.md`
  - `sample/tutorials/sample10-dbaccess-mini-crud-flow/compose.yaml`
  - `sample/tutorials/sample10-dbaccess-mini-crud-flow/run.sh`
  - `sample/tutorials/sample10-dbaccess-mini-crud-flow/seed/`
  - `sample/tutorials/sample10-dbaccess-mini-crud-flow/reference/`
- checker / test
  - `mtool/scripts/check_sample10_dbaccess_mini_crud_flow_outputs.php`
  - `mtool/scripts/lib/sample10_dbaccess_mini_crud_flow_output_check.php`
  - `tests/Integration/Sample10DbAccessMiniCrudFlowOutputTest.php`
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
  - `docs/reports/2026/README.md`

## schema / metadata

```sql
CREATE TABLE SupportTicket (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'open',
    AssignedTo VARCHAR(100) NOT NULL DEFAULT '',
    Body TEXT NOT NULL,
    UpdatedAt DATETIME NOT NULL,
    PRIMARY KEY (Id)
);
```

- `project_db_access_classes`
  - `source_name = 'SupportTicket'`
- `project_db_access_functions`
  - `GetSupportTicketList`
    - `action_type = 'SELECTLIST'`
    - `sort_order_columns = 'SupportTicket.UpdatedAt desc, SupportTicket.Id desc'`
    - `limit_parameter_type = 'argument'`
  - `GetSupportTicket`
    - `action_type = 'SELECTSINGLE'`
  - `InsertSupportTicket`
    - `action_type = 'INSERT'`
    - `parameter_type = 'classobject'`
  - `UpdateSupportTicket`
    - `action_type = 'UPDATE'`
    - `parameter_type = 'classobject'`
  - `DeleteSupportTicket`
    - `action_type = 'DELETE'`
    - `parameter_type = 'classobject'`
- `project_db_access_function_select_target_fields`
  - list: `Id`, `Title`, `Status`, `AssignedTo`, `UpdatedAt`
  - detail: `Id`, `Title`, `Status`, `AssignedTo`, `Body`, `UpdatedAt`
- `project_db_access_function_select_wheres`
  - list: `Status = argument`
  - detail: `Id = argument`
- `project_db_access_function_insert_target_fields`
  - `Title`
  - `Status`
  - `AssignedTo`
  - `Body`
  - `UpdatedAt`
- `project_db_access_function_update_target_fields`
  - `Title`
  - `Status`
  - `AssignedTo`
  - `Body`
  - `UpdatedAt`
- `project_db_access_function_update_delete_wheres`
  - `Id = argument`

生成された canonical SQL は次の形になった。

```sql
select SupportTicket.Id, SupportTicket.Title, SupportTicket.Status, SupportTicket.AssignedTo, SupportTicket.UpdatedAt
from SupportTicket
where SupportTicket.Status = ?
order by SupportTicket.UpdatedAt desc, SupportTicket.Id desc
limit ?
```

## verification

- published artifacts
  - `DATACLASS-PHP`: `20260522-062212-445f3919`
  - `DBACCESS-PHP`: `20260522-062212-00a86fa9`
  - `work/source-outputs/SAMPLE10/{DATACLASS-PHP,DBACCESS-PHP}/` を `sample/tutorials/sample10-dbaccess-mini-crud-flow/reference/` へコピーして durable actual output とした
- focused runtime test
  - `ADMIN_HTTP_PORT=18391 LAB_HTTP_PORT=18392 CONFIG_DB_HOST_PORT=43391 LAB_DB_HOST_PORT=43392 make sample10-pack-runtime-test`
  - `OK (1 test, 26 assertions)`
- full suite
  - `ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test`
  - `OK (77 tests, 2297 assertions)`
