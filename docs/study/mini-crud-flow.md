# Study: mini CRUD flow

English companion:
This study note uses `sample10-dbaccess-mini-crud-flow` as the capstone tutorial. It brings together list, detail, create, update, and delete functions around one small table.

`sample10-dbaccess-mini-crud-flow` は tutorial lane の capstone です。  
`SupportTicket` 1 table を使って、list / detail / create / update / delete を 1 つの小さな CRUD flow として見ます。

## まず実行する

repository root で実行します。

```bash
make sample10-pack-runtime-test
```

成功したら、次のファイルを読みます。

- [../../sample/tutorials/sample10-dbaccess-mini-crud-flow/README.md](../../sample/tutorials/sample10-dbaccess-mini-crud-flow/README.md)
- [../../sample/tutorials/sample10-dbaccess-mini-crud-flow/seed/](../../sample/tutorials/sample10-dbaccess-mini-crud-flow/seed/)
- [../../sample/tutorials/sample10-dbaccess-mini-crud-flow/reference/DBACCESS-PHP/](../../sample/tutorials/sample10-dbaccess-mini-crud-flow/reference/DBACCESS-PHP/)

## function ごとに読む

`sample10` では、DB Access class 1 つに 5 function がまとまっています。

| function | 学ぶこと |
| --- | --- |
| `GetSupportTicketList` | list / filter / limit |
| `GetSupportTicket` | detail by id |
| `InsertSupportTicket` | create target fields |
| `UpdateSupportTicket` | update target fields + id where |
| `DeleteSupportTicket` | delete where |

## seed で確認すること

- `project_db_access_classes`
  - DB Access class の単位を見る
- `project_db_access_functions`
  - list / detail / write function の単位を見る
- `project_db_access_function_select_wheres`
  - list filter と detail id where を見る
- `project_db_access_function_insert_target_fields`
  - create で受け取る field を見る
- `project_db_access_function_update_target_fields`
  - update で変更できる field を見る
- `project_db_access_function_update_delete_wheres`
  - update / delete の対象 row を絞る条件を見る

## output で確認すること

`reference/DBACCESS-PHP/base/dbaccess-SupportTicketBase.php` を中心に読みます。  
custom layer 側の `dbaccess-SupportTicket.php` は、generated base を継承する薄い拡張点として見ます。

見る点:

- list と detail は select 系 function として出る
- insert / update / delete は write 系 function として出る
- generated base と custom class の責務が分かれている

## 次に試すこと

`sample10` まで読めたら、実際の既存 DB を使う導線に進みます。

- [../existing-db-to-output.md](../existing-db-to-output.md)
- [../common-tasks.md](../common-tasks.md)
- [../current-supported-workflow.md](../current-supported-workflow.md)

sample は教材です。  
実案件では、sample の seed SQL を直接真似るのではなく、admin / script で import、sync、metadata design、source output を進めます。
