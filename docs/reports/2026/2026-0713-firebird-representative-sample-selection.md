# Firebird Representative Sample Selection / Firebird代表sample選定

Date: 2026-07-13

Status: `F100_1_DONE_SAMPLE_MATRIX`

## Purpose / 目的

Firebird 100% support should proceed sample-first before changing Mtool itself.

The goal of this step is not to convert every sample. It is to choose enough representative samples to prove the risky Firebird support surfaces:

- generated DBAccess/runtime dialect behavior;
- schema DDL differences;
- CRUD and read-model behavior;
- transactions and guarded mutation behavior;
- text/blob/json/time handling;
- multi-table relationships;
- local/profile packaging expectations;
- migration path readiness for SQLite -> Firebird and Firebird -> MySQL/MariaDB.

## Selected representative samples / 選定sample

| Sample | Role in Firebird coverage | Why selected |
| --- | --- | --- |
| sample05-dbaccess-select-basic | Smallest generated DBAccess read baseline | First low-risk proof that generated DBAccess can connect to and read from a Firebird profile. |
| sample08-dbaccess-join-read-model | Join/read-model coverage | Exercises joins and read-model shape, which are common dialect-sensitive areas. |
| sample09-dbaccess-aggregate-report | Aggregate/report query coverage | Exercises aggregate SQL and report-style read behavior. |
| sample18-mini-task-board-demo | CRUD, no-code UI/runtime, guarded mutation, Transaction Full boundary | Existing central no-code sample with DBAccess, runtime, action metadata, and transaction evidence. |
| sample21-ebook-catalog-api-demo | Multi-table catalog and many-to-many-style relationships | Covers richer schema and relationship surfaces before Mtool config-store work. |
| sample22-ebook-chapter-workflow-demo | Workflow/multi-entity state transition shape | Covers workflow-like data shape and multi-entity generated output. |
| sample27-app-local-persistence-demo | Local persistence/profile expectation | Helps verify Firebird as a local durable profile rather than only a server DB novelty. |
| sample30-no-code-app-local-sync-demo | Local sync/offline-ish profile boundary | Helps identify whether Firebird profile semantics collide with app-local sync expectations. |

## Coverage matrix / coverage matrix

| Coverage area | Primary samples | Exit evidence |
| --- | --- | --- |
| Basic generated DBAccess read | sample05 | Focused Firebird DBAccess smoke passes. |
| Joins and read models | sample08 | Firebird read-model query behavior is proven or dialect gap recorded. |
| Aggregates/reports | sample09 | Aggregate query smoke passes or required SQL rewrite is documented. |
| CRUD and guarded mutation | sample18 | Create/read/update-style path and guarded mutation boundary are proven with Firebird profile. |
| Transaction Full reuse | sample18 | Existing transaction boundary can run on Firebird or a driver-specific gap is recorded. |
| No-code runtime integration | sample18 | Generated/no-code runtime can read and present Firebird-backed data without changing the hand-coded baseline. |
| Rich relationships | sample21, sample22 | Multi-table and relationship-heavy generated output works or gaps are explicit. |
| Text/blob/json/time values | sample18, sample21, sample22 | Value representation and conversion rules are proven or excluded. |
| Local durable profile packaging | sample27 | Firebird profile setup boundary is clear for local durable use. |
| Local sync/app profile boundary | sample30 | Confirms whether Firebird belongs in local sync scenarios or should stay server/local-durable only. |
| SQLite -> Firebird migration readiness | sample05, sample18, sample21 | Source shapes are sufficient to test one-way promotion once migration implementation begins. |
| Firebird -> MySQL/MariaDB migration readiness | sample18, sample21, sample22 | Firebird source metadata/value coverage is sufficient for the later one-way promotion path. |

## Non-goals for the sample-first pass / sample-first passの非目標

- Do not convert every sample before moving to Mtool.
- Do not claim embedded/serverless Firebird support until product packaging is proven.
- Do not make normal `make test` require Firebird.
- Do not implement SQLite -> Firebird or Firebird -> MySQL/MariaDB migration during sample selection.
- Do not replace MySQL/MariaDB or SQLite defaults.

