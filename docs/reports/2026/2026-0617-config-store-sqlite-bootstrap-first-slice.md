# 2026-06-17 Config Store SQLite Bootstrap First Slice

## Summary

`APP_CONFIG_STORE_DIR` で選ばれる SQLite config store に対して、空の `config.sqlite` を current config schema へ初期化する first slice を追加した。

目的は、ユーザーが保存フォルダだけを指定した時に、schema preflight が通る最低限の土台を作ることである。

## Changes

- `mtool/app/config_db_bootstrap.php`
  - SQLite profile では current MariaDB initdb SQL を bootstrap 時に SQLite 用 statement へ変換して適用する。
  - `CREATE TABLE` は SQLite type / auto increment へ変換する。
  - `ALTER TABLE ... ADD COLUMN IF NOT EXISTS` は PHP 側で column existence を確認してから `ADD COLUMN` する。
  - MariaDB 固有の key / constraint / drop legacy column statement は SQLite first slice では skip する。
  - `IsNull` column は SQLite parser と衝突するため quoted identifier に変換する。
- `tests/Integration/ConfigDbBootstrapSqliteTest.php`
  - temp SQLite config store を作り、`app_config_db_bootstrap_apply()` 後に required tables / columns が揃うことを固定した。
- `mtool/app/project_repository_pdo.php`
  - `UPDATE projects` で `updated_at = CURRENT_TIMESTAMP` を明示し、SQLite profile でも MySQL の `ON UPDATE CURRENT_TIMESTAMP` 相当になるようにした。
- `tests/Integration/ProjectRepositorySqliteTest.php`
  - temp SQLite config store 上で project insert / fetch / update が通ることを固定した。
- `mtool/app/source_output_repository_pdo.php`
  - source output repository の write path を canonical `config_db` へ寄せた。
  - `DATE_FORMAT()` を dialect helper 経由にした。
- `mtool/app/database_source_repository_pdo.php`
  - SQLite config store でも parent directory 作成込みで接続できるようにした。
  - update 時に `updated_at = CURRENT_TIMESTAMP` を明示した。
- `tests/Integration/SourceOutputRepositorySqliteTest.php`
  - temp SQLite config store 上で source output create / fetch / update / catalog / delete が通ることを固定した。
- `tests/Integration/DatabaseSourceRepositorySqliteTest.php`
  - temp SQLite config store 上で database source create / fetch / update / catalog / delete が通ることを固定した。
- `mtool/app/project_membership_repository_pdo.php`
  - membership summary の role aggregation を SQLite 対応した。
- `mtool/app/project_page_security_repository_pdo.php`
  - page security capability aggregation を SQLite 対応した。
  - update 時に `updated_at = CURRENT_TIMESTAMP` を明示した。
- `mtool/app/project_host_assignment_repository_pdo.php`
  - update 時に `updated_at = CURRENT_TIMESTAMP` を明示した。
- `tests/Integration/AdminSettingsRepositoriesSqliteTest.php`
  - temp SQLite config store 上で membership replace / summary、page security create / fetch / update / catalog / delete、host assignment create / update / catalog / delete が通ることを固定した。
- `mtool/app/sql_dialect.php`
  - SQLite reserved word に近い legacy column を扱うため、`app_sql_identifier()` を追加した。
- `mtool/app/table_metadata_repository_pdo.php`
  - `dbtablecolumns.IsNull` を dialect-aware identifier 経由にした。
- `mtool/app/data_class_repository_pdo.php`
  - `LastModifiedDT` select expression を dialect helper 経由にした。
- `tests/Integration/SchemaMetadataRepositoriesSqliteTest.php`
  - temp SQLite config store 上で table / column metadata と data class / field metadata の create / fetch / update / delete が通ることを固定した。
- `mtool/app/db_access_repository_pdo.php`
  - DB access class / function metadata の datetime select expression を dialect helper 経由にした。
  - SQLite profile では `ON DUPLICATE KEY UPDATE` を使わず、存在確認後に insert / update する first slice にした。
  - source output target catalog の datetime select expression を dialect helper 経由にした。
  - SELECT where / target field / having fetch の datetime select expression を dialect helper 経由にした。
  - UPDATE / DELETE where と INSERT / UPDATE target field fetch の datetime select expression も dialect helper 経由で動くことを確認した。
