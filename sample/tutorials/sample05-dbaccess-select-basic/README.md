# sample05-dbaccess-select-basic

- canonical project key: `SAMPLE05`
- 役割: `project -> live schema import -> data class sync -> db access output` を、1 table + 1 DB Access class + 1 selectlist function で確認する最小 DB Access tutorial sample pack
- seed は `SAMPLE05` project と、source schema 側の物理 `Notice` table、canonical `project_db_access_*` metadata 1 class / 1 function / 4 target fields、`DATACLASS-PHP` / `DBACCESS-PHP` source output definition を作る
- canonical `dbtable` / `dataclass` metadata は seed しない。table import と data class sync で current metadata を作る前提
- `project_db_access_functions` は `GetNoticeList` 1 本だけに絞る。`where` / paging / user-supplied sort はまだ入れず、`sample06` の filter / sort / page lane へ送る
- durable input: `seed/`
- durable actual output sample: `reference/DATACLASS-PHP/data-Notice.php`, `reference/DATACLASS-PHP/base/data-NoticeBase.php`, `reference/DBACCESS-PHP/dbaccess-Notice.php`, `reference/DBACCESS-PHP/base/dbaccess-NoticeBase.php`
- disposable runtime root: `work/sample-packs/sample05-dbaccess-select-basic/`

起動:

```bash
./sample/tutorials/sample05-dbaccess-select-basic/run.sh up
```

seed を既存環境へ適用:

```bash
./sample/tutorials/sample05-dbaccess-select-basic/run.sh apply-seed
```

検証:

```bash
make sample05-pack-runtime-test
```

`sample05-pack-runtime-test` は container 内 PHPUnit で `tests/Integration/Sample5DbAccessSelectBasicOutputTest.php` を実行します。

seed される代表 row:

- `Notice`
  - `SortOrder=10`, `Title=Maintenance Window`
  - `SortOrder=20`, `Title=Release Notes`

最小フロー:

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample05-dbaccess-select-basic/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE05 --source=live-schema --table=Notice

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample05-dbaccess-select-basic/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE05

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample05-dbaccess-select-basic/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE05 --source-output-key=DATACLASS-PHP --requested-by=sample05-pack --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample05-dbaccess-select-basic/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE05 --source-output-key=DBACCESS-PHP --requested-by=sample05-pack --publish
```

生成物:

```text
work/source-outputs/SAMPLE05/DATACLASS-PHP/data-Notice.php
work/source-outputs/SAMPLE05/DATACLASS-PHP/base/data-NoticeBase.php
work/source-outputs/SAMPLE05/DBACCESS-PHP/dbaccess-Notice.php
work/source-outputs/SAMPLE05/DBACCESS-PHP/base/dbaccess-NoticeBase.php
```