## Next step / 次step

Proceed to F100-2: implement Firebird sample support in the selected order, starting with sample05 as the smallest DBAccess read baseline.

## F100-2 first slice: sample05 Firebird DBAccess baseline / F100-2 first slice

Date: 2026-07-13

Status: `DONE_SAMPLE05_DBACCESS_READ_BASELINE`

Added an opt-in sample05 Firebird DBAccess smoke:

- Script: `mtool/scripts/check_sample05_firebird_dbaccess_smoke.php`
- Make target: `make sample05-firebird-dbaccess-smoke-docker`
- Compose service: `sample05-firebird-dbaccess-smoke`
- Scope: create disposable Firebird `NOTICE` table, insert deterministic rows, and read them through generated `NoticeDBAccess::GetNoticeList()`.
- Non-goal: no generated DBAccess class changes, no normal `make test` dependency on Firebird, no migration implementation.

Verification:

```bash
php -l mtool/scripts/check_sample05_firebird_dbaccess_smoke.php
make -n sample05-firebird-dbaccess-smoke-docker
make sample05-firebird-dbaccess-smoke-docker
make test
git diff --check
```

Docker smoke result:

```json
{
  "ok": true,
  "stage": "ok",
  "details": {
    "sample": "sample05-dbaccess-select-basic",
    "pdo_driver": "firebird",
    "table": "NOTICE",
    "row_count": 2
  }
}
```

Conclusion:

- The existing generated DBAccess runtime DSN path can read from Firebird through PDO.
- Firebird unquoted identifier behavior is compatible with sample05's basic generated SQL for this slice.
- Full regression remains green: `make test` passed with 632 tests, 15395 assertions, and 5 skipped.
- The next sample support slice should move to joins/read-models, starting with sample08, unless a shared Firebird sample harness is extracted first.

## F100-2 second slice: sample08 Firebird join/read-model baseline / F100-2 second slice

Date: 2026-07-13

Status: `DONE_SAMPLE08_JOIN_READ_MODEL_BASELINE`

Added an opt-in sample08 Firebird DBAccess smoke:

- Script: `mtool/scripts/check_sample08_firebird_dbaccess_smoke.php`
- Make target: `make sample08-firebird-dbaccess-smoke-docker`
- Compose service: `sample08-firebird-dbaccess-smoke`
- Scope: create disposable Firebird `BLOG_AUTHOR` and `BLOG_POST` tables, insert deterministic active/inactive and published/draft rows, and read the joined read model through generated `BlogPostDBAccess::GetPublishedBlogPostAuthorSummaryList()`.
- Non-goal: no generated DBAccess class changes, no normal `make test` dependency on Firebird, no migration implementation.

Verification:

```bash
php -l mtool/scripts/check_sample08_firebird_dbaccess_smoke.php
make -n sample08-firebird-dbaccess-smoke-docker
make sample08-firebird-dbaccess-smoke-docker
make test
git diff --check
```

Docker smoke result:

```json
{
  "ok": true,
  "stage": "ok",
  "details": {
    "sample": "sample08-dbaccess-join-read-model",
    "pdo_driver": "firebird",
    "tables": ["BLOG_AUTHOR", "BLOG_POST"],
    "row_count": 1
  }
}
```

Conclusion:

- Firebird can run the generated sample08 join/read-model SELECT with positional parameters.
- The generated `blog_author.is_active = 1` predicate works against a Firebird `SMALLINT` boolean-like column for this slice.
- Full regression remains green: `make test` passed with 632 tests, 15395 assertions, and 5 skipped.
- The next sample support slice should move to aggregates/reports with sample09.

## F100-2 third slice: sample09 Firebird aggregate/report baseline / F100-2 third slice

Date: 2026-07-13

Status: `DONE_SAMPLE09_AGGREGATE_REPORT_BASELINE`

Added an opt-in sample09 Firebird DBAccess smoke:

