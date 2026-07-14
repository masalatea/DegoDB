# Firebird Config Repository Smoke / Firebird config repository smoke

Status: `F100_4_REPRESENTATIVE_CONFIG_REPOSITORY_SMOKE_DBACCESS_DONE`

This report records the F100-4 checkpoint where a representative Mtool config-store repository path was exercised against the opt-in Firebird profile.

この report は、代表的な Mtool config-store repository 経路を opt-in Firebird profile 上で実行した F100-4 checkpoint を記録します。

## What changed / 変更点

- Added `mtool/scripts/check_firebird_config_repository_smoke.php`.
- Added Docker compose service `firebird-config-repository-smoke`.
- Added Make target `make firebird-config-repository-smoke-docker`.
- Added `app_sql_limit_clause()` so Firebird can use `ROWS n` instead of MySQL/SQLite/PostgreSQL `LIMIT n`.
- Added `app_sql_normalize_row_keys()` so repositories can read uppercase associative keys returned by drivers such as PDO Firebird.
- Updated Project repository to use:
  - dialect-aware limit clauses
  - normalized row keys
  - project-key fallback lookup when `lastInsertId()` is not useful
- Updated SourceOutput repository to use:
  - dialect-aware limit clauses
  - normalized row keys
- Updated DBAccess repository to use:
  - dialect-aware limit clauses
  - normalized row keys for class/function metadata fetch and catalog reads
  - SQLite-style find/update/insert upsert fallback for Firebird instead of MySQL-only `ON DUPLICATE KEY UPDATE`
  - post-insert lookup fallback when `lastInsertId()` is not portable
  - explicit `auth_policy_version` / `auth_policy_json` persistence for function metadata
- Updated AuditLog repository to use:
  - dialect-aware limit clauses
  - normalized row keys
- Widened Firebird datetime string cast from `VARCHAR(19)` to `VARCHAR(32)` to avoid Firebird timestamp string truncation.

## Repository behavior proven / 実証した repository 挙動

The smoke uses the normal Firebird config profile and normal config bootstrap apply path, then exercises Project, SourceOutput, DBAccess metadata, and AuditLog repository behavior:

1. `app_config_db_bootstrap_apply()` reaches `schema_current=true`.
2. `app_pdo_insert_project()` inserts a project and owner membership.
3. `app_pdo_update_project()` updates text fields and lifecycle state.
4. `app_pdo_fetch_project_by_key()` reads the project with member count.
5. `app_pdo_create_project_source_output()` inserts a source output row.
6. `app_pdo_update_project_source_output()` updates large text fields and ordering.
7. `app_pdo_fetch_project_source_output_item()` reads the source output row.
8. `app_pdo_fetch_project_source_output_catalog()` reads the project catalog including the implicit AI context output.
9. `app_pdo_upsert_db_access_class_metadata()` inserts DBAccess class metadata.
10. `app_pdo_fetch_db_access_class_metadata()` reads the class metadata.
11. `app_pdo_upsert_db_access_function_metadata()` inserts DBAccess function metadata.
12. `app_pdo_fetch_db_access_function_metadata()` reads the function metadata.
13. `app_pdo_fetch_db_access_class_metadata_catalog()` reads the class catalog.
14. `app_pdo_fetch_db_access_function_metadata_catalog()` reads the function catalog.
15. `app_pdo_fetch_db_access_function_blob_target_context()` reads the function context through the lower-level PDO helper.
16. `app_pdo_audit_log_append()` inserts a row into `audit_events`.
17. `app_pdo_audit_log_fetch_by_event_key()` reads the inserted row.
18. `app_pdo_audit_log_fetch_latest()` reads recent rows with Firebird `ROWS n`.
19. `metadata_json` round-trips through a Firebird `BLOB SUB_TYPE TEXT` column.
20. sensitive nested metadata redaction still works before persistence.

## Verification / 検証

- `php -l mtool/scripts/check_firebird_config_repository_smoke.php`
- `php -l mtool/app/sql_dialect.php`
- `php -l mtool/app/audit_log_repository_pdo.php`
- `php -l tests/Integration/SqlDialectTest.php`
- `make firebird-config-repository-smoke-docker`
  - result: `ok: true`
  - Firebird version: `5.0.4`
  - bootstrap: `schema_current=true`
  - Project repository: insert/update/fetch/member count passed
  - SourceOutput repository: create/update/fetch/catalog passed
  - BLOB text metadata keys: `driver`, `blob_text_probe`, `nested`
- `make test`
  - result: passed
  - summary: `Tests: 644, Assertions: 15431, Skipped: 5.`
- After widening the smoke to Project repository, `make firebird-config-repository-smoke-docker` remained green:
  - Project repository: insert/update/fetch/member count passed
  - AuditLog repository: insert/fetch/latest/BLOB text round-trip passed
- After widening the smoke to Project repository, `make test` remained green:
  - summary: `Tests: 644, Assertions: 15431, Skipped: 5.`
- After widening the smoke to SourceOutput repository, `make firebird-config-repository-smoke-docker` remained green:
  - SourceOutput repository: create/update/fetch/catalog passed
- After widening the smoke to SourceOutput repository, `make test` remained green:
  - summary: `Tests: 644, Assertions: 15431, Skipped: 5.`
- After widening the smoke to DBAccess metadata repository, `make firebird-config-repository-smoke-docker` remained green:
  - DBAccess class metadata: upsert/fetch/catalog passed
  - DBAccess function metadata: upsert/fetch/catalog/blob-target context passed
  - representative output details included `db_access_class_catalog_count=1` and `db_access_function_catalog_count=1`
- After the DBAccess metadata widening, `make sample17-pack-runtime-test` remained green after preserving the existing catalog output shape:
  - result: passed
  - summary: `OK (1 test, 11 assertions)`
- After the DBAccess metadata widening, `make test` remained green:
  - result: passed
  - summary: `Tests: 644, Assertions: 15431, Skipped: 5.`

## Bugs found and fixed / 発見・修正した問題

- Firebird rejected `LIMIT`; repository queries need dialect-aware `ROWS`.
- PDO Firebird returned uppercase associative row keys; repository mappers need key normalization.
- Firebird timestamp string cast to `VARCHAR(19)` could truncate; widened to `VARCHAR(32)`.
- PDO Firebird did not provide a portable `lastInsertId()` path for Project repository; project-key fallback lookup now resolves the inserted project id.
- DBAccess repository project/class/function lookup helpers still had MySQL/SQLite-style `LIMIT 1`; they now use dialect-aware limit clauses.
- DBAccess function metadata upsert depended on MySQL `ON DUPLICATE KEY UPDATE`; Firebird now uses the existing find/update/insert fallback shape.
- DBAccess function metadata fetch selected `auth_policy_version` and `auth_policy_json`, but the upsert path did not persist them; this is now explicit so Firebird `NOT NULL` columns are satisfied.
- A first DBAccess catalog mapper refactor leaked item-only fields (`last_detected_*`, function `source_name`) into catalog output and changed `function_count` from int to string; Sample17 caught this and the catalog output shape was restored.

## Remaining boundary / 残境界

This is a representative repository smoke, not a full audit of every repository path. The next decision is whether F100-4 can close with explicit non-goals, or whether another named repository family is required before migration paths.

1. Close F100-4 with explicit non-goals if the agreed Mtool Firebird scope is representative config-store/runtime support rather than every admin workflow.
2. Or name the next repository family and add a similarly bounded smoke.
3. After F100-4 closes, move to F100-5 SQLite -> Firebird migration path.
