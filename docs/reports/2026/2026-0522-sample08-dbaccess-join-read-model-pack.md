# 2026-05-22 Sample08 DBAccess Join Read Model Pack

## 結論

- `sample/tutorials/sample08-dbaccess-join-read-model/` を追加し、tutorial lane の 8 本目を current 化した。
- sample08 は `BlogAuthor` / `BlogPost` の 2 live table と、DTO shape 用の `BlogPostAuthorSummary` table を使い、joined `SELECTLIST` が read model Data Class へ入る最小構成に固定した。
- `sample07` の write tutorial の次段として、`project_db_access_function_select_wheres` の `anotherfield` join 1 本と fixed where 2 本だけで canonical joined select を確認できる状態にした。

## 追加したもの

- runtime pack
  - `sample/tutorials/sample08-dbaccess-join-read-model/README.md`
  - `sample/tutorials/sample08-dbaccess-join-read-model/compose.yaml`
  - `sample/tutorials/sample08-dbaccess-join-read-model/run.sh`
  - `sample/tutorials/sample08-dbaccess-join-read-model/seed/`
  - `sample/tutorials/sample08-dbaccess-join-read-model/reference/`
- checker / test
  - `mtool/scripts/check_sample8_dbaccess_join_read_model_outputs.php`
  - `mtool/scripts/lib/sample8_dbaccess_join_read_model_output_check.php`
  - `tests/Integration/Sample8DbAccessJoinReadModelOutputTest.php`
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

## schema / metadata

```sql
CREATE TABLE BlogAuthor (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Name VARCHAR(255) NOT NULL,
    IsActive TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (Id)
);

CREATE TABLE BlogPost (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    BlogAuthorId BIGINT UNSIGNED NOT NULL,
    Title VARCHAR(255) NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'draft',
    PRIMARY KEY (Id)
);

CREATE TABLE BlogPostAuthorSummary (
    BlogPostId BIGINT UNSIGNED NOT NULL,
    BlogPostTitle VARCHAR(255) NOT NULL,
    BlogAuthorId BIGINT UNSIGNED NOT NULL,
    BlogAuthorName VARCHAR(255) NOT NULL,
    PRIMARY KEY (BlogPostId)
);
```

- `project_db_access_classes`
  - `source_name = 'BlogPost'`
- `project_db_access_functions`
  - `GetPublishedBlogPostAuthorSummaryList`
    - `action_type = 'SELECTLIST'`
    - `data_class_base_name = 'BlogPostAuthorSummary'`
    - `target_table_name = 'BlogPost'`
- `project_db_access_function_select_target_fields`
  - `BlogPost.Id -> BlogPostId`
  - `BlogPost.Title -> BlogPostTitle`
  - `BlogAuthor.Id -> BlogAuthorId`
  - `BlogAuthor.Name -> BlogAuthorName`
- `project_db_access_function_select_wheres`
  - `BlogPost.BlogAuthorId = BlogAuthor.Id` (`parameter_type = anotherfield`, `join_type = inner`)
  - `BlogPost.Status = 'published'`
  - `BlogAuthor.IsActive = 1` (`parameter_data_type = raw`)

生成された canonical SQL は次の形になった。

```sql
select BlogPost.Id, BlogPost.Title, BlogAuthor.Id, BlogAuthor.Name
from BlogPost join BlogAuthor on BlogPost.BlogAuthorId = BlogAuthor.Id
where BlogPost.Status = 'published' and BlogAuthor.IsActive = 1
order by BlogPost.Id asc
```

## verification

- published artifacts
  - `DATACLASS-PHP`: `20260522-053642-937647b1`
  - `DBACCESS-PHP`: `20260522-053642-5db46bf6`
  - `work/source-outputs/SAMPLE08/{DATACLASS-PHP,DBACCESS-PHP}/` を `sample/tutorials/sample08-dbaccess-join-read-model/reference/` へコピーして durable actual output とした
- focused runtime test
  - `make sample08-pack-runtime-test`
  - `OK (1 test, 23 assertions)`
- full suite
  - `ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test`
  - `OK (75 tests, 2197 assertions)`
