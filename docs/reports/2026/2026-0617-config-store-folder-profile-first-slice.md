# 2026-06-17 Config Store Folder Profile First Slice

## Summary

Lightweight SQLite persistence の user-facing entry point を `APP_CONFIG_STORE_DIR` に寄せた。

目的は、ユーザーが細かい driver / file name を意識せず、保存フォルダだけを指定すれば local SQLite file store を選べるようにすることである。

## Changes

- `mtool/app/config.php`
  - `APP_CONFIG_STORE_DIR` を追加した。
  - `APP_CONFIG_STORE_DIR` が指定され、`APP_CONFIG_STORE_DRIVER` が空なら `sqlite` profile として扱う。
  - SQLite file name は既定で `config.sqlite` とする。
  - 明示された relative folder は current working directory 基準で解決する。
  - 未指定時は従来どおり MySQL / MariaDB config DB profile を使う。
- `mtool/app/database.php`
  - SQLite DSN の parent directory を connection 前に作成する。
- `mtool/app/config_db_bootstrap.php`
  - SQLite config store に MariaDB initdb SQL を誤適用しない guard を追加した。
- `compose.yaml`
  - `APP_CONFIG_STORE_DIR` を `web-admin` / `web-lab` に渡す。
  - `APP_CONFIG_STORE_DRIVER` は空 default とし、folder-only inference を妨げないようにした。
- `.env.example`
  - user-facing config として `APP_CONFIG_STORE_DIR=` を追加した。
  - detailed SQLite file env は `.env.example` からは外し、通常入口を folder-only にした。
- `tests/Integration/ConfigStoreProfileTest.php`
  - default MySQL profile と folder-only SQLite profile の config shape を固定した。
- user docs
  - `README.md`
  - `docs/quickstart.md`
  - `docs/common-tasks.md`
  - `docs/start-here.md`

## User-Facing Shape

Lightweight local file store:

```env
APP_CONFIG_STORE_DIR=work/config-store
```

This resolves to:

```text
work/config-store/config.sqlite
```

Server DB profile remains the default when `APP_CONFIG_STORE_DIR` is empty.

## Boundary

- SQLite schema migration was not complete in this slice; it landed later in the SQLite bootstrap first slice.
- This slice wires config and connection shape only.
- MySQL / MariaDB remains the default profile.
- `APP_CONFIG_STORE_DRIVER` remains available as an advanced override, but normal docs should lead with folder-only configuration.

## Verification

```bash
php -l mtool/app/config.php
php -l mtool/app/database.php
php -l tests/Integration/ConfigStoreProfileTest.php
APP_CONFIG_STORE_DIR=work/config-store php -r 'require "mtool/app/config.php"; $app = app_load_config(); var_export([$app["config_db"]["driver"], str_ends_with($app["config_db"]["name"], "/work/config-store/config.sqlite"), $app["config_db"]["dsn"] === "sqlite:" . $app["config_db"]["name"]]); echo PHP_EOL;'
php -r 'require "mtool/app/config.php"; $app = app_load_config(); var_export([$app["config_db"]["driver"], $app["config_db"]["dsn"]]); echo PHP_EOL;'
docker compose -f compose.yaml config | rg "APP_CONFIG_STORE|APP_CONFIG_SQLITE"
```

All non-DB checks passed. PHPUnit was not available locally.

## Follow-up

- Add lightweight compose / make lane that does not start `db-config`.
- Add dual-profile sample gates for MySQL / MariaDB and SQLite config store.
- Add file-store specific backup command using SQLite-safe backup / `VACUUM INTO`.
- Final docs pass should make quickstart / manual pages bilingual.