- `mtool/app/compare_output_repository_pdo.php`
  - compare output / additional path の datetime select expression を dialect helper 経由にした。
- `mtool/app/custom_proxy_repository_pdo.php`
  - custom proxy / step catalog の datetime select expression を dialect helper 経由にした。
  - SQLite profile では step order reset に MySQL 固有の `DEFAULT(step_order)` を使わず schema default value の `100` を使うようにした。
- `mtool/app/project_html_source_binding_repository_pdo.php`
  - HTML source binding の datetime select expression を dialect helper 経由にした。
  - SQLite profile では `ON DUPLICATE KEY UPDATE` を使わず、存在確認後に insert / update する first slice にした。
- `mtool/app/project_html_repository.php`
  - project HTML definition / parameter の table existence check と datetime expression を dialect helper 経由にした。
  - SQLite profile では bootstrap reference import に `ON DUPLICATE KEY UPDATE` を使わず、存在確認後に insert / update する first slice にした。
- `mtool/app/html_template_repository.php`
  - HTML template / parameter の table existence check を dialect helper 経由にした。
  - SQLite profile では bootstrap reference import に `ON DUPLICATE KEY UPDATE` を使わず、存在確認後に insert / update する first slice にした。
- `mtool/app/experiment_repository_pdo.php`
  - lab experiment repository の datetime select expression を dialect helper 経由にした。
  - update 時に `updated_at = CURRENT_TIMESTAMP` を明示した。
- `docker/php-apache/Dockerfile`
  - Docker / make 経由の PHPUnit でも SQLite profile を検証できるように `php8.4-sqlite3` を追加した。
- `tests/Integration/DbAccessRepositorySqliteTest.php`
  - temp SQLite config store 上で DB access class upsert / fetch、function upsert / fetch、source output target replace / fetch / catalog、function delete が通ることを固定した。
  - SELECT where / target field / having の create / fetch / update / delete が通ることを固定した。
  - UPDATE / DELETE where と INSERT / UPDATE target field の create / fetch / update / delete が通ることを固定した。
- `tests/Integration/CompareAndCustomProxyRepositoriesSqliteTest.php`
  - temp SQLite config store 上で compare output / additional path の create / fetch / update / delete が通ることを固定した。
  - temp SQLite config store 上で custom proxy / target keys / steps の create / fetch / update / reset / delete が通ることを固定した。
- `tests/Integration/HtmlSourceBindingRepositorySqliteTest.php`
  - temp SQLite config store 上で HTML source binding の upsert / fetch / catalog / delete が通ることを固定した。
- `tests/Integration/ProjectHtmlRepositorySqliteTest.php`
  - temp SQLite config store 上で project HTML definition / parameter の create / fetch / update / delete が通ることを固定した。
- `tests/Integration/HtmlTemplateRepositorySqliteTest.php`
  - temp SQLite config store 上で HTML template / parameter の create / fetch / update / delete が通ることを固定した。

## User-Facing Shape

通常ユーザーの設定入口は引き続き folder-only:

```env
APP_CONFIG_STORE_DIR=work/config-store
```

この値が入っている場合、`db-config-migrate` は SQLite profile として `APP_CONFIG_STORE_DIR/config.sqlite` を作成し、config schema を初期化できる。

server DB profile を使う場合は `APP_CONFIG_STORE_DIR` を空にし、従来どおり MySQL / MariaDB config DB を使う。

## First CRUD Smoke

SQLite config store 上で、first target として以下の repository smoke を通した。

対象:

- `app_pdo_insert_project()`
- `app_pdo_fetch_project_by_key()`
- `app_pdo_update_project()`
- `app_pdo_create_project_source_output()`
- `app_pdo_fetch_project_source_output_item()`
- `app_pdo_update_project_source_output()`
- `app_pdo_fetch_project_source_output_catalog()`
- `app_pdo_delete_project_source_output()`
- `app_pdo_create_database_source()`
- `app_pdo_fetch_database_source_item()`
- `app_pdo_update_database_source()`
- `app_pdo_fetch_database_source_catalog()`
- `app_pdo_delete_database_source()`
- `app_pdo_replace_project_memberships()`
- `app_pdo_fetch_project_membership_summary()`
- `app_pdo_create_project_page_security_policy()`
- `app_pdo_fetch_project_page_security_policy()`
- `app_pdo_update_project_page_security_policy()`
- `app_pdo_fetch_project_page_security_policies()`
- `app_pdo_delete_project_page_security_policy()`
- `app_pdo_create_project_host_assignment()`
- `app_pdo_update_project_host_assignment()`
- `app_pdo_fetch_project_host_assignments()`
- `app_pdo_delete_project_host_assignment()`
- `app_pdo_create_table_metadata_item()`
- `app_pdo_create_table_metadata_column()`
- `app_pdo_update_table_metadata_column()`
- `app_pdo_fetch_table_metadata_snapshot()`
- `app_pdo_delete_table_metadata_column()`
- `app_pdo_delete_table_metadata_item()`
- `app_pdo_create_data_class_metadata_item()`
- `app_pdo_create_data_class_metadata_field()`
- `app_pdo_update_data_class_metadata_field()`
- `app_pdo_fetch_data_class_metadata_snapshot()`
- `app_pdo_delete_data_class_metadata_field()`
- `app_pdo_delete_data_class_metadata_item()`
- `app_pdo_upsert_db_access_class_metadata()`
- `app_pdo_fetch_db_access_class_metadata()`
- `app_pdo_upsert_db_access_function_metadata()`
- `app_pdo_fetch_db_access_function_metadata()`
- `app_pdo_replace_db_access_function_source_output_target_keys()`
- `app_pdo_fetch_db_access_function_source_output_target_keys()`
- `app_pdo_fetch_source_output_db_access_function_target_catalog()`
- `app_pdo_delete_db_access_function_metadata()`
- `app_pdo_create_db_access_function_select_where()`
- `app_pdo_fetch_db_access_function_select_where_catalog()`
- `app_pdo_update_db_access_function_select_where()`
- `app_pdo_delete_db_access_function_select_where()`
- `app_pdo_create_db_access_function_select_target_field()`
- `app_pdo_fetch_db_access_function_select_target_field_catalog()`
- `app_pdo_update_db_access_function_select_target_field()`
- `app_pdo_delete_db_access_function_select_target_field()`
- `app_pdo_create_db_access_function_select_having()`
- `app_pdo_fetch_db_access_function_select_having_catalog()`
- `app_pdo_update_db_access_function_select_having()`
- `app_pdo_delete_db_access_function_select_having()`
- `app_pdo_create_db_access_function_update_delete_where()`
- `app_pdo_fetch_db_access_function_update_delete_where_catalog()`
- `app_pdo_update_db_access_function_update_delete_where()`
- `app_pdo_delete_db_access_function_update_delete_where()`
- `app_pdo_create_db_access_function_insert_target_field()`
- `app_pdo_fetch_db_access_function_insert_target_field_catalog()`
- `app_pdo_update_db_access_function_insert_target_field()`
- `app_pdo_delete_db_access_function_insert_target_field()`
- `app_pdo_create_db_access_function_update_target_field()`
- `app_pdo_fetch_db_access_function_update_target_field_catalog()`
- `app_pdo_update_db_access_function_update_target_field()`
- `app_pdo_delete_db_access_function_update_target_field()`
- `app_pdo_create_project_compare_output()`
- `app_pdo_fetch_project_compare_output_catalog()`
- `app_pdo_update_project_compare_output()`
- `app_pdo_delete_project_compare_output()`
- `app_pdo_create_project_compare_output_additional_path()`
- `app_pdo_fetch_project_compare_output_additional_path_catalog()`
- `app_pdo_update_project_compare_output_additional_path()`
- `app_pdo_delete_project_compare_output_additional_path()`
- `app_pdo_create_project_custom_proxy()`
- `app_pdo_fetch_project_custom_proxy_catalog()`
- `app_pdo_update_project_custom_proxy()`
- `app_pdo_delete_project_custom_proxy()`
- `app_pdo_replace_project_custom_proxy_target_keys()`
- `app_pdo_fetch_project_custom_proxy_target_keys()`
- `app_pdo_create_project_custom_proxy_step()`
- `app_pdo_fetch_project_custom_proxy_step_catalog()`
- `app_pdo_update_project_custom_proxy_step()`
- `app_pdo_reset_project_custom_proxy_step_order()`
- `app_pdo_delete_project_custom_proxy_step()`
- `app_pdo_upsert_project_html_source_binding()`
- `app_pdo_fetch_project_html_source_binding()`
- `app_pdo_fetch_project_html_source_bindings()`
- `app_pdo_delete_project_html_source_binding()`
- `app_create_project_html()`
- `app_fetch_project_html_catalog()`
- `app_update_project_html()`
- `app_delete_project_html()`
- `app_create_project_html_parameter()`
- `app_fetch_project_html_parameter_catalog()`
- `app_update_project_html_parameter()`
- `app_delete_project_html_parameter()`
- `app_create_html_template()`
- `app_fetch_html_template_catalog()`
- `app_update_html_template()`
- `app_delete_html_template()`
- `app_create_html_template_parameter()`
- `app_fetch_html_template_parameter_catalog()`
- `app_update_html_template_parameter()`
- `app_delete_html_template_parameter()`

