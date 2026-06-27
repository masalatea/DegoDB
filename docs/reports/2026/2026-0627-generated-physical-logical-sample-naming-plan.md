# 2026-06-27 Generated Physical / Logical Sample Naming Plan

## Status

- status: `TRACKED_AND_CUSTOM_PROXY_SAMPLE_NAMING_MIGRATION_COMPLETED`
- scope: next generated name migration slice after DataClass wording cleanup
- purpose: make the active plan concrete enough to start physical / logical sample naming migration without confusing it with the completed wording slice

## Current Boundary

Completed before this plan:

- generated name migration audit tooling
- physical / logical / generated naming helper
- opt-in `MTOOL_GENERATED_NAME_POLICY=physical-logical-v1`
- Mtool runtime reference first slice
- sample-wide DataClass base comment wording update
- current sample seed/docs wording classification

Completed scope:

- migrate representative tutorial samples from mixed-case physical DB names to snake_case physical names
- preserve generated PHP/OpenAPI surface names through `physical_name -> logical_name -> generated_name`
- regenerate references from Mtool output rather than hand-editing generated artifacts

## Representative First Target

Use `sample10-dbaccess-mini-crud-flow` first.

Why:

- it has one table, so the physical/logical map is easy to inspect
- it exercises DataClass output and DBAccess output
- it includes list, single, insert, update, and delete DBAccess functions
- it already serves as the user DB contract representative for DBAccess metadata

## Sample10 Naming Map

Physical DB names should move to snake_case.

| Current physical | New physical | Logical / generated class surface |
| --- | --- | --- |
| `SupportTicket` | `support_ticket` | `SupportTicket` |
| `Id` | `id` | `Id` / `id` depending on surface |
| `Title` | `title` | `Title` / `title` |
| `Status` | `status` | `Status` / `status` |
| `AssignedTo` | `assigned_to` | `AssignedTo` / `assignedTo` |
| `Body` | `body` | `Body` / `body` |
| `UpdatedAt` | `updated_at` | `UpdatedAt` / `updatedAt` |

Expected generated PHP file/class names should remain stable for class surfaces:

- `data-SupportTicket.php`
- `base/data-SupportTicketBase.php`
- `dbaccess-SupportTicket.php`
- `base/dbaccess-SupportTicketBase.php`
- `SupportTicketData`
- `SupportTicketDBAccess`

Expected generated PHP property / parameter surfaces may move to lower-camel when the opt-in policy is used:

- `$id`
- `$title`
- `$status`
- `$assignedTo`
- `$body`
- `$updatedAt`

This property/parameter change is the main compatibility review point.

## Applied Sample10 Changes

Applied through source inputs and regenerated output:

1. Updated live schema seed:
   - table `SupportTicket` -> `support_ticket`
   - columns `Id`, `Title`, `Status`, `AssignedTo`, `Body`, `UpdatedAt` -> snake_case physical names
2. Updated DBAccess metadata seed physical targets:
   - `target_table_name`
   - `target_table_column_name`
   - `sort_order_columns`
3. Kept logical/generated DBAccess function names stable for the first representative slice:
   - `GetSupportTicketList`
   - `GetSupportTicket`
   - `InsertSupportTicket`
   - `UpdateSupportTicket`
   - `DeleteSupportTicket`
4. Updated sample10 runtime output checker expectations in two layers:
   - physical metadata expectations should use snake_case
   - generated output expectations should accept the policy-driven PHP surface
5. Regenerated `sample10` references from actual Mtool output:
   - `DATACLASS-PHP/base/data-SupportTicketBase.php`
   - `DBACCESS-PHP/base/dbaccess-SupportTicketBase.php`
6. Updated the sample runner to pass an explicit `MTOOL_GENERATED_NAME_POLICY` through to phpunit when needed, and updated the sample10 checker/test to run this migrated sample with `physical-logical-v1`.

## Verification

Passed:

```sh
MTOOL_GENERATED_NAME_POLICY=physical-logical-v1 make sample10-pack-runtime-test
make sample10-pack-runtime-test
make user-db-contract-test
make sample08-pack-runtime-test
USER_DB_CONTRACT_SAMPLE=sample08-dbaccess-join-read-model make user-db-contract-test
make sample09-pack-runtime-test
USER_DB_CONTRACT_SAMPLE=sample09-dbaccess-aggregate-report make user-db-contract-test
make sample13-pack-runtime-test
make sample16-pack-runtime-test
make sample17-pack-runtime-test
make sample18-pack-runtime-test
make sample18-http-runtime-smoke
make sample19-pack-runtime-test
make sample20-pack-runtime-test
make sample21-pack-runtime-test
make sample22-pack-runtime-test
make sample23-pack-runtime-test
make sample24-pack-runtime-test
make sample25-pack-runtime-test
make sample26-pack-runtime-test
make sample01-pack-runtime-test
make sample02-pack-runtime-test
make sample03-pack-runtime-test
make sample04-pack-runtime-test
make sample05-pack-runtime-test
make sample06-pack-runtime-test
make sample07-pack-runtime-test
make sample12-pack-runtime-test
make sample14-pack-runtime-test
make sample15-pack-runtime-test
make test
```