- Script: `mtool/scripts/check_sample09_firebird_dbaccess_smoke.php`
- Make target: `make sample09-firebird-dbaccess-smoke-docker`
- Compose service: `sample09-firebird-dbaccess-smoke`
- Scope: create disposable Firebird `SALES_CATEGORY` and `SALES_RECORD` tables, insert deterministic closed/open and active/inactive rows, and read the aggregate report through generated `SalesRecordDBAccess::GetClosedSalesCategoryReportList()`.
- Non-goal: no generated DBAccess class changes, no normal `make test` dependency on Firebird, no migration implementation.

Verification:

```bash
php -l mtool/scripts/check_sample09_firebird_dbaccess_smoke.php
make -n sample09-firebird-dbaccess-smoke-docker
make sample09-firebird-dbaccess-smoke-docker
make test
git diff --check
```

Docker smoke result:

```json
{
  "ok": true,
  "stage": "ok",
  "details": {
    "sample": "sample09-dbaccess-aggregate-report",
    "pdo_driver": "firebird",
    "tables": ["SALES_CATEGORY", "SALES_RECORD"],
    "row_count": 1
  }
}
```

Conclusion:

- Firebird can run the generated sample09 aggregate/report SELECT with `COUNT`, `SUM`, `GROUP BY`, `HAVING`, positional parameters, and aggregate ordering.
- The generated `sales_category.is_active = 1` predicate works against a Firebird `SMALLINT` boolean-like column for this slice.
- Full regression remains green: `make test` passed with 632 tests, 15395 assertions, and 5 skipped.
- The next sample support slice should move to CRUD / no-code runtime / guarded mutation with sample18.

## F100-2 fourth slice: sample18 Firebird CRUD/list + no-code runtime + Transaction Full + guarded route baseline / F100-2 fourth slice

Date: 2026-07-14

Status: `DONE_SAMPLE18_CRUD_LIST_NO_CODE_TRANSACTION_GUARDED_ROUTE_BASELINE`

Added an opt-in sample18 Firebird DBAccess smoke:

- Script: `mtool/scripts/check_sample18_firebird_dbaccess_smoke.php`
- Make target: `make sample18-firebird-dbaccess-smoke-docker`
- Compose service: `sample18-firebird-dbaccess-smoke`
- Runtime support: generated DBAccess runtime rewrites the narrow Firebird-incompatible trailing `LIMIT ?` form to `ROWS ?` for PDO Firebird only; DBACCESS and AUTH proxy runtime reference snapshots were updated to match the generator.
- Scope: create disposable Firebird `TASK_CARD` table, seed deterministic rows, exercise generated `GetTaskCardList`, `GetTaskCard`, `InsertTaskCard`, `UpdateTaskCard`, and `CompleteTaskCard`, render Firebird-read rows through the no-code runtime list/presentation model, prove generated runtime `beginTransaction` / `commit` / `rollBack` reuse with generated DBAccess calls, then execute the guarded generated-submit route with Firebird as the app DB and disposable SQLite as the config-store audit/idempotency DB.
- Non-goal: no generated DBAccess base class SQL changes, no normal `make test` dependency on Firebird, no no-code public runtime route replacement, no migration implementation.

Verification:

```bash
php -l mtool/scripts/check_sample18_firebird_dbaccess_smoke.php
php -l mtool/app/project_output_db_access_generator.php
php -l sample/tutorials/sample18-mini-task-board-demo/reference/DBACCESS-PHP/_support/mtool_runtime_db.php
make -n sample18-firebird-dbaccess-smoke-docker
make sample18-pack-runtime-test
make sample18-firebird-dbaccess-smoke-docker
make test
git diff --check
```

Docker smoke result:

```json
{
  "ok": true,
  "stage": "ok",
  "details": {
    "sample": "sample18-mini-task-board-demo",
    "pdo_driver": "firebird",
    "table": "TASK_CARD",
    "created_id": 4,
    "completed_status": "done",
    "no_code_runtime_rows": 2,
    "no_code_runtime_html_markers": 3,
    "transaction_commit_rows": 1,
    "transaction_rollback_rows": 0,
    "guarded_route_created_rows": 1,
    "guarded_route_completed_status": "done",
    "guarded_route_duplicate_status": "duplicate"
  }
}
```

