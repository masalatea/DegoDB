# 2026-06-17 HTTP Runtime Smoke Plan

## Purpose

DB layer parity inventory の次 slice として、generated proxy / OpenAPI runtime を HTTP 経由で確認する smoke test の設計を整理する。

目的は、MySQL / MariaDB default lane と SQLite config store lane の両方で、generated output が file comparison だけでなく HTTP route として動くことを確認することである。

## Current Baseline

現在確認済み:

- `make test`
  - MySQL / MariaDB default config store + integration suite
  - `OK (174 tests, 7119 assertions)`
- `make sample16-pack-runtime-test-sqlite`
  - SQLite config store + authenticated proxy generated runtime bundle file/support smoke
  - `OK (1 test, 35 assertions)`
- `make sample17-pack-runtime-test-sqlite`
  - SQLite config store + multi-output capstone
  - `OK (1 test, 7 assertions)`
- `make sample16-http-runtime-smoke`
  - MySQL / MariaDB config store + Lab HTTP published proxy route smoke
  - `OK`
- `make sample16-http-runtime-smoke-sqlite`
  - SQLite config store + Lab HTTP published proxy route smoke
  - `OK`
- `make sample13-http-runtime-smoke`
  - MySQL / MariaDB config store + Lab HTTP Swagger viewer route + referenced proxy route smoke
  - `OK`
- `make sample13-http-runtime-smoke-sqlite`
  - SQLite config store + Lab HTTP Swagger viewer route + referenced proxy route smoke
  - `OK`
- `make sample13-browser-try-it-out-smoke`
  - MySQL / MariaDB config store + Lab Swagger viewer Try It Out browser smoke
  - `OK`
- `make sample13-browser-try-it-out-smoke-sqlite`
  - SQLite config store + Lab Swagger viewer Try It Out browser smoke
  - `OK`

残る不足は、今回の representative samples ではなく、他 sample / auth-required OpenAPI operation への横展開である。

## Existing Assets

再利用候補:

- `mtool/scripts/check_external_database_source_lab_swagger_flow.php`
  - admin login
  - source registration
  - table import
  - data class sync
  - DB access sync
  - OpenAPI publish
  - proxy publish
  - lab login
  - swagger page load
  - published proxy route HTTP GET
- `sample13-openapi-api-surface`
  - OpenAPI JSON artifact の最小 sample。
  - SQLite config store lane あり。
- `sample16-authenticated-proxy`
  - authenticated proxy artifact の sample。
  - runtime dbclasses support file smoke 済み。
  - SQLite config store lane あり。
- `sample17-multi-output-project`
  - DataClass / DBAccess / HTML / OpenAPI capstone。
  - SQLite config store lane あり。

## Scope Decision

HTTP runtime smoke は 2 種類に分ける。

1. OpenAPI viewer / proxy route smoke
   - swagger viewer が published `openapi.json` を解決できる。
   - generated proxy route を Lab HTTP route 経由で呼べる。
   - 対象候補: sample13 または sample17。
2. Authenticated proxy route smoke
   - missing / wrong token が HTTP response として fail closed になる。
   - matching token が HTTP response として success になる。
   - 対象候補: sample16。

最初の実装 slice は、sample16 の authenticated proxy route smoke がよい。

理由:

- 既に direct handler auth check がある。
- runtime dbclasses support files が reference comparison に入っている。
- HTTP success / failure が明確で、MySQL / MariaDB と SQLite config store の parity を見やすい。
- OpenAPI viewer UI よりも assertion surface が小さい。

## Proposed Make Targets

候補:

```make
sample16-http-runtime-smoke
sample16-http-runtime-smoke-sqlite
sample13-http-runtime-smoke
sample13-http-runtime-smoke-sqlite
sample13-browser-try-it-out-smoke
sample13-browser-try-it-out-smoke-sqlite
```

sample16 authenticated proxy、sample13 Swagger viewer + referenced proxy route、sample13 Try It Out / browser fetch smoke は実装済み。

## Implemented First Slice

実装済み:

- `mtool/scripts/check_sample16_authenticated_proxy_http_smoke.php`
  - Lab health check。
  - Lab login + CSRF handling。
  - published proxy route への JSON POST。
  - missing token / wrong token の fail-closed check。
  - matching token の success payload check。