The second command verifies that sample10 itself opts into the migrated naming policy during its checker/test path.
The user DB contract test verifies the migrated sample10 output across MySQL/MariaDB and SQLite capture/compare lanes.
The sample08 user DB contract test verifies the migrated join read-model output across the same MySQL/MariaDB and SQLite lanes.
The sample09 user DB contract test verifies the migrated aggregate report output across the same MySQL/MariaDB and SQLite lanes.
The sample13 runtime output test verifies the migrated OpenAPI API surface keeps generated route/schema names stable while physical table metadata uses snake_case names.
The sample16 runtime output test verifies the migrated authenticated single-proxy server keeps generated endpoint/class names and static bearer behavior stable while runtime SQL uses snake_case physical table metadata.
The sample17 runtime output test verifies the migrated multi-output capstone keeps generated DataClass, DBAccess, and OpenAPI surface names stable while schema context outputs expose snake_case physical table metadata.
The sample18 runtime output and HTTP smoke tests verify the migrated mini task board keeps generated DataClass, DBAccess, OpenAPI, and web-lab demo behavior stable while SQL uses snake_case physical table metadata.
The sample19 runtime output test verifies the migrated JSON-first normalized/read-model sample keeps generated PHP class/file names stable while join SQL and canonical metadata use snake_case physical names.
The sample20 runtime output test verifies the migrated content publishing sample keeps generated DataClass, DBAccess, OpenAPI path/schema, and HTML page behavior stable while SQL and canonical metadata use snake_case physical names.
The sample21 runtime output test verifies the migrated ebook catalog API keeps generated DataClass, DBAccess, OpenAPI path/schema, and proxy parameter names stable while SQL and canonical metadata use snake_case physical names.
The sample22 runtime output test verifies the migrated ebook chapter workflow keeps generated read/write DBAccess functions, OpenAPI path/schema, and proxy parameter names stable while SQL and canonical metadata use snake_case physical names.
The sample23 runtime output test verifies the migrated ebook media metadata sample keeps generated DataClass, DBAccess, OpenAPI path/schema, and proxy parameter names stable while SQL and canonical metadata use snake_case physical names.
The sample24 runtime output test verifies the migrated ebook public reader site keeps generated DataClass, DBAccess, HTML page, OpenAPI path/schema, and proxy parameter names stable while SQL and canonical metadata use snake_case physical names.
The sample25 runtime output test verifies the migrated ebook editor auth CMS keeps generated DataClass, DBAccess, OpenAPI, auth proxy path/handler/schema, and ProjectToken behavior stable while SQL and canonical metadata use snake_case physical names.
The sample26 runtime output test verifies the migrated ebook headless CMS capstone keeps generated DataClass, DBAccess, OpenAPI, auth proxy path/handler/schema, HTML page, and metadata bundle surfaces stable while SQL and canonical metadata use snake_case physical names.
The sample01 runtime output test verifies the migrated simple table sample keeps generated DataClass and DBAccess file/class names stable while SQL and canonical metadata use snake_case physical names.
The sample02 runtime output test verifies the migrated nullable/default-status DataClass sample keeps generated DataClass file/class names stable while canonical metadata uses snake_case physical names.
The sample03 runtime output test verifies the migrated lookup/helper DataClass sample keeps generated DataClass file/class names stable while canonical metadata uses snake_case physical names.
The sample04 runtime output test verifies the migrated parent/child DataClass sample keeps generated DataClass file/class names stable while SQL foreign-key metadata and canonical metadata use snake_case physical names.
The sample05 runtime output test verifies the migrated select-only DBAccess sample keeps generated DataClass and DBAccess file/class names stable while SQL and canonical DBAccess metadata use snake_case physical names.
The sample06 runtime output test verifies the migrated filtered/sorted/paged DBAccess sample keeps generated DataClass and DBAccess file/class names stable while SQL filter/sort/limit metadata uses snake_case physical names.
The sample07 runtime output test verifies the migrated CRUD DBAccess sample keeps generated DataClass and DBAccess file/class names stable while insert/update/delete SQL metadata uses snake_case physical names.
The sample12 runtime output test verifies the migrated external DB source import sample keeps generated DataClass file/class names stable while imported external schema metadata uses snake_case physical names.
The sample14 runtime output test verifies the migrated custom proxy step source uses the snake_case physical `project_source_output` reference while generated handler, DBAccess class, DBAccess method, and request parameter surfaces stay stable.
The sample15 runtime output test verifies the migrated project metadata bundle sample keeps project-core export/import stable while bundle table and DataClass metadata use snake_case physical names.
The full test suite includes `SamplePhysicalLogicalNamingContractTest`, which scans tutorial seed SQL schema identifiers, tutorial seed INSERT statements, sample checker physical-name constants, generated reference DBAccess SQL strings, tutorial reference JSON recursively, generated reference Markdown/HTML/TXT text mentions, and tutorial documentation Markdown/TXT text mentions, then fails when physical-name metadata columns/fields such as `table_name`, `source_name`, `target_table_name`, `target_table_column_name`, or `db_access_source_name` contain mixed-case identifiers. The same contract also verifies that migrated tutorial output tests and CLI check scripts for `sample01`-`sample10` and applicable `sample12`-`sample19` entrypoints explicitly opt into `MTOOL_GENERATED_NAME_POLICY=physical-logical-v1`; `sample11` and legacy companion-declaration entrypoints remain explicitly classified as excluded because they have no DB physical-name migration target. Current tutorial/study docs distinguish physical `snake_case` table/column references from generated `PascalCase` class/API surfaces in the active learning path.

