# sample01-simple-table-runtime

- canonical project key: `SAMPLE1`
- 役割: `project -> live schema import -> data class sync -> data class output -> CRUD db access output` を 1 table だけで確認する最小 sample pack
- seed は `SAMPLE1` project と、source schema 側の物理 `article` table、minimal CRUD の canonical `project_db_access_*` metadata、`DATACLASS-PHP` / `DBACCESS-PHP` source output definition を作る
- canonical `dbtable` / `dataclass` metadata は seed しない。import / sync で作る前提
- durable input: `seed/`
- durable actual output sample: `reference/DATACLASS-PHP/data-Article.php`, `reference/DATACLASS-PHP/base/data-ArticleBase.php`, `reference/DBACCESS-PHP/dbaccess-Article.php`, `reference/DBACCESS-PHP/base/dbaccess-ArticleBase.php`
- disposable runtime root: `work/sample-packs/sample01-simple-table-runtime/`

起動:

```bash
./sample/tutorials/sample01-simple-table-runtime/run.sh up
```

seed を既存環境へ適用:

```bash
./sample/tutorials/sample01-simple-table-runtime/run.sh apply-seed
```

検証:

```bash
make test
make sample01-pack-runtime-test
```

`sample01-pack-runtime-test` は container 内 PHPUnit で `tests/Integration/Sample1SimpleTableOutputTest.php` を実行します。historical な `sample01-pack-output-test` / `sample1-output-test` は互換 alias として残しています。

最小フロー:

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample01-simple-table-runtime/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE1 --source=live-schema --table=article

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample01-simple-table-runtime/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE1

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample01-simple-table-runtime/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE1 --source-output-key=DATACLASS-PHP --requested-by=sample01-pack --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample01-simple-table-runtime/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE1 --source-output-key=DBACCESS-PHP --requested-by=sample01-pack --publish
```

生成物:

```text
work/source-outputs/SAMPLE1/DATACLASS-PHP/data-Article.php
work/source-outputs/SAMPLE1/DATACLASS-PHP/base/data-ArticleBase.php
work/source-outputs/SAMPLE1/DBACCESS-PHP/dbaccess-Article.php
work/source-outputs/SAMPLE1/DBACCESS-PHP/base/dbaccess-ArticleBase.php
```