## Boundary

- SQLite bootstrap first slice は schema creation / preflight を対象にする。
- SQLite profile で admin 全画面の CRUD が完了したわけではない。
- first CRUD smoke は `projects` / `project_source_outputs` / `database_sources` / membership / page security / host assignment / table metadata / data class metadata / DB access class-function metadata / DB access SELECT detail repository / DB access mutation detail repository / compare output / custom proxy metadata / HTML source binding / project HTML metadata / HTML template metadata に限定する。
- Unique index / foreign key / advanced constraint の完全 parity は後続 slice で扱う。
- MySQL / MariaDB profile は default のまま維持する。

## Verification

```bash
php -l mtool/app/config_db_bootstrap.php
php -l tests/Integration/ConfigDbBootstrapSqliteTest.php
php -l mtool/app/project_repository_pdo.php
php -l tests/Integration/ProjectRepositorySqliteTest.php
php -l mtool/app/source_output_repository_pdo.php
php -l mtool/app/database_source_repository_pdo.php
php -l tests/Integration/SourceOutputRepositorySqliteTest.php
php -l tests/Integration/DatabaseSourceRepositorySqliteTest.php
php -l mtool/app/project_membership_repository_pdo.php
php -l mtool/app/project_page_security_repository_pdo.php
php -l mtool/app/project_host_assignment_repository_pdo.php
php -l tests/Integration/AdminSettingsRepositoriesSqliteTest.php
php -l mtool/app/table_metadata_repository_pdo.php
php -l mtool/app/data_class_repository_pdo.php
php -l tests/Integration/SchemaMetadataRepositoriesSqliteTest.php
php -l mtool/app/db_access_repository_pdo.php
php -l tests/Integration/DbAccessRepositorySqliteTest.php
php -l mtool/app/compare_output_repository_pdo.php
php -l mtool/app/custom_proxy_repository_pdo.php
php -l tests/Integration/CompareAndCustomProxyRepositoriesSqliteTest.php
php -l mtool/app/project_html_source_binding_repository_pdo.php
php -l tests/Integration/HtmlSourceBindingRepositorySqliteTest.php
php -l mtool/app/project_html_repository.php
php -l tests/Integration/ProjectHtmlRepositorySqliteTest.php
php -l mtool/app/html_template_repository.php
php -l tests/Integration/HtmlTemplateRepositorySqliteTest.php
php -l mtool/app/experiment_repository_pdo.php
make test
php -r 'require "mtool/app/config.php"; require "mtool/app/config_db_bootstrap.php"; $dir = sys_get_temp_dir() . "/dego-sqlite-bootstrap-" . getmypid(); putenv("APP_CONFIG_STORE_DIR=" . $dir); $app = app_load_config(); $result = app_config_db_bootstrap_apply($app); var_export([$result["ok"], $result["summary"]["applied_file_count"], $result["summary"]["schema_current"], count($result["missing_tables"]), count($result["missing_columns"]), count($result["unexpected_legacy_columns"])]); echo PHP_EOL;'
php -r 'require "mtool/app/config.php"; require "mtool/app/config_db_bootstrap.php"; require "mtool/app/project_repository_pdo.php"; $dir = sys_get_temp_dir() . "/dego-project-repo-sqlite-" . getmypid(); $configDb = app_config_store_config("sqlite", "db-config", "3306", "config_app", "config_app", "secret", "/var/www/work", $dir); $app = ["site" => "admin", "db" => $configDb, "config_db" => $configDb]; $bootstrap = app_config_db_bootstrap_apply($app); $insert = app_pdo_insert_project($app, ["project_key" => "SQLITE_TEST", "name" => "SQLite Test", "slug" => "sqlite-test", "lifecycle_status" => "active", "owner_login_id" => "owner@example.test", "description" => "created"]); $item = app_pdo_fetch_project_by_key($app, "SQLITE_TEST"); $update = app_pdo_update_project($app, ["project_key" => "SQLITE_TEST", "name" => "SQLite Test Updated", "slug" => "sqlite-test-updated", "lifecycle_status" => "active", "description" => "updated"]); $updated = app_pdo_fetch_project_by_key($app, "SQLITE_TEST"); var_export([$bootstrap["ok"], $insert["ok"], $item["ok"], $item["item"]["member_count"] ?? null, $update["ok"], $updated["item"]["name"] ?? null, $updated["item"]["slug"] ?? null]); echo PHP_EOL;'
```