Partial / boundary check:

```sh
USER_DB_CONTRACT_SAMPLE=sample21-ebook-catalog-api-demo make user-db-contract-test
USER_DB_CONTRACT_SAMPLE=sample22-ebook-chapter-workflow-demo make user-db-contract-test
USER_DB_CONTRACT_SAMPLE=sample23-ebook-media-metadata-demo make user-db-contract-test
USER_DB_CONTRACT_SAMPLE=sample24-ebook-public-reader-site-demo make user-db-contract-test
USER_DB_CONTRACT_SAMPLE=sample25-ebook-editor-auth-cms-demo make user-db-contract-test
USER_DB_CONTRACT_SAMPLE=sample26-ebook-headless-cms-capstone make user-db-contract-test
```

The sample21, sample22, sample23, sample24, sample25, and sample26 user DB contract commands verified the MySQL lane and then stopped at the pre-existing ebook lane boundary where sqlite capture is not defined for those ebook samples; do not treat that shared target as a passing sqlite contract for these samples.

## Applied Sample26 Changes

Applied through source inputs, user DB fixture alignment, auth proxy verification, metadata bundle verification, and regenerated output:

1. Updated live schema seed:
   - tables `EbookCmsBook`, `EbookCmsChapter` -> `ebook_cms_book`, `ebook_cms_chapter`
   - columns moved to snake_case physical names such as `author_name`, `cover_image_url`, `epub_download_url`, `ebook_cms_book_id`, `chapter_title`, `chapter_slug`, `spine_order`, `body_markdown`, `published_at`, and `updated_at`
2. Updated DBAccess metadata seed physical targets:
   - class source `ebook_cms_book`
   - function target tables `ebook_cms_book` and `ebook_cms_chapter`
   - select/update/where metadata uses physical column names while generated store fields remain lower-camel, such as `authorName`, `coverImageUrl`, `ebookCmsBookId`, `chapterTitle`, `bodyMarkdown`, `publishedAt`, and `updatedAt`
3. Kept generated capstone surface stable:
   - DataClass and DBAccess files/classes remain `EbookCmsBookData`, `EbookCmsChapterData`, and `EbookCmsBookDBAccess`
   - OpenAPI and auth proxy paths remain `/proxyserver-EbookCmsBook-*.php`
   - proxy handler classes remain `EbookCmsBook*ProxyHandler*`
4. Regenerated `sample26` references from actual Mtool output:
   - `DATACLASS-PHP`
   - `DBACCESS-PHP`
   - `OPENAPI-JSON`
   - `AUTH-PROXY-SERVER`
   - `PROJECT-METADATA-BUNDLE`

## Applied Sample01 Changes

Applied through source inputs and regenerated output:

1. Updated live schema seed:
   - table `Article` -> `article`
   - columns `Id`, `Title`, `Body` -> `id`, `title`, `body`
2. Updated DBAccess metadata seed physical targets:
   - class source `article`
   - function target table `article`
   - select/insert/update/delete metadata uses physical column names while generated store fields remain `id`, `title`, and `body`
3. Kept generated PHP surface stable:
   - files/classes remain `ArticleData` and `ArticleDBAccess`
   - DBAccess method names remain `GetArticleList`, `GetArticle`, `InsertArticle`, `UpdateArticle`, and `DeleteArticle`
4. Regenerated `sample01` references from actual Mtool output:
   - `DATACLASS-PHP`
   - `DBACCESS-PHP`

## Applied Sample02 Changes

Applied through source inputs and regenerated output:

1. Updated live schema seed:
   - table `Task` -> `task`
   - columns moved to snake_case physical names such as `sort_order`, `is_pinned`, and `published_at`
2. Kept generated PHP surface stable:
   - files/classes remain `TaskData` and `TaskDataBase`
   - generated DTO properties move to lower-camel names such as `sortOrder`, `isPinned`, and `publishedAt`
3. Regenerated `sample02` references from actual Mtool output:
   - `DATACLASS-PHP`

## Applied Sample03 Changes

Applied through source inputs and regenerated output:

1. Updated live schema seed:
   - tables `TaskStatus`, `TaskPriority` -> `task_status`, `task_priority`
   - columns moved to snake_case physical names such as `status_key`, `priority_key`, `sort_order`, and `is_closed`
2. Kept generated PHP surface stable:
   - files/classes remain `TaskStatusData` and `TaskPriorityData`
   - canonical data class metadata remains physical (`task_status`, `task_priority`) while generated DTO properties move to lower-camel names such as `statusKey`, `priorityKey`, `sortOrder`, and `isClosed`
3. Regenerated `sample03` references from actual Mtool output:
   - `DATACLASS-PHP`

