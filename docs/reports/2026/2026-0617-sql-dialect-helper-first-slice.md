# 2026-06-17 SQL Dialect Helper First Slice

## Summary

SQLite profile に向けた最初の code slice として、SQL dialect helper を追加し、`project_repository_pdo.php` の datetime select expression を helper 経由にした。

この slice では default MySQL / MariaDB behavior は変えない。`mysql:` DSN では従来と同じ `DATE_FORMAT(..., "%Y-%m-%d %H:%i:%s")` を返す。

## Changes

- `mtool/app/sql_dialect.php`
  - `app_sql_dialect_from_dsn()`
  - `app_sql_dialect_from_db_config()`
  - `app_sql_dialect_from_pdo()`
  - `app_sql_datetime_select_expr()`
  - `app_sql_table_exists()`
  - `app_sql_column_exists()`
  - `app_sql_server_version()`
  - `app_sql_current_database_name()`
- `mtool/app/database.php`
  - dialect helper を読み込むようにした。
- `mtool/app/project_repository_pdo.php`
  - project catalog / project detail の `updated_at` select expression を dialect helper 経由にした。
- `mtool/app/database_source_repository_pdo.php`
  - `database_sources` table existence check を `app_sql_table_exists()` 経由にした。
- `mtool/app/config_db_bootstrap.php`
  - preflight / apply の table existence、column existence、server version、database name lookup を dialect helper 経由にした。
- `tests/Integration/SqlDialectTest.php`
  - DSN / PDO dialect detection、datetime select expression、SQLite table / column existence、SQLite version / database name helper を固定した。

## Current Behavior

MySQL / MariaDB:

```sql
DATE_FORMAT(p.updated_at, "%Y-%m-%d %H:%i:%s") AS updated_at
```

SQLite future path:

```sql
strftime('%Y-%m-%d %H:%M:%S', p.updated_at) AS updated_at
```

SQLite table exists future path:

```sql
SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = :table_name LIMIT 1
```

SQLite column exists future path:

```sql
SELECT 1 FROM pragma_table_info(:table_name) WHERE name = :column_name LIMIT 1
```

## Boundary

- SQLite config store connection はまだ追加していない。
- SQLite schema migration はまだ追加していない。
- `project_repository_pdo.php` の write path はまだ MySQL schema 前提のまま。
- first slice は SQL expression boundary の導入のみ。

## Verification

```bash
php -l mtool/app/sql_dialect.php
php -l mtool/app/database.php
php -l mtool/app/project_repository_pdo.php
php -l mtool/app/database_source_repository_pdo.php
php -l mtool/app/config_db_bootstrap.php
php -l tests/Integration/SqlDialectTest.php
php -r 'require "mtool/app/sql_dialect.php"; $pdo = new PDO("sqlite::memory:"); $pdo->exec("CREATE TABLE database_sources (id INTEGER PRIMARY KEY, source_key TEXT)"); var_export([app_sql_server_version($pdo) !== "", app_sql_current_database_name($pdo), app_sql_table_exists($pdo, "database_sources"), app_sql_column_exists($pdo, "database_sources", "source_key")]); echo PHP_EOL;'
make -n backup-config-db-rotate CONFIG_DB_BACKUP_KEEP_COUNT=2 CONFIG_DB_BACKUP_KEEP_DAYS=7
```

- syntax / make dry-run checks passed.
- local `phpunit` command was unavailable in this workspace, so the PHPUnit case was added but not executed locally.

## Next

- Add focused tests for dialect helper output.
- Convert one more small repository or move to SQLite output sample planning.
