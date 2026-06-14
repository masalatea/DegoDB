# 2026-05-26 Config DB Externalization Local Compose Overlay

## 要約

- base `compose.yaml` から `db-config` service 定義を外した。
- default local stack は `compose.local-db-config.yaml` を重ねる形に変え、`make up` / `make start` / `make config-db-preflight` などの local lane をそちらへ寄せた。
- sample pack / scenario の `db-config` partial override は local overlay を一緒に読むように helper script を更新した。

## 変更

- `compose.yaml`
  - base compose から `db-config` service を外した。
  - `web-admin` / `web-lab` に `compose.local-db-config.yaml` の read-only mount を追加した。
- `compose.local-db-config.yaml`
  - local default 用の `db-config` service 定義を新設した。
- `Makefile`
  - `COMPOSE_LOCAL` と `COMPOSE_BASE` を分け、default local lane は overlay 付き、external lane は base only に整理した。
- `sample/_pack-support/sample-pack-runner.sh`
- `mtool/scripts/run_sample_pack_phpunit_test.sh`
- `mtool/scripts/check_sample_pack_compose_smoke.sh`
- `mtool/scripts/check_sample_pack_runtime_smoke.sh`
- `mtool/scripts/apply_config_sample_seed.sh`
  - sample/scenario helper が `compose.local-db-config.yaml` を一緒に読むようにした。
- `tests/Integration/ConfigDbExternalizationContractTest.php`
  - base compose には `db-config` service が無いこと
  - local overlay には `db-config` service があること
  - Makefile が local overlay を default lane に使うこと
    を固定した。

## current rule

- base `compose.yaml`
  - `db-lab` / `web-admin` / `web-lab` / optional `lab-db-ui` の土台だけを持つ。
- `compose.local-db-config.yaml`
  - local default で使う `db-config` service を足す。
- `make up`
  - `compose.yaml + compose.local-db-config.yaml` を使う current local mainline。
- `make up-external-config-db`
  - `compose.yaml` だけを使い、external `APP_CONFIG_DB_*` target を前提に local `db-config` を起動しない lane。
- sample pack / scenario helper
  - root base compose だけではなく local overlay も読む。partial `db-config` override の merge 先を維持する。

## 検証

```bash
php -l tests/Integration/ConfigDbExternalizationContractTest.php
bash -n mtool/scripts/apply_config_sample_seed.sh
bash -n sample/_pack-support/sample-pack-runner.sh
bash -n mtool/scripts/run_sample_pack_phpunit_test.sh
bash -n mtool/scripts/check_sample_pack_compose_smoke.sh
bash -n mtool/scripts/check_sample_pack_runtime_smoke.sh
docker compose -f compose.yaml config --services
docker compose -f compose.yaml -f compose.local-db-config.yaml config --services
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/ConfigDbExternalizationContractTest.php
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/LabDbIngressContractTest.php
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/DocsEntranceContractTest.php
make sample-pack-compose-smoke
make config-db-preflight
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```

- `docker compose -f compose.yaml config --services`: `db-lab`, `web-admin`, `web-lab`
- `docker compose -f compose.yaml -f compose.local-db-config.yaml config --services`: `db-config`, `db-lab`, `web-admin`, `web-lab`
- `ConfigDbExternalizationContractTest`: `OK (5 tests, 54 assertions)`
- `LabDbIngressContractTest`: `OK (4 tests, 52 assertions)`
- `DocsEntranceContractTest`: `OK (5 tests, 78 assertions)`
- `make sample-pack-compose-smoke`: `17 pack(s)` pass
- `make config-db-preflight`: `ok=true`, `schema_current=true`
- full suite: `OK (121 tests, 4403 assertions)`