## Applied Sample04 Changes

Applied through source inputs and regenerated output:

1. Updated live schema seed:
   - tables `Post`, `PostComment` -> `post`, `post_comment`
   - columns moved to snake_case physical names such as `published_at`, `post_id`, `author_name`, and `sort_order`
   - foreign key moved to physical names: `post_comment.post_id -> post.id`
2. Kept generated PHP surface stable:
   - files/classes remain `PostData` and `PostCommentData`
   - canonical data class metadata remains physical (`post`, `post_comment`) while generated DTO properties move to lower-camel names such as `publishedAt`, `postId`, `authorName`, and `sortOrder`
3. Regenerated `sample04` references from actual Mtool output:
   - `DATACLASS-PHP`

## Applied Sample05 Changes

Applied through source inputs and regenerated output:

1. Updated live schema seed:
   - table `Notice` -> `notice`
   - columns `Id`, `Title`, `Body`, `SortOrder` -> `id`, `title`, `body`, `sort_order`
2. Updated DBAccess metadata seed physical targets:
   - class source `notice`
   - function target table `notice`
   - select target fields use physical column names while generated store fields remain lower-camel, such as `sortOrder`
3. Kept generated PHP surface stable:
   - files/classes remain `NoticeData` and `NoticeDBAccess`
   - DBAccess method remains `GetNoticeList`
4. Regenerated `sample05` references from actual Mtool output:
   - `DATACLASS-PHP`
   - `DBACCESS-PHP`

## Applied Sample06 Changes

Applied through source inputs and regenerated output:

1. Updated live schema seed:
   - table `Announcement` -> `announcement`
   - columns `Id`, `Title`, `Status`, `PublishedAt` -> `id`, `title`, `status`, `published_at`
2. Updated DBAccess metadata seed physical targets:
   - class source `announcement`
   - function target table `announcement`
   - select target fields and where filters use physical column names while generated store fields remain lower-camel, such as `publishedAt`
   - fixed sort uses `announcement.published_at desc, announcement.id desc`
3. Kept generated PHP surface stable:
   - files/classes remain `AnnouncementData` and `AnnouncementDBAccess`
   - DBAccess method remains `GetAnnouncementList`
4. Regenerated `sample06` references from actual Mtool output:
   - `DATACLASS-PHP`
   - `DBACCESS-PHP`

## Applied Sample07 Changes

Applied through source inputs and regenerated output:

1. Updated live schema seed:
   - table `TodoItem` -> `todo_item`
   - columns `Id`, `Title`, `Status`, `Body` -> `id`, `title`, `status`, `body`
2. Updated DBAccess metadata seed physical targets:
   - class source `todo_item`
   - function target table `todo_item`
   - insert/update target fields use physical column names
   - update/delete wheres use physical `id`
3. Kept generated PHP surface stable:
   - files/classes remain `TodoItemData` and `TodoItemDBAccess`
   - DBAccess methods remain `InsertTodoItem`, `UpdateTodoItem`, and `DeleteTodoItem`
4. Regenerated `sample07` references from actual Mtool output:
   - `DATACLASS-PHP`
   - `DBACCESS-PHP`

## Applied Sample12 Changes

Applied through external source inputs, checker fixture SQL, and regenerated output:

1. Updated external lab schema seed:
   - table `ExternalArticle` -> `external_article`
   - columns `Id`, `Title`, `Slug`, `Status`, `PublishedAt`, `Body` -> `id`, `title`, `slug`, `status`, `published_at`, `body`
2. Updated the sample12 runtime checker fixture to create and import the same physical `external_article` table from `named-live-schema:sample12_lab`.
3. Kept generated PHP surface stable:
   - files/classes remain `ExternalArticleData`
   - generated DTO properties move to lower-camel names such as `publishedAt`
4. Regenerated `sample12` references from actual Mtool output:
   - `DATACLASS-PHP`

## Applied Sample14 Changes

Applied through custom proxy metadata, resolver fallback, and regenerated output:

1. Updated custom proxy step metadata:
   - DBAccess step source `ProjectSourceOutput` -> `project_source_output`
2. Added custom proxy resolver fallback for `physical-logical-v1`:
   - build-plan resolution can map a physical step source name back to the generated class surface
   - runtime generated-function lookup can use the same physical-to-generated class fallback
3. Kept generated proxy and DBAccess surfaces stable:
   - handler class remains `CatalogSummaryProxyHandlerBase`
   - DBAccess class remains `ProjectSourceOutputDBAccess`
   - DBAccess method remains `GetProjectSourceOutputList`
   - request parameter remains `param_ProjectSourceOutput_ProjectPID_where`
4. Regenerated `sample14` custom proxy references from actual Mtool output:
   - `CUSTOM-PROXY-SERVER`

## Applied Sample15 Changes

Applied through source inputs and regenerated metadata bundle output:

1. Updated live schema seed:
   - table `BundleNote` -> `bundle_note`
   - columns `Id`, `Title`, `Body`, `UpdatedAt` -> `id`, `title`, `body`, `updated_at`