Result:

```text
array (
  0 => true,
  1 => 19,
  2 => true,
  3 => 0,
  4 => 0,
  5 => 0,
)
```

Project repository smoke:

```text
array (
  0 => true,
  1 => true,
  2 => true,
  3 => 1,
  4 => true,
  5 => 'SQLite Test Updated',
  6 => 'sqlite-test-updated',
)
```

Source output repository smoke:

```text
array (
  0 => true,
  1 => true,
  2 => true,
  3 => true,
  4 => 'DB Access PHP',
  5 => true,
  6 => 'DB Access PHP Updated',
  7 => true,
)
```

Database source repository smoke:

```text
array (
  0 => true,
  1 => true,
  2 => true,
  3 => 'external-test',
  4 => true,
  5 => 'external-test-updated',
  6 => true,
)
```

Admin settings repository smoke:

```text
array (
  0 => true,
  1 => true,
  2 => true,
  3 => 3,
  4 => 2,
  5 => true,
  6 =>
  array (
    0 => 'LoginCookieToken',
    1 => 'ProjectToken',
  ),
  7 => true,
  8 =>
  array (
    0 => 'ProjectToken',
  ),
  9 => true,
  10 => true,
  11 => 'admin-updated.local',
  12 => true,
  13 => true,
)
```

Schema metadata repository smoke:

```text
array (
  0 => true,
  1 => true,
  2 => true,
  3 => true,
  4 => 'user_id',
  5 => 1,
  6 => true,
  7 => true,
  8 => 'userId',
  9 => 1,
  10 => true,
  11 => true,
  12 => true,
  13 => true,
)
```

DB access repository first-slice smoke:

```text
array (
  0 => true,
  1 => true,
  2 => true,
  3 => true,
  4 => 'src/Db',
  5 => true,
  6 => 'SELECT',
  7 => true,
  8 =>
  array (
    0 => 'DBACCESS-PHP',
  ),
  9 => 'selectUser',
  10 => true,
)
```

DB access SELECT detail smoke:

```text
array (
  0 => true,
  1 => true,
  2 => true,
  3 => true,
  4 => 'id',
  5 => true,
  6 => 'user_id',
  7 => true,
  8 => 'name',
  9 => true,
  10 => 'userName',
  11 => true,
  12 => '0',
  13 => true,
  14 => '1',
  15 => true,
  16 => true,
  17 => true,
)
```

Docker PHPUnit via `make test`:

```text
OK (168 tests, 7046 assertions)
```

## Follow-up

- Add a lightweight compose / make lane that runs with `APP_CONFIG_STORE_DIR` without starting `db-config`.
- Add dual-profile sample gates so tutorial packs can run against both MySQL / MariaDB and SQLite config store.
- Run admin UI smoke with `APP_CONFIG_STORE_DIR` and identify the next SQLite repository SQL gaps after the current HTML / DB access first slices.
- Add SQLite-safe backup / restore / rotation for `config.sqlite`.
- Decide whether to keep runtime conversion or promote the converted schema to a curated SQLite schema file.
