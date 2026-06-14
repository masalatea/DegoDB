# 2026-05-26 External Config DB Convenience Targets

## 要約

- external config DB lane で `up-external-config-db` の後に使う follow-up target を最小限追加した。
- current では `down / ps / logs / health / config-db-preflight / db-config-migrate` だけを external lane 向けに用意し、shell 系や start/stop parity までは広げていない。
- current docs を external lane の follow-up target に合わせて更新した。

## 変更

- `Makefile`
  - `down-external-config-db`
  - `ps-external-config-db`
  - `logs-external-config-db`
  - `health-external-config-db`
  - `config-db-preflight-external-config-db`
  - `db-config-migrate-external-config-db`
    を追加した。
  - `make help` に新 target が出るように `PHONY` と env bootstrap 対象を調整した。
- `README.md`
- `docs/start-here.md`
- `docs/common-tasks.md`
- `docs/current-supported-workflow.md`
  - external lane の起動後に使う follow-up target を current wording に追加した。
- `tests/Integration/ConfigDbExternalizationContractTest.php`
  - Makefile に external convenience target 群があることを固定した。
- `tests/Integration/DocsEntranceContractTest.php`
  - entrance docs が `make config-db-preflight-external-config-db` を含むことを固定した。

## current rule

- local default lane
  - `make up` / `make start` / `make stop` / `make ps` / `make logs` / `make config-db-preflight` / `make db-config-migrate`
  - `compose.yaml + compose.local-db-config.yaml`
- external config DB lane
  - 起動: `make up-external-config-db`
  - follow-up: `make ps-external-config-db` / `make logs-external-config-db` / `make health-external-config-db`
  - config DB check/apply: `make config-db-preflight-external-config-db` / `make db-config-migrate-external-config-db`
  - teardown: `make down-external-config-db`
- current non-goal
  - external lane の `start/stop/reset/shell` parity まではまだ増やさない
  - `show_compose_access_urls.sh` の helper 共通化もこの slice では行わない

## 検証

```bash
make help | sed -n '1,120p'
make ps-external-config-db
make health-external-config-db
make config-db-preflight-external-config-db
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/ConfigDbExternalizationContractTest.php
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/DocsEntranceContractTest.php
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```

- `make help`: new external convenience target が表示される
- `make ps-external-config-db`: pass
- `make health-external-config-db`: admin / lab とも `ok=true`
- `make config-db-preflight-external-config-db`: `ok=true`, `schema_current=true`
- `ConfigDbExternalizationContractTest`: `OK (5 tests, 59 assertions)`
- `DocsEntranceContractTest`: `OK (6 tests, 98 assertions)`
- full suite: `OK (124 tests, 4462 assertions)`