2. Updated the sample15 metadata bundle checker to import physical `bundle_note`.
3. Kept project metadata bundle behavior stable:
   - project key remains `SAMPLE15`
   - project-core export / preview / apply flow remains `replace-core`
   - source output definitions and bundle section set remain stable
4. Regenerated `sample15` metadata bundle references from actual Mtool export:
   - `PROJECT-METADATA-BUNDLE`

## Applied Sample25 Changes

Applied through source inputs, user DB fixture alignment, auth proxy verification, and regenerated output:

1. Updated live schema seed:
   - tables `EbookEditorBook`, `EbookEditorChapter` -> `ebook_editor_book`, `ebook_editor_chapter`
   - columns moved to snake_case physical names such as `ebook_editor_book_id`, `chapter_title`, `chapter_slug`, `spine_order`, `body_markdown`, `published_at`, and `updated_at`
2. Updated DBAccess metadata seed physical targets:
   - class source `ebook_editor_chapter`
   - update/read functions target `ebook_editor_chapter`
   - select target fields, update fields, wheres, and fixed timestamp fields use physical table/column names
   - generated store fields remain lower-camel, such as `ebookEditorBookId`, `chapterTitle`, `spineOrder`, `bodyMarkdown`, `publishedAt`, and `updatedAt`
3. Kept generated editor auth surface stable:
   - DataClass and DBAccess files/classes remain `EbookEditor*`
   - OpenAPI paths remain `/proxyserver-EbookEditorChapter-*.php`
   - Auth proxy handlers remain `EbookEditorChapter*ProxyHandler*`
   - ProjectToken fail-closed behavior remains covered by the sample25 checker
   - proxy parameter and object names remain `param_EbookEditorChapter_Id_where` and `EbookEditorChapterObj`
4. Updated the sample25 user DB runtime fixture SQL to use physical tables/columns and updated runtime DTO property assignments to lower-camel.
5. Regenerated `sample25` references from actual Mtool output:
   - `DATACLASS-PHP`
   - `DBACCESS-PHP`
   - `OPENAPI-JSON`
   - `AUTH-PROXY-SERVER`

## Applied Sample24 Changes

Applied through source inputs, user DB fixture alignment, and regenerated output:

1. Updated live schema seed:
   - tables `EbookReaderBook`, `EbookReaderChapter`, `EbookReaderMediaDelivery` -> `ebook_reader_book`, `ebook_reader_chapter`, `ebook_reader_media_delivery`
   - columns moved to snake_case physical names such as `author_name`, `genre_name`, `ebook_reader_book_id`, `book_slug`, `chapter_title`, `chapter_slug`, `spine_order`, `body_markdown`, `asset_slug`, `file_size_bytes`, and `version_label`
2. Updated DBAccess metadata seed physical targets:
   - class source `ebook_reader_book`
   - read functions target `ebook_reader_book`, `ebook_reader_chapter`, and `ebook_reader_media_delivery`
   - select target fields, wheres, and sort order use physical table/column names
   - generated store fields remain lower-camel, such as `authorName`, `genreName`, `chapterTitle`, `spineOrder`, `bodyMarkdown`, `assetSlug`, and `fileSizeBytes`
3. Kept generated ebook reader surface stable:
   - DataClass and DBAccess files/classes remain `EbookReader*`
   - OpenAPI paths remain `/proxyserver-EbookReaderBook-*.php`
   - OpenAPI component schema remains `EbookReaderBookData`
   - HTML page output remains curated and unchanged
   - proxy parameter names remain `param_EbookReader*_where`
4. Updated the sample24 user DB runtime fixture SQL to use physical tables/columns.
5. Regenerated `sample24` references from actual Mtool output:
   - `DATACLASS-PHP`
   - `DBACCESS-PHP`
   - `OPENAPI-JSON`

## Applied Sample23 Changes

Applied through source inputs, user DB fixture alignment, and regenerated output:

1. Updated live schema seed:
   - tables `EbookMediaBook`, `EbookMediaAsset`, `EbookMediaBookAsset`, `EbookMediaDelivery` -> `ebook_media_book`, `ebook_media_asset`, `ebook_media_book_asset`, `ebook_media_delivery`
   - columns moved to snake_case physical names such as `asset_slug`, `display_name`, `storage_path`, `file_size_bytes`, `version_label`, `book_slug`, `sort_order`, and `is_primary_asset`
2. Updated DBAccess metadata seed physical targets:
   - class source `ebook_media_asset`
   - read functions target `ebook_media_delivery`
   - insert/update functions target `ebook_media_asset`
   - select target fields, insert fields, update fields, wheres, and sort order use physical table/column names
   - generated store fields remain lower-camel, such as `assetSlug`, `displayName`, `fileSizeBytes`, `versionLabel`, and `isPrimaryAsset`
3. Kept generated ebook media surface stable:
   - DataClass and DBAccess files/classes remain `EbookMedia*`
   - OpenAPI paths remain `/proxyserver-EbookMediaAsset-*.php`
   - OpenAPI component schema remains `EbookMediaAssetData`
   - proxy parameter names remain `param_EbookMediaDelivery_*_where` and object parameter names remain `EbookMediaAssetObj`
