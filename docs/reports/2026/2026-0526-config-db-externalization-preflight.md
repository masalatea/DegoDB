# 2026-05-26 Config DB Externalization Preflight

## 要約

- canonical config DB externalization の first slice として、`APP_CONFIG_DB_*` override を current compose lane に通した。
- local dev の default は引き続き `db-config` container のまま維持し、`web-admin` / `web-lab` は必要時だけ external config DB を向けられるようにした。
- 外部 target を current schema として使えるか確認する `check_config_db_bootstrap.php` と `make config-db-preflight` を追加した。
- official migration apply path として `migrate_config_db.php` と `make db-config-migrate` を current `APP_CONFIG_DB` target 対応に切り替えた。
- current admin lane では一部 repository がまだ `db` を読むため、external config DB を admin から使う時は `APP_DB_*` と `APP_CONFIG_DB_*` を同じ target にそろえる rule を preflight に明示した。

## 変更点

- `compose.yaml`
  - `web-admin` の `APP_DB_*` / `APP_CONFIG_DB_*` を `APP_CONFIG_DB_*` override で差し替え可能にした
  - `web-lab` の `APP_CONFIG_DB_*` も override 可能にした
  - root `docker/` を `web-admin` / `web-lab` へ read-only mount し、container 内 preflight から `docker/mariadb/config-initdb/` を参照できるようにした
- `.env.example`
  - optional `APP_CONFIG_DB_HOST/PORT/NAME/USER/PASSWORD` を追加した
- `mtool/app/config_db_bootstrap.php`
  - config DB preflight helper を追加した
  - current schema marker として required table / required column / forbidden legacy column を検査する
  - target mode を `compose-local-service` / `host-loopback` / `external` で要約する
  - admin site では `APP_DB_* == APP_CONFIG_DB_*` を current requirement として検査する
- `mtool/scripts/check_config_db_bootstrap.php`
  - JSON で preflight 結果を返す CLI を追加した
- `mtool/scripts/migrate_config_db.php`
  - current `config-initdb` を `APP_CONFIG_DB` target へ apply する CLI を追加した
- `Makefile`
  - `config-db-preflight` target を追加した
  - `db-config-migrate` を current `APP_CONFIG_DB` target へ apply する official target に切り替えた
- `tests/Integration/ConfigDbExternalizationContractTest.php`
  - compose/env wiring、admin DB mirror rule、preflight/migration target wiring を固定した

## current rule

- local compose default では `db-config` container をそのまま使う
- external target に切り替えたい時は `APP_CONFIG_DB_*` を指定する
- root compose の `web-admin` は `APP_DB_*` を `APP_CONFIG_DB_*` と同値で受けるので、admin site 側の legacy `db` read path も external target にそろう
- host-side script や shared env で admin lane を使う時も、current は `APP_DB_*` と `APP_CONFIG_DB_*` を同じ target にそろえる
- preflight は次を current schema marker として扱う
  - required table が全部あること
  - `project_source_outputs.spec_visibility` など latest/current column があること
  - `legacy_source_pid` のような dropped legacy column が残っていないこと

## current command

local compose lane:

```bash
make config-db-preflight
```

direct CLI:

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/check_config_db_bootstrap.php --requested-by=manual
```

apply:

```bash
make db-config-migrate
```

direct CLI:

```bash
docker compose exec -T web-admin php /var/www/mtool/scripts/migrate_config_db.php --requested-by=manual
```

host/shared env で external target を直に見る場合の例:

```bash
APP_SITE=admin \
APP_DB_HOST=external-db.example \
APP_DB_PORT=3306 \
APP_DB_NAME=config_app \
APP_DB_USER=config_app \
APP_DB_PASSWORD=secret \
APP_CONFIG_DB_HOST=external-db.example \
APP_CONFIG_DB_PORT=3306 \
APP_CONFIG_DB_NAME=config_app \
APP_CONFIG_DB_USER=config_app \
APP_CONFIG_DB_PASSWORD=secret \
php mtool/scripts/check_config_db_bootstrap.php --requested-by=manual
```

## 今回の boundary

- root compose はまだ `db-config` service に `depends_on` しており、external target を使う場合でも local service topology 自体は残る
- つまり今回の slice は `external target を app が読める / preflight できる` ところまでで、`local compose から db-config service を完全に外す` ところまではやっていない
- migration apply 自体は `docker/mariadb/config-initdb/*.sql` を current `APP_CONFIG_DB` target へ順番どおりに apply する
- current compose lane では `make db-config-migrate` が official target で、default local なら従来どおり `db-config` container に効き、override 時は external target に効く

## 検証

```bash
php -l mtool/app/config_db_bootstrap.php
php -l mtool/scripts/check_config_db_bootstrap.php
php -l mtool/scripts/migrate_config_db.php
php -l tests/Integration/ConfigDbExternalizationContractTest.php
docker compose config
docker compose exec -T web-admin php /var/www/mtool/scripts/check_config_db_bootstrap.php --requested-by=codex
docker compose exec -T web-admin php /var/www/mtool/scripts/migrate_config_db.php --requested-by=codex
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/ConfigDbExternalizationContractTest.php
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/LabDbIngressContractTest.php
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/ProjectMetadataBundleContractTest.php
make config-db-preflight
make db-config-migrate
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```

- `ConfigDbExternalizationContractTest`: `OK (3 tests, 27 assertions)`
- `LabDbIngressContractTest`: `OK (4 tests, 52 assertions)`
- `ProjectMetadataBundleContractTest`: `OK (1 test, 52 assertions)`
- `make config-db-preflight`: `ok=true`, `schema_current=true`
- `make db-config-migrate`: `ok=true`, `applied_file_count=23`, `schema_current=true`
- full suite: `OK (116 tests, 4241 assertions)`

## 次の段

- local compose でも必要なら `db-config` service を optional にできるか検討する
- export/import bundle 側で `database_sources` と secret separation policy をどう扱うか決める
