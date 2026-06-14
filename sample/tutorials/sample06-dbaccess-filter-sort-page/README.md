# sample06-dbaccess-filter-sort-page

- canonical project key: `SAMPLE06`
- 役割: `project -> live schema import -> data class sync -> db access output` を、1 table + 1 DB Access class + 1 selectlist function で `filter + fixed sort + page size limit` まで確認する tutorial sample pack
- seed は `SAMPLE06` project と、source schema 側の物理 `Announcement` table、canonical `project_db_access_*` metadata 1 class / 1 function / 4 target fields / 1 select where、`DATACLASS-PHP` / `DBACCESS-PHP` source output definition を作る
- canonical `dbtable` / `dataclass` metadata は seed しない。table import と data class sync で current metadata を作る前提
- `project_db_access_functions` は `GetAnnouncementList` 1 本だけに絞る。`Status` filter、`PublishedAt desc, Id desc` の fixed sort、`limit` argument を組み合わせて、一覧画面の最小 list query を確認する
- durable input: `seed/`
- durable actual output sample: `reference/DATACLASS-PHP/data-Announcement.php`, `reference/DATACLASS-PHP/base/data-AnnouncementBase.php`, `reference/DBACCESS-PHP/dbaccess-Announcement.php`, `reference/DBACCESS-PHP/base/dbaccess-AnnouncementBase.php`
- disposable runtime root: `work/sample-packs/sample06-dbaccess-filter-sort-page/`

起動:

```bash
./sample/tutorials/sample06-dbaccess-filter-sort-page/run.sh up
```

seed を既存環境へ適用:

```bash
./sample/tutorials/sample06-dbaccess-filter-sort-page/run.sh apply-seed
```

検証:

```bash
make sample06-pack-runtime-test
```

`sample06-pack-runtime-test` は container 内 PHPUnit で `tests/Integration/Sample6DbAccessFilterSortPageOutputTest.php` を実行します。

seed される代表 row:

- `Announcement`
  - `Status=published`, `Title=Welcome Release`, `PublishedAt=2026-05-20 09:00:00`
  - `Status=draft`, `Title=Planned Maintenance`, `PublishedAt=NULL`
  - `Status=published`, `Title=May Newsletter`, `PublishedAt=2026-05-22 08:30:00`

最小フロー:

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample06-dbaccess-filter-sort-page/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE06 --source=live-schema --table=Announcement

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample06-dbaccess-filter-sort-page/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE06

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample06-dbaccess-filter-sort-page/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE06 --source-output-key=DATACLASS-PHP --requested-by=sample06-pack --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample06-dbaccess-filter-sort-page/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE06 --source-output-key=DBACCESS-PHP --requested-by=sample06-pack --publish
```

生成物:

```text
work/source-outputs/SAMPLE06/DATACLASS-PHP/data-Announcement.php
work/source-outputs/SAMPLE06/DATACLASS-PHP/base/data-AnnouncementBase.php
work/source-outputs/SAMPLE06/DBACCESS-PHP/dbaccess-Announcement.php
work/source-outputs/SAMPLE06/DBACCESS-PHP/base/dbaccess-AnnouncementBase.php
```