Important Firebird findings:

- Generated MySQL/SQLite-style `LIMIT ?` is not Firebird-compatible; sample18 needs a dialect-aware runtime rewrite to `ROWS ?`.
- Reference-only runtime edits break generated-output parity. The generator support template and checked sample reference copies, including AUTH proxy embedded runtime copies, must move together.
- Firebird identity columns do not automatically advance when seed rows insert explicit IDs. The smoke avoids explicit seed IDs so later generated `InsertTaskCard` can use the identity sequence safely. Migration implementation must handle this explicitly.
- Firebird-read DBAccess rows can be converted to the no-code runtime display model and rendered into preview HTML. This is a fast read/presentation proof, not a claim that the no-code generator opens a Firebird connection directly.
- Firebird PDO supports the generated runtime transaction surface for this sample: a generated DBAccess insert inside `beginTransaction` commits as visible data, while a required failure followed by `rollBack` leaves no partial row.
- The guarded generated-submit route can execute against Firebird when transaction callables bind the app DB to Firebird. Audit/idempotency remain in a separate disposable config store, so the success policy is still ordered execution plus post-commit recording rather than cross-store physical atomicity.

Conclusion:

- Firebird can run sample18 generated list/detail/insert/update/complete DBAccess paths with a narrow runtime dialect adapter, can feed Firebird-read rows into the no-code runtime display model, can reuse the generated runtime transaction boundary for commit/rollback semantics, and can serve the guarded generated-submit route for create/complete plus duplicate blocking.
- Full regression remains green after the no-code runtime and guarded route extension: `make test` passed with 632 tests, 15395 assertions, and 5 skipped.
- This covers the CRUD/list, no-code runtime read/presentation, Transaction Full, and guarded mutation part of the sample18 row. The next F100-2 slice can move to sample21 rich catalog relationships.

## F100-2 fifth slice: sample21 Firebird rich catalog relationship/read-model baseline / F100-2 fifth slice

Date: 2026-07-14

Status: `DONE_SAMPLE21_CATALOG_RELATIONSHIP_BASELINE`

Added an opt-in sample21 Firebird DBAccess smoke:

- Script: `mtool/scripts/check_sample21_firebird_dbaccess_smoke.php`
- Make target: `make sample21-firebird-dbaccess-smoke-docker`
- Compose service: `sample21-firebird-dbaccess-smoke`
- Scope: create disposable Firebird catalog tables for series, authors, genres, books, book-author links, book-genre links, and the generated `ebook_catalog_item` read model; seed deterministic published/draft rows; exercise generated `GetPublicEbookCatalogList` and `GetPublicEbookBook`.
- Non-goal: no generated DBAccess class changes, no normal `make test` dependency on Firebird, no migration implementation.

Verification:

```bash
php -l mtool/scripts/check_sample21_firebird_dbaccess_smoke.php
make -n sample21-firebird-dbaccess-smoke-docker
make sample21-firebird-dbaccess-smoke-docker
make test
git diff --check
```

Docker smoke result:

```json
{
  "ok": true,
  "stage": "ok",
  "details": {
    "sample": "sample21-ebook-catalog-api-demo",
    "pdo_driver": "firebird",
    "relationship_counts": {
      "series": 2,
      "authors": 2,
      "genres": 2,
      "books": 3,
      "book_authors": 3,
      "book_genres": 3,
      "catalog_items": 3
    },
    "catalog_rows": [
      {"bookId": 2, "bookSlug": "second-ebook", "epubStatus": "planned"},
      {"bookId": 1, "bookSlug": "first-ebook", "epubStatus": "available"}
    ],
    "detail_book_slug": "first-ebook",
    "missing_book": null
  }
}
```

Important Firebird findings:

- The same narrow runtime `LIMIT ?` to `ROWS ?` adapter introduced by sample18 also supports sample21's generated catalog list query.
- Firebird can carry the relationship-shaped catalog seed and generated read-model table in this representative slice.
- The smoke intentionally uses single-row prepared inserts because Firebird does not accept the MySQL-style multi-row seed syntax used in the tutorial SQL.

Conclusion:

- Firebird can run the sample21 generated public catalog list/detail DBAccess over a relationship-backed catalog shape.
- Full regression remains green after the sample21 slice: `make test` passed with 632 tests, 15395 assertions, and 5 skipped.
- The next F100-2 slice can move to sample22 workflow/multi-entity state transition shape.

## F100-2 sixth slice: sample22 Firebird workflow state transition baseline / F100-2 sixth slice

Date: 2026-07-14

Status: `DONE_SAMPLE22_WORKFLOW_STATE_TRANSITION_BASELINE`

Added an opt-in sample22 Firebird DBAccess smoke:

- Script: `mtool/scripts/check_sample22_firebird_dbaccess_smoke.php`
- Make target: `make sample22-firebird-dbaccess-smoke-docker`
- Compose service: `sample22-firebird-dbaccess-smoke`
- Runtime support: generated DBAccess runtime now rewrites Firebird `NOW()` calls to `CURRENT_TIMESTAMP` in addition to the existing narrow trailing `LIMIT ?` to `ROWS ?` rewrite.
- Scope: create disposable Firebird workflow tables for books, editable chapters, and the published chapter read model; seed deterministic published/draft rows; exercise generated `GetPublishedEbookWorkflowChapterList`, `GetPublishedEbookWorkflowChapter`, `InsertEbookWorkflowChapter`, `UpdateEbookWorkflowChapterDraft`, `UpdateEbookWorkflowChapterOrder`, and `PublishEbookWorkflowChapter`.
- Non-goal: no normal `make test` dependency on Firebird, no published read-model projection/sync implementation, no migration implementation.

Verification:

```bash
php -l mtool/scripts/check_sample22_firebird_dbaccess_smoke.php
php -l mtool/app/project_output_db_access_generator.php
php -l sample/tutorials/sample22-ebook-chapter-workflow-demo/reference/DBACCESS-PHP/_support/mtool_runtime_db.php
make -n sample22-firebird-dbaccess-smoke-docker
make sample22-firebird-dbaccess-smoke-docker
make test
git diff --check
```

Docker smoke result:

```json
{
  "ok": true,
  "stage": "ok",
  "details": {
    "sample": "sample22-ebook-chapter-workflow-demo",
    "pdo_driver": "firebird",
    "published_rows": [
      {"chapterId": 1, "chapterSlug": "opening", "spineOrder": 1},
      {"chapterId": 2, "chapterSlug": "middle", "spineOrder": 2}
    ],
    "published_detail_slug": "opening",
    "inserted_draft_rows": 1,
    "workflow_status_after_publish": "published",
    "workflow_spine_order_after_order": 5
  }
}
```

Important Firebird findings:

- Generated update/insert workflow SQL uses MySQL-style `NOW()`. Firebird needs `CURRENT_TIMESTAMP`, so the runtime adapter now performs that narrow rewrite for PDO Firebird.
- Firebird identity columns again require explicit sequence alignment when seed rows use explicit IDs; the smoke restarts the identity after seeding.
- The generated publish operation updates the editable `ebook_workflow_chapter` table. It does not automatically project into `ebook_workflow_published_chapter`; read-model projection remains an application/sample concern outside this generated DBAccess proof.

Conclusion:

- Firebird can run sample22 public published read-model queries and draft workflow insert/update/order/publish state transitions through generated DBAccess.
- Full regression remains green after the sample22 slice: `make test` passed with 632 tests, 15395 assertions, and 5 skipped.
- The next F100-2 slice can move to sample27 local durable profile packaging expectations.

## F100-2 seventh slice: sample27 Firebird App-local persistence boundary / F100-2 seventh slice

Date: 2026-07-14

Status: `DONE_SAMPLE27_FIREBIRD_APP_LOCAL_PERSISTENCE_BOUNDARY`

Added an opt-in sample27 Firebird App-local persistence smoke:

- Script: `mtool/scripts/check_sample27_firebird_app_local_persistence_smoke.php`
- Make target: `make sample27-firebird-app-local-persistence-smoke-docker`
- Compose service: `sample27-firebird-app-local-persistence-smoke`
- Scope: create disposable Firebird `APP_LOCAL_TASK`, seed the sample27 server fixture row, read it into the sample27 shared-contract DTO shape, generate/apply the App-local SQLite schema, save the Firebird-read DTO into local SQLite, and read it back with dirty/sync metadata.
- Non-goal: this does not make the Mtool config store itself run on Firebird. That belongs to F100-4 after sample coverage is confirmed.

Verification:

```bash
php -l mtool/scripts/check_sample27_firebird_app_local_persistence_smoke.php
make -n sample27-firebird-app-local-persistence-smoke-docker
make sample27-firebird-app-local-persistence-smoke-docker
make sample27-pack-runtime-test
make test
git diff --check
```

Docker smoke result:

```json
{
  "ok": true,
  "stage": "ok",
  "details": {
    "sample": "sample27-app-local-persistence-demo",
    "firebird_pdo_driver": "firebird",
    "local_pdo_driver": "sqlite",
    "server_table": "APP_LOCAL_TASK",
    "local_contract_key": "app_local_task",
    "local_table_count": 1,
    "server_dto": {
      "id": 1001,
      "title": "Server task for App-local persistence",
      "status": "draft",
      "sortOrder": 10,
      "isPinned": false,
      "publishedAt": null,
      "note": "server read fixture for sample27"
    },
    "local_metadata": {
      "sync_status": "dirty",
      "dirty": 1,
      "tombstone": 0
    }
  }
}
```

Important Firebird findings:

- sample27's core question is not generated DBAccess. It is whether a local durable server profile can hand off a DTO into App-local persistence without forcing the app-local side to know the server DB engine.
- The Firebird smoke therefore fixes the sample27 contract shape locally and proves Firebird server row -> DTO -> App-local SQLite save/read. Full canonical-project-metadata generation remains covered by the normal sample27 pack test and by Mtool's MySQL/SQLite config-store tests.
- This keeps the user-facing boundary clean: Firebird can be the durable local server-side profile, while app-local SQLite remains the mobile/browser/local persistence edge.

Conclusion:

- Firebird can satisfy the sample27 local durable profile boundary for server DTO handoff into App-local SQLite persistence.
- The normal sample27 pack test remains green: `make sample27-pack-runtime-test` passed with 1 test and 11 assertions.
- Full regression remains green after the sample27 slice: `make test` passed with 632 tests, 15395 assertions, and 5 skipped.
- This closes the representative sample matrix item for local durable profile packaging expectations unless the F100-3 coverage review finds another sample-level gap.

## F100-3 coverage checkpoint: sample scope closure / F100-3 coverage checkpoint

Date: 2026-07-14

Status: `DONE_SAMPLE_COVERAGE_CONFIRMED`

The F100-3 review closes the representative sample-first scope and moves the active lane to F100-4.

Decision summary:

- sample05, sample08, sample09, sample18, sample21, sample22, and sample27 provide enough evidence for generated DBAccess/runtime dialect behavior, joins, aggregates, CRUD, no-code runtime read/presentation, Transaction Full, guarded mutation, rich relationships, workflow state transitions, text/time handling, and local durable profile handoff.
- sample30 was reviewed but explicitly deferred. Its current uniqueness is managed operation sync outbox, App-local identity / SSO-shaped actor handoff, failed outbox visibility, and no-code runtime sync hints. Those belong to config-store/outbox/profile behavior and are better reopened during F100-4 or F100-5 if a concrete Firebird profile gap appears.
- The detailed checkpoint is recorded in `docs/reports/2026/2026-0714-firebird-sample-coverage-checkpoint.md`.

Conclusion:

- F100-2 is complete.
- F100-3 is complete.
- Next active work is F100-4: adapt Mtool itself to an opt-in Firebird config-store/runtime profile.