- `make sample16-http-runtime-smoke`
  - MySQL / MariaDB config store lane。
  - `KEEP_SAMPLE_STACK_RUNNING=1` で sample16 generation / publish 後に HTTP smoke を実行する。
- `make sample16-http-runtime-smoke-sqlite`
  - SQLite config store lane。
  - `APP_CONFIG_STORE_DIR=work/tmp/config-store-sample16-http-sqlite-*` を使う。

実装中に見つかった問題:

- generated proxy bundle の `autoload_proxy_runtime.php` が、runtime adapter より先に legacy mysqli-only `connect_mtooldb_if_not_yet()` を定義していた。
- そのため、HTTP route では `_support/mtool_runtime_db.php` の generated adapter が使われず、MySQL lane で `Call to undefined method mysqli::execute()` になっていた。
- `autoload_proxy_runtime.php` は `_support/mtool_runtime_db.php` を先に require し、接続関数は generated adapter に任せる形へ変更した。
- この修正により、MySQL / MariaDB と SQLite の両方で published proxy route が同じ generated DBAccess adapter を使う。

Verification:

| Check | Result | Notes |
| --- | --- | --- |
| `make sample16-pack-runtime-test` | OK | `1 test, 35 assertions` |
| `make sample16-http-runtime-smoke` | OK | missing/wrong token fail closed, matching token `200 OK` |
| `make sample16-http-runtime-smoke-sqlite` | OK | SQLite config store lane でも同じ payload contract |
| `make sample13-http-runtime-smoke` | OK | Swagger viewer loads published OpenAPI spec with 2 operations and referenced proxy returns `200 OK` |
| `make sample13-http-runtime-smoke-sqlite` | OK | SQLite config store lane でも同じ viewer + proxy contract |
| `make sample13-browser-try-it-out-smoke` | OK | headless Chrome で Swagger viewer の `ApiTask.GetApiTask` Try It Out が `HTTP 200 OK` |
| `make sample13-browser-try-it-out-smoke-sqlite` | OK | SQLite config store lane でも同じ Try It Out contract |
| `make test` | OK | `174 tests, 7119 assertions` |

Additional post-metadata verification:

| Check | Result | Notes |
| --- | --- | --- |
| `make sample13-pack-runtime-test-sqlite` | OK | OpenAPI build-plan `parameter_schemas` stays aligned in SQLite config store lane |
| `make sample16-pack-runtime-test` | OK | authenticated proxy build-plan carries stable `AuthTask.Id` select where metadata |
| `make sample16-pack-runtime-test-sqlite` | OK | same authenticated proxy metadata and runtime contract in SQLite config store lane |
| `make sample17-pack-runtime-test-sqlite` | OK | multi-output OpenAPI build-plan `parameter_schemas` stays aligned |

## Implemented Second Slice

実装済み:

- `mtool/scripts/check_sample13_openapi_swagger_http_smoke.php`
  - Lab health check。
  - Lab login + CSRF handling。
  - `/runs/swagger/SAMPLE13?source_output_key=OPENAPI-JSON&db_source_key=config_db` の HTTP GET。
  - published `openapi.json` が viewer 上で `published-output` として解決されることを確認。
  - operations count、`ApiTask.GetApiTask`、`ApiTask.GetApiTaskList`、`db_source_key` selector、raw spec を確認。
  - OpenAPI が参照する `/runs/proxy/SAMPLE13/API-PROXY-SERVER/proxyserver-ApiTask-GetApiTask.php` を HTTP POST し、`200 OK` と `Result` payload を確認。
- `make sample13-http-runtime-smoke`
  - MySQL / MariaDB config store lane。
  - sample13 OpenAPI generation / publish 後に `API-PROXY-SERVER` も publish し、Swagger viewer + proxy route HTTP smoke を実行する。
- `make sample13-http-runtime-smoke-sqlite`
  - SQLite config store lane。
  - `APP_CONFIG_STORE_DIR=work/tmp/config-store-sample13-http-sqlite-*` を使う。

## Implemented Third Slice

実装済み:

- `mtool/scripts/check_sample13_openapi_swagger_try_it_out.js`
  - headless Chrome / Playwright で Lab login。
  - `/runs/swagger/SAMPLE13?source_output_key=OPENAPI-JSON&db_source_key=config_db` を開く。
  - viewer の `db_source_key` selector が `config_db` を保持していることを確認。
  - viewer の `base_url` を current Lab port の `/runs/proxy/SAMPLE13/API-PROXY-SERVER` に差し替える。
  - `ApiTask.GetApiTask` の request textarea に `param_ApiTask_Id_where=1` を入れ、Try It Out を click。
  - browser fetch の network response と viewer response output が `HTTP 200 OK` / `_status=OK` / `Result.Title` を返すことを確認。
  - before / after screenshot と `result.json` を `output/playwright/sample13-openapi-swagger/` に保存。
- `make sample13-browser-try-it-out-smoke`
  - MySQL / MariaDB config store lane。
  - sample13 generation / publish 後に `API-PROXY-SERVER` を publish し、browser smoke を実行する。
- `make sample13-browser-try-it-out-smoke-sqlite`
  - SQLite config store lane。
  - `APP_CONFIG_STORE_DIR=work/tmp/config-store-sample13-browser-sqlite-*` を使う。

## Proposed Script Shape

候補 script:

```text
mtool/scripts/check_sample16_authenticated_proxy_http_smoke.php
```

入力:

- `--admin-base-url`
- `--lab-base-url`
- `--project-key=SAMPLE16`
- `--source-output-key=AUTH-PROXY-SERVER`
- `--token=...`
- `--expected-title=...` or expected payload marker

処理:

1. admin / lab health を確認する。
2. sample pack runner が seed / publish 済みの状態を前提にする。
3. Lab route の proxy endpoint path を作る。
4. token なし request が non-2xx または auth error payload になることを確認する。
5. wrong token request が non-2xx または auth error payload になることを確認する。
6. matching token request が 2xx になり、JSON payload が expected shape であることを確認する。
7. result JSON を stdout に出す。

HTTP route candidate:

```text
/runs/proxy/SAMPLE16/AUTH-PROXY-SERVER/proxyserver-AuthTask-GetAuthTask.php
```

Token / parameter delivery:

- request method: `POST`
- body: JSON object
- token key: `TOKEN`
- scalar argument key: `param_AuthTask_Id_where`

Example body:

```json
{
  "TOKEN": "sample16-secret",
  "param_AuthTask_Id_where": 1
}
```

Reason:

- generated `single_proxy_runtime.php` decodes `php://input` as JSON object.
- `project-token` auth strategy expects `TOKEN` in the decoded payload.
- generated `AuthTask.GetAuthTask` step reads the scalar argument from `param_AuthTask_Id_where`.

## Dual Profile Gate

MySQL / MariaDB default lane:

```bash
make sample16-pack-runtime-test
make sample16-http-runtime-smoke
```

SQLite config store lane:

```bash
make sample16-pack-runtime-test-sqlite
make sample16-http-runtime-smoke-sqlite
```

注意:

- config store driver は admin metadata store の違いであり、Lab runtime DB / user DB source とは別に扱う。
- HTTP smoke の success payload は、config store profile に依存しない同一 contract にする。

## Open Questions

- sample pack runner の lifecycle 中に HTTP smoke を差し込むか、runner 後に compose project を keep して別 script を実行するか。
- `run_sample_pack_phpunit_test.sh` は現状 teardown するため、HTTP smoke 用に `SAMPLE_PACK_INCLUDE_LIFECYCLE` / keep option を拡張する必要があるか。
- Swagger viewer の page-load と referenced proxy endpoint smoke は PHP HTTP client で確認し、Try It Out の browser-side form operation は Playwright browser smoke で確認する。

## Next Implementation Slice

1. OpenAPI examples の typed scalar 改善と DBAccess metadata-backed scalar typing first slice は完了。Review: [AI Generation Review: sample13 / sample16](2026-0617-ai-generation-review-sample13-sample16.md).
2. auth-required OpenAPI operation を browser Try It Out smoke に広げるか判断する。
3. ProjectToken / future bearer auth の OpenAPI representation を整理する。
4. richer DBAccess parameter metadata を追加する場合は、OpenAPI scalar typing の metadata-backed 対象を広げる。