4. Updated the sample23 user DB runtime fixture SQL to use physical tables/columns and updated runtime DTO property assignments to lower-camel.
5. Regenerated `sample23` references from actual Mtool output:
   - `DATACLASS-PHP`
   - `DBACCESS-PHP`
   - `OPENAPI-JSON`

## Applied Sample22 Changes

Applied through source inputs, user DB fixture alignment, and regenerated output:

1. Updated live schema seed:
   - tables `EbookWorkflowBook`, `EbookWorkflowChapter`, `EbookWorkflowPublishedChapter` -> `ebook_workflow_book`, `ebook_workflow_chapter`, `ebook_workflow_published_chapter`
   - columns moved to snake_case physical names such as `ebook_workflow_book_id`, `chapter_title`, `chapter_slug`, `spine_order`, `nav_label`, `epub_resource_path`, `body_markdown`, `published_at`, and `updated_at`
2. Updated DBAccess metadata seed physical targets:
   - class source `ebook_workflow_chapter`
   - read functions target `ebook_workflow_published_chapter`
   - insert/update/publish functions target `ebook_workflow_chapter`
   - select target fields, insert fields, update fields, wheres, and sort order use physical table/column names
   - generated store fields remain lower-camel, such as `chapterTitle`, `spineOrder`, `epubResourcePath`, and `bodyMarkdown`
3. Kept generated ebook workflow surface stable:
   - DataClass and DBAccess files/classes remain `EbookWorkflow*`
   - OpenAPI paths remain `/proxyserver-EbookWorkflowChapter-*.php`
   - OpenAPI component schema remains `EbookWorkflowChapterData`
   - proxy parameter names remain `param_EbookWorkflowPublishedChapter_*_where` and object parameter names remain `EbookWorkflowChapterObj`
4. Updated the sample22 user DB runtime fixture SQL to use physical tables/columns and updated runtime DTO property assignments to lower-camel.
5. Regenerated `sample22` references from actual Mtool output:
   - `DATACLASS-PHP`
   - `DBACCESS-PHP`
   - `OPENAPI-JSON`

## Applied Sample21 Changes

Applied through source inputs, runtime SQL parameter matching, user DB fixture alignment, and regenerated output:

1. Updated live schema seed:
   - tables `EbookSeries`, `EbookAuthor`, `EbookGenre`, `EbookBook`, `EbookBookAuthor`, `EbookBookGenre`, `EbookCatalogItem` -> `ebook_series`, `ebook_author`, `ebook_genre`, `ebook_book`, `ebook_book_author`, `ebook_book_genre`, `ebook_catalog_item`
   - columns moved to snake_case physical names such as `ebook_series_id`, `book_title`, `author_slug`, `published_at`, `epub_status`, and `primary_epub_url`
2. Updated DBAccess metadata seed physical targets:
   - class source `ebook_catalog_item`
   - function target table `ebook_catalog_item`
   - select target fields, fixed/argument wheres, and sort order use physical table/column names
   - generated store fields remain lower-camel, such as `bookTitle`, `authorSlug`, `publishedAt`, and `primaryEpubUrl`
3. Kept generated ebook catalog surface stable:
   - DataClass and DBAccess files/classes remain `Ebook*` and `EbookCatalogItemDBAccess`
   - OpenAPI paths remain `/proxyserver-EbookCatalogItem-GetPublicEbook*.php`
   - OpenAPI component schema remains `EbookCatalogItemData`
   - proxy parameter names remain `param_EbookCatalogItem_*_where`
4. Updated runtime SQL parameter/column matching so legacy-style generated argument names such as `param_EbookCatalogItem_AuthorSlug_where` can match physical columns like `author_slug`.
5. Updated the sample21 user DB runtime fixture SQL to use physical `ebook_catalog_item` / snake_case columns while keeping generated PHP class/file names stable.
6. Regenerated `sample21` references from actual Mtool output:
   - `DATACLASS-PHP`
   - `DBACCESS-PHP`
   - `OPENAPI-JSON`

## Applied Sample20 Changes

Applied through source inputs, proxy parameter schema mapping, and regenerated output:

1. Updated live schema seed:
   - table `ContentArticle` -> `content_article`
   - columns `Id`, `Title`, `Slug`, `CategoryName`, `AuthorName`, `Status`, `PublishedAt`, `Summary`, `Body`, `UpdatedAt` -> snake_case physical names
2. Updated DBAccess metadata seed physical targets:
   - class source `content_article`
   - function target table `content_article`
   - select target fields, fixed wheres, argument where, and sort order use physical table/column names
   - generated store fields remain lower-camel, such as `categoryName`, `authorName`, `publishedAt`, and `updatedAt`
3. Kept generated content publishing surface stable:
   - DataClass and DBAccess files/classes remain `ContentArticleData` and `ContentArticleDBAccess`
   - OpenAPI paths remain `/proxyserver-ContentArticle-GetPublishedContentArticle*.php`
   - OpenAPI component schema remains `ContentArticleData`
   - HTML page reference remains curated and unchanged
4. Updated single-proxy parameter schema mapping so legacy-style generated parameter names such as `param_ContentArticle_Slug_where` can resolve physical metadata like `content_article.slug` instead of falling back to where-order position.
5. Regenerated `sample20` references from actual Mtool output:
   - `DATACLASS-PHP`
   - `DBACCESS-PHP`
   - `OPENAPI-JSON`

