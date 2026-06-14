# 2026-05-22 Sample04 Dataclass Parent Child Pack

## 結論

- `sample/tutorials/sample04-dataclass-parent-child-basic/` を追加し、tutorial lane の 4 本目を current 化した。
- project key は `SAMPLE04` とし、legacy runtime pack の `SAMPLE4` と衝突しないようにした。
- sample04 は `Post` / `PostComment` の 2 table と `DATACLASS-PHP` 1 output に絞り、parent/child schema の import / sync / output を確認する tutorial として固定した。

## 追加したもの

- runtime pack
  - `sample/tutorials/sample04-dataclass-parent-child-basic/README.md`
  - `sample/tutorials/sample04-dataclass-parent-child-basic/compose.yaml`
  - `sample/tutorials/sample04-dataclass-parent-child-basic/run.sh`
  - `sample/tutorials/sample04-dataclass-parent-child-basic/seed/`
  - `sample/tutorials/sample04-dataclass-parent-child-basic/reference/DATACLASS-PHP/`
- checker / test
  - `mtool/scripts/check_sample4_dataclass_parent_child_basic_outputs.php`
  - `mtool/scripts/lib/sample4_dataclass_parent_child_basic_output_check.php`
  - `tests/Integration/Sample4DataclassParentChildBasicOutputTest.php`
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

## schema

```sql
CREATE TABLE Post (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'draft',
    PublishedAt DATETIME NULL,
    PRIMARY KEY (Id)
);

CREATE TABLE PostComment (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    PostId BIGINT UNSIGNED NOT NULL,
    AuthorName VARCHAR(100) NOT NULL,
    Body TEXT NOT NULL,
    SortOrder INT NOT NULL DEFAULT 0,
    PRIMARY KEY (Id),
    KEY idx_postcomment_postid (PostId),
    CONSTRAINT fk_postcomment_post FOREIGN KEY (PostId) REFERENCES Post (Id) ON DELETE CASCADE
);
```

この sample は FK を持つが、current の Data Class sync は relation metadata (`RefDataClassName` / `RefDataClassFieldName`) を自動補完しない。そのため `PostComment.PostId` は scalar field として同期され、relation wiring 自体は次段へ残す。

## verification

- published artifact
  - `20260522-023402-7168f78f`
  - `work/source-outputs/SAMPLE04/DATACLASS-PHP/` を `sample/tutorials/sample04-dataclass-parent-child-basic/reference/DATACLASS-PHP/` へコピーして durable actual output とした
- focused runtime test
  - `ADMIN_HTTP_PORT=18141 LAB_HTTP_PORT=18142 CONFIG_DB_HOST_PORT=43141 LAB_DB_HOST_PORT=43142 docker compose -f compose.yaml -f sample/tutorials/sample04-dataclass-parent-child-basic/compose.yaml exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/Sample4DataclassParentChildBasicOutputTest.php`
  - `OK (1 test, 13 assertions)`
- full suite
  - `make test ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092`
  - `OK (71 tests, 2030 assertions)`

## 補足

- `sample03` が independent な 2 table lookup lane だったのに対し、`sample04` では child table が parent id を持つ schema を current Data Class lane に載せた。
- `sample04-pack-runtime-test` を canonical target とし、historical な `sample04-pack-output-test` alias は internal pattern compat layer のまま触っていない。
