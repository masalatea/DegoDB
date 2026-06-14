# 2026-05-26 Config DB Externalization Compose Dependency

## 要約

- root compose の `web-admin` / `web-lab` から `depends_on: db-config` を外した。
- default local stack の `db-config` service 定義は残しつつ、external/shared env 向けに `make up-external-config-db` を追加した。
- current rule を `local default = make up`、`external config DB = make up-external-config-db` に整理した。

## 変更

- `compose.yaml`
  - `web-admin` の `db-config` 起動順依存を削除した。
  - `web-lab` の `depends_on` は `db-lab` のみ残し、`db-config` 依存を削除した。
- `Makefile`
  - `up-external-config-db` target を追加した。
  - external `APP_CONFIG_DB_*` target を使う時に、`web-admin` / `web-lab` / `db-lab` と optional `lab-db-ui` だけを起動できるようにした。
- `tests/Integration/ConfigDbExternalizationContractTest.php`
  - root compose の web services が `db-config` を startup dependency に持たないことを固定した。
- `docs/current-supported-workflow.md`
- `docs/common-tasks.md`
  - external config DB lane の起動コマンドと current boundary を更新した。

## current rule

- `make up`
  - default local stack。`db-config` / `db-lab` / `web-admin` / `web-lab` と `lab-db-ui` を上げる。
- `make up-external-config-db`
  - external/shared env 向け。`APP_CONFIG_DB_*` を external MariaDB に向けた上で、local `db-config` を起動せずに `web-admin` / `web-lab` / `db-lab` と `lab-db-ui` を上げる。
- root compose には `db-config` service 定義自体は残す。
- `db-config` shell / migrate / local default lane はまだ維持し、完全 removal は次段の topology 整理で判断する。

## 検証

```bash
php -l tests/Integration/ConfigDbExternalizationContractTest.php
make help
docker compose config
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/ConfigDbExternalizationContractTest.php
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/DocsEntranceContractTest.php
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```

- `make help`: `up-external-config-db` が current target 一覧に表示される
- `ConfigDbExternalizationContractTest`: `OK (5 tests, 45 assertions)`
- `DocsEntranceContractTest`: `OK (5 tests, 78 assertions)`
- full suite: `OK (121 tests, 4394 assertions)`
