# sample04-dataclass-parent-child-basic

- canonical project key: `SAMPLE04`
- 役割: `project -> live schema import -> data class sync -> data class output` を、親 table と child table を持つ 2 table schema で確認する tutorial sample pack
- seed は `SAMPLE04` project と、source schema 側の物理 `Post` / `PostComment` table、`DATACLASS-PHP` source output definition を作る
- `PostComment.PostId` には foreign key を張るが、current の Data Class sync は relation metadata (`RefDataClassName` / `RefDataClassFieldName`) を自動補完しない。この sample の主題は parent/child schema の import / sync / output であり、relation wiring 自体はまだ手動領域である
- `project_db_access_*` metadata は seed しない。`sample05` の DB Access へ進む前に、child table が親 table id を持つ schema を current Data Class lane でどう見るかを固定する
- durable input: `seed/`
- durable actual output sample: `reference/DATACLASS-PHP/data-Post.php`, `reference/DATACLASS-PHP/base/data-PostBase.php`, `reference/DATACLASS-PHP/data-PostComment.php`, `reference/DATACLASS-PHP/base/data-PostCommentBase.php`
- disposable runtime root: `work/sample-packs/sample04-dataclass-parent-child-basic/`

起動:

```bash
./sample/tutorials/sample04-dataclass-parent-child-basic/run.sh up
```

seed を既存環境へ適用:

```bash
./sample/tutorials/sample04-dataclass-parent-child-basic/run.sh apply-seed
```

検証:

```bash
make sample04-pack-runtime-test
```

`sample04-pack-runtime-test` は container 内 PHPUnit で `tests/Integration/Sample4DataclassParentChildBasicOutputTest.php` を実行します。

seed される代表 row:

- `Post`
  - `Welcome`
- `PostComment`
  - `PostId=1`, `AuthorName=Alice`
  - `PostId=1`, `AuthorName=Bob`

最小フロー:

```bash
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample04-dataclass-parent-child-basic/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE04 --source=live-schema --table=Post

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample04-dataclass-parent-child-basic/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE04 --source=live-schema --table=PostComment

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample04-dataclass-parent-child-basic/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE04

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample04-dataclass-parent-child-basic/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE04 --source-output-key=DATACLASS-PHP --requested-by=sample04-pack --publish
```

生成物:

```text
work/source-outputs/SAMPLE04/DATACLASS-PHP/data-Post.php
work/source-outputs/SAMPLE04/DATACLASS-PHP/base/data-PostBase.php
work/source-outputs/SAMPLE04/DATACLASS-PHP/data-PostComment.php
work/source-outputs/SAMPLE04/DATACLASS-PHP/base/data-PostCommentBase.php
```