## Applied Sample19 Changes

Applied through source inputs and regenerated output:

1. Updated live schema seed:
   - tables `JsonAuthor`, `JsonCategory`, `ArticleJsonModel`, `ArticlePublicSummary` -> `json_author`, `json_category`, `article_json_model`, `article_public_summary`
   - columns moved to snake_case physical names such as `json_author_id`, `published_at`, `article_id`, and `category_name`
2. Updated DBAccess metadata seed physical targets:
   - class source `article_json_model`
   - function target table `article_json_model`
   - select target fields and join/fixed wheres use physical table/column names
   - generated store fields remain lower-camel, such as `articleId`, `publishedAt`, `authorName`, and `categoryName`
3. Kept generated PHP surface stable:
   - DataClass files/classes remain `JsonAuthor`, `JsonCategory`, `ArticleJsonModel`, and `ArticlePublicSummary`
   - DBAccess file/class remains `ArticleJsonModelDBAccess`
4. Regenerated `sample19` references from actual Mtool output:
   - `DATACLASS-PHP`
   - `DBACCESS-PHP`

## Applied Sample18 Changes

Applied through source inputs, generated references, and HTTP smoke verification:

1. Updated live schema seed:
   - table `TaskCard` -> `task_card`
   - columns `Id`, `Title`, `Body`, `Status`, `AssignedTo`, `Priority`, `DueDate`, `CompletedAt`, `UpdatedAt` -> snake_case physical names
2. Updated DBAccess metadata seed physical targets:
   - class source `task_card`
   - function target table `task_card`
   - select target fields, select wheres, insert fields, update fields, update wheres, and sort order use physical names
   - generated store fields remain lower-camel, such as `assignedTo`, `dueDate`, `completedAt`, and `updatedAt`
3. Kept generated demo surface stable:
   - DataClass and DBAccess files/classes remain `TaskCardData` and `TaskCardDBAccess`
   - OpenAPI paths remain `/proxyserver-TaskCard-*.php`
   - OpenAPI component schema remains `TaskCardData`
   - HTML page reference remains curated and unchanged
4. Regenerated `sample18` references from actual Mtool output:
   - `DATACLASS-PHP`
   - `DBACCESS-PHP`
   - `OPENAPI-JSON`

## Applied Sample17 Changes

Applied through source inputs, runtime SQL generation, and regenerated output:

1. Updated live schema seed:
   - table `CapstoneTask` -> `capstone_task`
   - columns `Id`, `Title`, `Status`, `OwnerName`, `Priority`, `DueDate`, `UpdatedAt` -> `id`, `title`, `status`, `owner_name`, `priority`, `due_date`, `updated_at`
2. Updated DBAccess metadata seed physical targets:
   - class source `capstone_task`
   - function target table `capstone_task`
   - select target fields use physical column names while generated store fields remain lower-camel, such as `ownerName`, `dueDate`, and `updatedAt`
   - select wheres and sort order use physical `status`, `priority`, and `id`
3. Kept generated multi-output surface stable:
   - DataClass and DBAccess files/classes remain `CapstoneTaskData` and `CapstoneTaskDBAccess`
   - OpenAPI paths remain `/proxyserver-CapstoneTask-GetCapstoneTask*.php`
   - OpenAPI component schema remains `CapstoneTaskData`
4. Updated canonical runtime SQL generation so physical/logical policy resolves result DTO class names even when the DBAccess source name is physical.
5. Regenerated `sample17` references from actual Mtool output:
   - `DATACLASS-PHP`
   - `DBACCESS-PHP`
   - `OPENAPI-JSON`
   - `AI-CONTEXT-MD`
   - `MODERNIZATION-AUDIT-MD`

## Applied Sample16 Changes

Applied through source inputs, canonical fallback runtime generation, and regenerated output:

1. Updated live schema seed:
   - table `AuthTask` -> `auth_task`
   - columns `Id`, `Title`, `Status`, `OwnerName`, `UpdatedAt` -> `id`, `title`, `status`, `owner_name`, `updated_at`
2. Updated DBAccess metadata seed physical targets:
   - class source `auth_task`
   - function target table `auth_task`
   - select target fields use physical column names while generated store fields remain lower-camel, such as `ownerName` and `updatedAt`
   - select where uses physical `id`
3. Kept generated authenticated proxy surface stable:
   - endpoint remains `proxyserver-AuthTask-GetAuthTask.php`
   - handler classes remain `AuthTaskGetAuthTaskProxyHandler*`
   - runtime classes remain `AuthTaskData` and `AuthTaskDBAccess`
4. Updated canonical bootstrap DBAccess fallback generation so SQL uses physical identifiers while generated class/property names and detected signature parameters remain on the logical surface.
5. Regenerated `sample16` authenticated proxy references from actual Mtool output:
   - `AUTH-PROXY-SERVER/build-plan.json`
   - `AUTH-PROXY-SERVER/_base/handlers/AuthTaskGetAuthTaskProxyHandler.php`
   - `AUTH-PROXY-SERVER/_support/runtime_dbclasses/base/data-AuthTaskBase.php`
   - `AUTH-PROXY-SERVER/_support/runtime_dbclasses/base/dbaccess-AuthTaskBase.php`

