# sample10-dbaccess-mini-crud-flow

- canonical project key: `SAMPLE10`
- 役割: `project -> live schema import -> data class sync -> db access output` を、1 table + 1 DB Access class + `list + detail + create + update + delete` 5 function で確認する tutorial sample pack
- seed は `SAMPLE10` project と、source schema 側の物理 `SupportTicket` table、canonical `project_db_access_*` metadata 1 class / 5 function / select target fields / select wheres / insert target fields / update target fields / update-delete where、`DATACLASS-PHP` / `DBACCESS-PHP` source output definition を作る
- canonical `dbtable` / `dataclass` metadata は seed しない。table import と data class sync で current metadata を作る前提
- `GetSupportTicketList` は `Status` argument filter + `limit` を持つ list function、`GetSupportTicket` は `Id` 1 本の detail function、`InsertSupportTicket` / `UpdateSupportTicket` / `DeleteSupportTicket` は write flow を担当する
- durable input: `seed/`
- durable actual output sample: `reference/DATACLASS-PHP/data-SupportTicket.php`, `reference/DATACLASS-PHP/base/data-SupportTicketBase.php`, `reference/DBACCESS-PHP/dbaccess-SupportTicket.php`, `reference/DBACCESS-PHP/base/dbaccess-SupportTicketBase.php`
- disposable runtime root: `work/sample-packs/sample10-dbaccess-mini-crud-flow/`

起動:

```bash
./sample/tutorials/sample10-dbaccess-mini-crud-flow/run.sh up
```

seed を既存環境へ適用:

```bash
./sample/tutorials/sample10-dbaccess-mini-crud-flow/run.sh apply-seed
```

検証:

```bash
make sample10-pack-runtime-test
```

`sample10-pack-runtime-test` は container 内 PHPUnit で `tests/Integration/Sample10DbAccessMiniCrudFlowOutputTest.php` を実行します。

seed される代表 row:

- `SupportTicket`
  - `Status=open`, `Title=Seed runtime sample`, `AssignedTo=Alice`, `UpdatedAt=2026-05-22 09:00:00`
  - `Status=in_progress`, `Title=Verify tutorial output`, `AssignedTo=Bob`, `UpdatedAt=2026-05-22 11:30:00`
  - `Status=done`, `Title=Archive stale sample docs`, `AssignedTo=''`, `UpdatedAt=2026-05-21 18:15:00`

最小フロー:

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample10-dbaccess-mini-crud-flow/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE10 --source=live-schema --table=SupportTicket

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample10-dbaccess-mini-crud-flow/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE10

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample10-dbaccess-mini-crud-flow/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE10 --source-output-key=DATACLASS-PHP --requested-by=sample10-pack --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample10-dbaccess-mini-crud-flow/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE10 --source-output-key=DBACCESS-PHP --requested-by=sample10-pack --publish
```

生成物:

```text
work/source-outputs/SAMPLE10/DATACLASS-PHP/data-SupportTicket.php
work/source-outputs/SAMPLE10/DATACLASS-PHP/base/data-SupportTicketBase.php
work/source-outputs/SAMPLE10/DBACCESS-PHP/dbaccess-SupportTicket.php
work/source-outputs/SAMPLE10/DBACCESS-PHP/base/dbaccess-SupportTicketBase.php
```
