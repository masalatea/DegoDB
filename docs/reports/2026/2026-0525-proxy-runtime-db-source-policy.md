# 2026-05-25 Proxy Runtime DB Source Policy

`db_source_key` selector を入れた後、viewer helper と published proxy relay の許容条件を `supports_proxy_runtime_read` flag に揃えた。

## 変更点

- `mtool/app/database.php`
  - `app_database_source_supports_proxy_runtime_read()` を追加した
- `mtool/app/lab_swagger_service.php`
  - viewer の `db_source_key` query は proxy runtime candidate のみ受け付けるようにした
  - catalog には存在するが `supports_proxy_runtime_read=0` の key は notice を出して auto-select に戻す
- `mtool/app/lab_published_single_proxy_page.php`
  - explicit `db_source_key` / `db_config_key` を validate する helper を追加した
  - `supports_proxy_runtime_read=0` または unknown key の explicit query は `422` で拒否する
  - legacy `db_config_key` query は `supports_proxy_runtime_read=1` の source に限って互換維持する
- `tests/Integration/OpenApiSourceOutputContractTest.php`
  - viewer selection helper が policy 外 source を弾く contract を追加した
  - published proxy helper が `db_source_key=db` を拒否し、`db_config_key=lab_db` を互換許可する contract を追加した

## 検証

```bash
docker compose exec -T web-admin phpunit /var/www/tests/Integration/OpenApiSourceOutputContractTest.php
php mtool/scripts/check_external_database_source_lab_swagger_flow.php
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
```

- `OpenApiSourceOutputContractTest`: `OK (10 tests, 1604 assertions)`
- localhost smoke:
  - temporary source: `ext_smoke_0525044504efff`
  - OpenAPI artifact: `20260525-044510-91aee1f5`
  - proxy artifact: `20260525-044510-64418502`
  - viewer landing path: `/runs/swagger/MTOOL?source_output_key=OPENAPI-JSON&db_source_key=ext_smoke_0525044504efff`
  - proxy result rows: `Bootstrap Health Check`, `Compare Output Prototype`
- full suite: `OK (107 tests, 4139 assertions)`

## 次

- browser automation で selector 操作と `Try It Out` click を actual browser 上で固定する
- 必要なら invalid `db_source_key` の `422` response を HTTP contract test として追加する
