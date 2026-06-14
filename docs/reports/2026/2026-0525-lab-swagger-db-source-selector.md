# 2026-05-25 Lab Swagger DB Source Selector

`lab` の Swagger viewer から external named DB source を自然に指定できるように、`db_source_key` を page state と `Try It Out` request の first-class input に上げた。

## 変更点

- `mtool/app/lab_swagger_service.php`
  - proxy runtime candidate を viewer 用 option に落とす helper を追加した
  - URL query の `db_source_key` を viewer state に戻す helper を追加した
- `mtool/app/lab_swagger_page.php`
  - `db_source_key` selector を toolbar に追加した
  - selected source を summary に表示するようにした
  - resolved URL preview と browser `fetch()` が `db_source_key` query を付けるようにした
  - proxy runtime candidate ではないが catalog には存在する key も explicit query として保持できるようにした
- `mtool/scripts/check_external_database_source_lab_swagger_flow.php`
  - `/runs/swagger/...&db_source_key={source_key}` を開き、viewer selector が選択状態で描画されることを smoke に追加した
- `tests/Integration/OpenApiSourceOutputContractTest.php`
  - viewer 用 runtime DB source option / selection helper の contract を追加した

## 検証

```bash
docker compose exec -T web-admin phpunit /var/www/tests/Integration/OpenApiSourceOutputContractTest.php
ADMIN_HTTP_PORT=18091 LAB_HTTP_PORT=18092 CONFIG_DB_HOST_PORT=43091 LAB_DB_HOST_PORT=43092 make test
php mtool/scripts/check_external_database_source_lab_swagger_flow.php
```

- `OpenApiSourceOutputContractTest`: `OK (9 tests, 1594 assertions)`
- full suite: `OK (106 tests, 4129 assertions)`
- localhost smoke:
  - temporary source: `ext_smoke_0525042959611b`
  - OpenAPI artifact: `20260525-043005-49419f70`
  - proxy artifact: `20260525-043005-db2d1d4a`
  - viewer landing path: `/runs/swagger/MTOOL?source_output_key=OPENAPI-JSON&db_source_key=ext_smoke_0525042959611b`
  - proxy result rows: `Bootstrap Health Check`, `Compare Output Prototype`

## 次

- browser automation まで欲しければ、selector state を保ったまま actual `Try It Out` click を Playwright で通す
- proxy relay 側で explicit `db_source_key` の許容 policy を `supports_proxy_runtime_read` に合わせるかは別判断でよい
