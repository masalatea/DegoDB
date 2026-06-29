# sample07-dbaccess-crud-basic

- canonical project key: `SAMPLE07`
- 役割: `project -> live schema import -> data class sync -> db access output` を、1 table + 1 DB Access class + 3 write function で `insert + update + delete` まで確認する tutorial sample pack
- seed は `SAMPLE07` project と、source schema 側の物理 `todo_item` table、canonical `project_db_access_*` metadata 1 class / 3 function / insert target fields / update target fields / update-delete where、`todo_item` shared contract metadata、`update_todo_item` managed operation metadata、`DATACLASS-PHP` / `DBACCESS-PHP` / `NO-CODE-RUNTIME` source output definition を作る
- canonical `dbtable` / `dataclass` metadata は seed しない。table import と data class sync で current metadata を作る前提
- `project_db_access_functions` は `InsertTodoItem` / `UpdateTodoItem` / `DeleteTodoItem` だけに絞る。write metadata に必要な `project_db_access_function_insert_target_fields`、`project_db_access_function_update_target_fields`、`project_db_access_function_update_delete_wheres` の最小構成を固定する
- `project_managed_operations` は `update_todo_item` を追加し、物理 contract key `todo_item` が generated PHP `TodoItemDBAccess::UpdateTodoItem()` binding に解決されることを sample pack test で確認する
- `project_shared_contracts` は `todo_item` を no-code `managed-screen` として追加し、`NO-CODE-RUNTIME` は generated `screen-definition.json` / `runtime-preview.json` / `runtime-preview.html` を sample pack test で確認する
- durable input: `seed/`
- durable actual output sample: `reference/DATACLASS-PHP/data-TodoItem.php`, `reference/DATACLASS-PHP/base/data-TodoItemBase.php`, `reference/DBACCESS-PHP/dbaccess-TodoItem.php`, `reference/DBACCESS-PHP/base/dbaccess-TodoItemBase.php`
- disposable runtime root: `work/sample-packs/sample07-dbaccess-crud-basic/`

起動:

```bash
./sample/tutorials/sample07-dbaccess-crud-basic/run.sh up
```

seed を既存環境へ適用:

```bash
./sample/tutorials/sample07-dbaccess-crud-basic/run.sh apply-seed
```

検証:

```bash
make sample07-pack-runtime-test
```

`sample07-pack-runtime-test` は container 内 PHPUnit で `tests/Integration/Sample7DbAccessCrudBasicOutputTest.php` を実行します。

seed される代表 row:

- `todo_item`
  - `status=open`, `title=Prepare onboarding checklist`, `body=Create the first generated CRUD sample.`
  - `status=done`, `title=Verify generated DB access output`, `body=Compare runtime output against durable reference files.`

最小フロー:

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample07-dbaccess-crud-basic/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE07 --source=live-schema --table=todo_item

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample07-dbaccess-crud-basic/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE07

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample07-dbaccess-crud-basic/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE07 --source-output-key=DATACLASS-PHP --requested-by=sample07-pack --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample07-dbaccess-crud-basic/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE07 --source-output-key=DBACCESS-PHP --requested-by=sample07-pack --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample07-dbaccess-crud-basic/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE07 --source-output-key=NO-CODE-RUNTIME --requested-by=sample07-pack --publish
```

生成物:

```text
work/source-outputs/SAMPLE07/DATACLASS-PHP/data-TodoItem.php
work/source-outputs/SAMPLE07/DATACLASS-PHP/base/data-TodoItemBase.php
work/source-outputs/SAMPLE07/DBACCESS-PHP/dbaccess-TodoItem.php
work/source-outputs/SAMPLE07/DBACCESS-PHP/base/dbaccess-TodoItemBase.php
work/source-outputs/SAMPLE07/NO-CODE-RUNTIME/README.md
work/source-outputs/SAMPLE07/NO-CODE-RUNTIME/runtime-preview.html
work/source-outputs/SAMPLE07/NO-CODE-RUNTIME/runtime-preview.json
work/source-outputs/SAMPLE07/NO-CODE-RUNTIME/screen-definition.json
```
