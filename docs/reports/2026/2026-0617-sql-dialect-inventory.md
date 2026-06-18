# 2026-06-17 SQL Dialect Inventory For SQLite Profile

## Summary

Lightweight SQLite persistence plan の Phase 0 として、current config store 周辺の MySQL / MariaDB dialect dependency を棚卸しした。

結論として、SQLite profile は `PDO DSN を sqlite に差し替えるだけ` では成立しない。DDL、repository SQL、schema preflight、table import introspection を dialect-aware にする必要がある。

## Scope

調査対象:

- `mtool/app`
- `docker/mariadb/config-initdb`

主な検索対象:

- `DATE_FORMAT`
- `ON DUPLICATE KEY UPDATE`
- `GROUP_CONCAT ... SEPARATOR`
- `information_schema`
- `DATABASE()`
- `NOW()`
- `AUTO_INCREMENT`
- `ENGINE=InnoDB`
- `ON UPDATE CURRENT_TIMESTAMP`

## Findings

### DDL

`docker/mariadb/config-initdb` には 23 SQL files がある。

current DDL は MariaDB 前提である。

代表的な差分:

| Current MariaDB | SQLite profile |
| --- | --- |
| `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` | `INTEGER PRIMARY KEY AUTOINCREMENT` or `INTEGER` |
| `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4` | none |
| `ON UPDATE CURRENT_TIMESTAMP` | app-side update or trigger |
| `UNIQUE KEY name (...)` | `UNIQUE (...)` or separate `CREATE UNIQUE INDEX` |
| `KEY name (...)` | separate `CREATE INDEX` |

SQLite schema は first slice では自動変換より手書き canonical schema が安全である。

### Repository SQL

Repository layer に次の MySQL-specific SQL がある。

#### Date Formatting

主な files:

- `project_repository_pdo.php`
- `source_output_repository_pdo.php`
- `db_access_repository_pdo.php`
- `compare_output_repository_pdo.php`
- `custom_proxy_repository_pdo.php`
- `project_html_repository.php`
- `project_html_source_binding_repository_pdo.php`
- `experiment_repository_pdo.php`
- `data_class_repository_pdo.php`

Current:

```sql
DATE_FORMAT(updated_at, "%Y-%m-%d %H:%i:%s")
```

SQLite profile:

```sql
strftime('%Y-%m-%d %H:%M:%S', updated_at)
```

Recommendation:

- first slice では SQL formatting を減らし、PHP side formatting へ寄せる方が安全。
- dialect helper として `app_sql_datetime_select($column, $alias)` を用意してもよい。

#### Upsert

主な files:

- `db_access_repository_pdo.php`
- `html_template_repository.php`
- `project_html_repository.php`
- `project_html_source_binding_repository_pdo.php`

Current:

```sql
ON DUPLICATE KEY UPDATE
```

SQLite profile:

```sql
ON CONFLICT (...) DO UPDATE SET ...
```

Recommendation:

- upsert は dialect helper だけで隠すと読みづらくなりやすい。
- first slice では targeted repository ごとに `mysql` / `sqlite` implementation を分けるのがよい。

#### Group Concatenation

主な files:

- `project_membership_repository_pdo.php`
- `project_page_security_repository_pdo.php`

Current:

```sql
GROUP_CONCAT(role_code ORDER BY role_code SEPARATOR ",")
```

SQLite profile:

SQLite の `group_concat()` は separator を持てるが、aggregate 内 `ORDER BY` の扱いが違う。ordered subquery が必要になる。

Recommendation:

- membership / security roles は SQL aggregate を避け、rows を fetch して PHP side grouping へ寄せると cross-dialect にしやすい。

#### Schema Introspection

主な files:

- `config_db_bootstrap.php`
- `database_source_repository_pdo.php`
- `html_template_repository.php`
- `project_html_repository.php`
- `project_table_import_source.php`
- `lab_published_single_proxy_page.php`

Current:

```sql
FROM information_schema.tables
WHERE table_schema = DATABASE()
```

SQLite profile:

```sql
SELECT name FROM sqlite_master WHERE type = 'table'
PRAGMA table_info(table_name)
```

Recommendation:

- `config_db_bootstrap` は driver-specific preflight に分ける。
- `project_table_import_source` は import source DB の introspection なので、config store driver とは別に source driver として扱う。

#### Current Timestamp

主な files:

- `project_html_repository.php`
- several repository update statements

Current:

```sql
NOW()
CURRENT_TIMESTAMP
```

SQLite profile:

```sql
CURRENT_TIMESTAMP
```

Recommendation:

- writes should prefer `CURRENT_TIMESTAMP` where possible.
- legacy `NOW()` usage should move behind dialect helper or PHP-side timestamp.

## Boundary Decision

SQLite profile first target は `config store` であり、import source introspection とは分ける。

つまり:

- `APP_CONFIG_STORE_DRIVER=sqlite` can store DegoDB design metadata in SQLite.
- `database_sources` may still point to MySQL / MariaDB / other DBs for live schema import.
- `project_table_import_source.php` should become source-driver-aware later, but it is not the same problem as config store persistence.

## First Conversion Candidates

Best first candidates:

1. `project_repository_pdo.php`
   - central but relatively small.
   - main issue is `DATE_FORMAT`.
2. `database_source_repository_pdo.php`
   - important for config store.
   - main issue is table exists introspection.
3. `source_output_repository_pdo.php`
   - important for generation workflows.
   - mostly `DATE_FORMAT` and ordinary CRUD.

Avoid as first slice:

- `db_access_repository_pdo.php`
  - large surface.
  - many `DATE_FORMAT` and upsert cases.
- `project_html_repository.php`
  - mixed legacy/current tables.
  - `NOW()`, upsert, introspection.
- `project_table_import_source.php`
  - source DB introspection rather than config store only.

## Recommended Next Implementation Slice

Add a small dialect boundary without changing the default MySQL behavior.

Suggested first files:

- `mtool/app/sql_dialect.php`
- `mtool/app/config.php`
- `mtool/app/database.php`
- `mtool/app/project_repository_pdo.php`

Suggested first capabilities:

- detect config store driver, default `mysql`
- create MySQL PDO exactly as today
- define `mysql` and `sqlite` dialect names
- provide datetime select helper
- convert `project_repository_pdo.php` date formatting through helper

SQLite connection and schema can remain behind a feature flag until the first SQLite schema slice lands.

