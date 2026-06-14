# 2026-05-26 Config DB Externalization Metadata Routing

## 要約

- admin canonical metadata repository の残存 `app_create_pdo()` 呼び出しを `config_db` 向け helper へ寄せた。
- `config DB preflight` / `db-config-migrate` から `APP_DB_* == APP_CONFIG_DB_*` の hard requirement を外し、mismatch は diagnostic warning に落とした。
- built-in `db` は `live-schema` import source と site default DB の意味のまま維持した。

## 変更

- `mtool/app/database.php`
  - `app_create_metadata_pdo()` を追加し、canonical metadata の接続先を `config_db` として明示した。
- `mtool/app/db_access_repository_pdo.php`
- `mtool/app/project_html_repository.php`
- `mtool/app/project_html_source_binding_repository_pdo.php`
- `mtool/app/html_template_repository.php`
  - canonical metadata CRUD を metadata helper 経由へ切り替えた。
- `mtool/app/config_db_bootstrap.php`
  - admin `APP_DB_*` / `APP_CONFIG_DB_*` mismatch を warning-only に変更した。
- `tests/Integration/ConfigDbExternalizationContractTest.php`
  - admin default DB をずらしても metadata は `config_db` へ行くこと、preflight が warning-only で通ることを固定した。
- `docs/current-supported-workflow.md`
- `docs/common-tasks.md`
  - current rule を `canonical metadata = config_db` / `built-in db = live schema source` に更新した。

## current rule

- admin canonical metadata repository / CRUD は `config_db` を読む。
- built-in `db` は `live-schema` import source と site default DB の意味のまま残す。
- compose default では admin service が `APP_DB_*` を `APP_CONFIG_DB_*` に寄せていてもよいが、external/shared env では分離してよい。
- preflight / migrate は `config_db` schema の current 判定を主に見て、admin DB mismatch は warning として扱う。

## 検証

```bash
php -l mtool/app/database.php
php -l mtool/app/config_db_bootstrap.php
php -l tests/Integration/ConfigDbExternalizationContractTest.php
php -l tests/Integration/OpenApiSourceOutputContractTest.php
php -l mtool/app/db_access_repository_pdo.php
make config-db-preflight
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/ConfigDbExternalizationContractTest.php
docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/OpenApiSourceOutputContractTest.php
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```

- `ConfigDbExternalizationContractTest`: `OK (4 tests, 33 assertions)`
- `OpenApiSourceOutputContractTest`: `OK (15 tests, 1627 assertions)`
- `make config-db-preflight`: `ok=true`, `schema_current=true`
- full suite: `OK (119 tests, 4363 assertions)`
