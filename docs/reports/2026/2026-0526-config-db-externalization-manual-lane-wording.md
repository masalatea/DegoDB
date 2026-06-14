# 2026-05-26 Config DB Externalization Manual Lane Wording

## 要約

- current user-facing doc を `compose.local-db-config.yaml` 前提に揃えた。
- `make help` の local-only target と external lane の境界が読めるように Makefile target comment を更新した。
- tutorial sample README の manual `docker compose` 例を local overlay 付きに統一した。

## 変更

- `Makefile`
  - `up` / `start` / `stop` / `down` / `reset` / `ps` / `logs` / `health` / `admin-shell` / `lab-shell` / `db-config-shell` / `db-lab-shell` / `config-db-preflight` / `db-config-migrate` / `db-lab-migrate`
    の help comment を local default stack 前提の wording に更新した。
- `README.md`
- `docs/start-here.md`
- `docs/common-tasks.md`
- `docs/current-supported-workflow.md`
  - `make up = local overlay lane`
  - `make up-external-config-db = external lane`
    の current wording を補強した。
- `sample/tutorials/sample01-simple-table-runtime/README.md` から `sample10-dbaccess-mini-crud-flow/README.md`
  - manual `docker compose -f compose.yaml ... exec` 例に `-f compose.local-db-config.yaml` を追加した。
- `tests/Integration/DocsEntranceContractTest.php`
  - current entrance docs が `compose.local-db-config.yaml` と `make up-external-config-db` を保持することを固定した。
- `tests/Integration/SamplePackCatalogTest.php`
  - shared sample-pack runner が local overlay を読むこと
  - runtime pack README の manual compose lane が local overlay を含むこと
    を固定した。

## current rule

- local default lane
  - `make up` / `make start` / `make stop` / `make ps` / `make logs` / `make db-config-shell` / `make config-db-preflight` / `make db-config-migrate`
  - `compose.yaml + compose.local-db-config.yaml` を使う。
- external lane
  - `make up-external-config-db`
  - `compose.yaml` だけを使い、local `db-config` を起動しない。
- tutorial sample README の manual compose 例
  - local default lane の current wording として `-f compose.local-db-config.yaml` を含める。

## 検証

```bash
php -l tests/Integration/DocsEntranceContractTest.php
php -l tests/Integration/SamplePackCatalogTest.php
make help | sed -n '1,80p'
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/DocsEntranceContractTest.php /var/www/tests/Integration/SamplePackCatalogTest.php
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```

- `DocsEntranceContractTest + SamplePackCatalogTest`: `OK (6 tests, 94 assertions)`
- `make help`: local-only target wording と `up-external-config-db` が current help に表示される
- full suite: `OK (123 tests, 4447 assertions)`
