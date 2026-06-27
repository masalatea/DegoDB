# sample08-dbaccess-join-read-model

- canonical project key: `SAMPLE08`
- 役割: `project -> live schema import -> data class sync -> db access output` を、2 live table + 1 read model table + 1 join function で確認する tutorial sample pack
- seed は `SAMPLE08` project と、source schema 側の物理 `blog_author` / `blog_post` / `blog_post_author_summary` table、canonical `project_db_access_*` metadata 1 class / 1 function / select target fields / select wheres、`DATACLASS-PHP` / `DBACCESS-PHP` source output definition を作る
- canonical `dbtable` / `dataclass` metadata は seed しない。table import と data class sync で current metadata を作る前提
- `BlogPostAuthorSummary` は join read model 用の DTO shape table であり、DB access function は物理 `blog_post` と `blog_author` を join してこの Data Class へ値を詰める
- この sample は physical DB name を `snake_case`、generated PHP class/file surface を `BlogPost` 系に分ける migrated sample として `physical-logical-v1` generated-name policy で検証する
- durable input: `seed/`
- durable actual output sample: `reference/DATACLASS-PHP/data-BlogAuthor.php`, `reference/DATACLASS-PHP/data-BlogPost.php`, `reference/DATACLASS-PHP/data-BlogPostAuthorSummary.php`, `reference/DBACCESS-PHP/dbaccess-BlogPost.php`
- disposable runtime root: `work/sample-packs/sample08-dbaccess-join-read-model/`

起動:

```bash
./sample/tutorials/sample08-dbaccess-join-read-model/run.sh up
```

seed を既存環境へ適用:

```bash
./sample/tutorials/sample08-dbaccess-join-read-model/run.sh apply-seed
```

検証:

```bash
make sample08-pack-runtime-test
```

`sample08-pack-runtime-test` は container 内 PHPUnit で `tests/Integration/Sample8DbAccessJoinReadModelOutputTest.php` を実行します。

seed される代表 row:

- `blog_author`
  - `name=Alice Editor`, `is_active=1`
  - `name=Bob Archived`, `is_active=0`
  - `name=Carol Writer`, `is_active=1`
- `blog_post`
  - `title=Canonical Join Tutorial`, `status=published`, `blog_author_id=1`
  - `title=Inactive Author Should Not Appear`, `status=published`, `blog_author_id=2`
  - `title=Draft Posts Stay Hidden`, `status=draft`, `blog_author_id=3`
  - `title=Roadmap Checkpoint`, `status=published`, `blog_author_id=3`

最小フロー:

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample08-dbaccess-join-read-model/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE08 --source=live-schema --table=blog_author

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample08-dbaccess-join-read-model/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE08 --source=live-schema --table=blog_post

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample08-dbaccess-join-read-model/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE08 --source=live-schema --table=blog_post_author_summary

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample08-dbaccess-join-read-model/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE08

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample08-dbaccess-join-read-model/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE08 --source-output-key=DATACLASS-PHP --requested-by=sample08-pack --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample08-dbaccess-join-read-model/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE08 --source-output-key=DBACCESS-PHP --requested-by=sample08-pack --publish
```

生成物:

```text
work/source-outputs/SAMPLE08/DATACLASS-PHP/data-BlogAuthor.php
work/source-outputs/SAMPLE08/DATACLASS-PHP/data-BlogPost.php
work/source-outputs/SAMPLE08/DATACLASS-PHP/data-BlogPostAuthorSummary.php
work/source-outputs/SAMPLE08/DBACCESS-PHP/dbaccess-BlogPost.php
```