## Applied Sample13 Changes

Applied through source inputs, generator fallback handling, and regenerated output:

1. Updated live schema seed:
   - table `ApiTask` -> `api_task`
   - columns `Id`, `Title`, `Status`, `OwnerName`, `DueDate`, `UpdatedAt` -> `id`, `title`, `status`, `owner_name`, `due_date`, `updated_at`
2. Updated DBAccess metadata seed physical targets:
   - class source `api_task`
   - function target table `api_task`
   - select target fields use physical column names while generated store fields remain lower-camel, such as `ownerName` and `dueDate`
   - select wheres use physical `status` and `id`
3. Kept generated OpenAPI / proxy surface stable:
   - paths remain `/proxyserver-ApiTask-GetApiTaskList.php` and `/proxyserver-ApiTask-GetApiTask.php`
   - OpenAPI component schema remains `ApiTaskData`
4. Updated canonical runtime fallback generation so OpenAPI generation can materialize runtime DataClass / DBAccess files with output class/file names when the only source metadata is physical `api_task`.
5. Regenerated `sample13` OpenAPI references from actual Mtool output:
   - `OPENAPI-JSON/build-plan.json`
   - `OPENAPI-JSON/openapi.json`

## Applied Sample09 Changes

Applied through source inputs and regenerated output:

1. Updated live schema seed:
   - tables `SalesCategory`, `SalesRecord`, `SalesCategoryReport` -> `sales_category`, `sales_record`, `sales_category_report`
   - columns moved to snake_case physical names such as `sales_category_id`, `is_active`, `closed_sale_count`, and `closed_sale_total_amount`
2. Updated DBAccess metadata seed physical targets:
   - class source `sales_record`
   - function target table `sales_record`
   - aggregate sort `sum(sales_record.amount) desc, sales_record.sales_category_id asc`
   - join where `sales_record.sales_category_id = sales_category.id`
   - fixed filters `sales_record.status = closed` and `sales_category.is_active = 1`
3. Kept generated PHP surface stable:
   - files/classes remain `SalesCategory`, `SalesRecord`, `SalesCategoryReport`, and `SalesRecordDBAccess`
   - generated DTO properties move to lower-camel names such as `salesCategoryId` and `closedSaleTotalAmount`
4. Regenerated `sample09` base references from actual Mtool output:
   - `DATACLASS-PHP/base/data-SalesCategoryBase.php`
   - `DATACLASS-PHP/base/data-SalesRecordBase.php`
   - `DATACLASS-PHP/base/data-SalesCategoryReportBase.php`
   - `DBACCESS-PHP/base/dbaccess-SalesRecordBase.php`

## Applied Sample08 Changes

Applied through source inputs and regenerated output:

1. Updated live schema seed:
   - tables `BlogAuthor`, `BlogPost`, `BlogPostAuthorSummary` -> `blog_author`, `blog_post`, `blog_post_author_summary`
   - columns moved to snake_case physical names such as `blog_author_id`, `is_active`, and `blog_post_title`
2. Updated DBAccess metadata seed physical targets:
   - class source `blog_post`
   - function target table `blog_post`
   - join where `blog_post.blog_author_id = blog_author.id`
   - fixed filters `blog_post.status = published` and `blog_author.is_active = 1`
3. Kept generated PHP surface stable:
   - files/classes remain `BlogAuthor`, `BlogPost`, `BlogPostAuthorSummary`, and `BlogPostDBAccess`
   - generated DTO properties move to lower-camel names such as `blogPostId` and `blogAuthorName`
4. Regenerated `sample08` base references from actual Mtool output:
   - `DATACLASS-PHP/base/data-BlogAuthorBase.php`
   - `DATACLASS-PHP/base/data-BlogPostBase.php`
   - `DATACLASS-PHP/base/data-BlogPostAuthorSummaryBase.php`
   - `DBACCESS-PHP/base/dbaccess-BlogPostBase.php`

## Guardrails

- Do not hand-patch files under `sample/tutorials/sample10-dbaccess-mini-crud-flow/reference`.
- If generated output differs unexpectedly, fix the seed, metadata sync, or generator rule and regenerate.
- Keep legacy references unchanged.
- Keep `physical_name` as the SQL/catalog source of truth.
- Do not silently rename unsafe physical names; validation should surface them.

## Final Verification

The tracked tutorial sample migration is complete for `sample01`-`sample10` and `sample12`-`sample26`; `sample11` has no DB physical-name migration target. The final regression includes the sample-wide naming contract, generated reference checks, tutorial documentation checks, migrated PHPUnit policy opt-ins, and CLI check-script policy opt-ins.

Latest verification:

```sh
make test
```

Result: `258 tests`, `9023 assertions`, `1 skipped`.

Any future broader implementation slice should start only after a fresh explicit scope decision, such as PostgreSQL follow-up or namespace cleanup. Do not treat sample08/sample10 as remaining work; they are part of the completed tracked sample migration.
