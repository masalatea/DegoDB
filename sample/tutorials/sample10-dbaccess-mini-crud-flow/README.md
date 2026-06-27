# sample10-dbaccess-mini-crud-flow

- canonical project key: `SAMPLE10`
- 役割: `project -> live schema import -> data class sync -> db access output` を、1 table + 1 DB Access class + `list + detail + create + update + delete` 5 function で確認する tutorial sample pack
- seed は `SAMPLE10` project と、source schema 側の物理 `support_ticket` table、canonical `project_db_access_*` metadata 1 class / 5 function / select target fields / select wheres / insert target fields / update target fields / update-delete where、`DATACLASS-PHP` / `DBACCESS-PHP` source output definition を作る
- canonical `dbtable` / `dataclass` metadata は seed しない。table import と data class sync で current metadata を作る前提
- `GetSupportTicketList` は `status` argument filter + `limit` を持つ list function、`GetSupportTicket` は `id` 1 本の detail function、`InsertSupportTicket` / `UpdateSupportTicket` / `DeleteSupportTicket` は write flow を担当する
- この sample は physical DB name を `snake_case`、generated PHP class/file surface を `SupportTicket` 系に分ける first migrated sample として `physical-logical-v1` generated-name policy で検証する
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

SQLite config store profile で同じ tutorial を検証:

```bash
make sample10-pack-runtime-test-sqlite
```

手元で軽く動かす場合は、DegoDB 自身の設計メタデータを `APP_CONFIG_STORE_DIR` 配下の SQLite file に保存できます。これは tutorial の user / Lab DB とは別です。

```bash
APP_CONFIG_STORE_DIR=work/config-store-sample10-sqlite \
  ./sample/tutorials/sample10-dbaccess-mini-crud-flow/run-sqlite-config.sh up
```

seed される代表 row:

- `support_ticket`
  - `status=open`, `title=Seed runtime sample`, `assigned_to=Alice`, `updated_at=2026-05-22 09:00:00`
  - `status=in_progress`, `title=Verify tutorial output`, `assigned_to=Bob`, `updated_at=2026-05-22 11:30:00`
  - `status=done`, `title=Archive stale sample docs`, `assigned_to=''`, `updated_at=2026-05-21 18:15:00`

最小フロー:

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample10-dbaccess-mini-crud-flow/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE10 --source=live-schema --table=support_ticket

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
