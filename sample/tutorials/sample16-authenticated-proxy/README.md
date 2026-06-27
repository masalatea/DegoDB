# sample16-authenticated-proxy

## 役割

- 役割: `static-bearer` authenticated single proxy server artifact と fail-closed auth behavior を確認する tutorial sample pack
- project key: `SAMPLE16`
- runtime root: `work/sample-packs/sample16-authenticated-proxy/`
- reference output: `reference/AUTH-PROXY-SERVER/`

`sample13` は OpenAPI artifact、`sample14` は custom proxy runtime、`sample15` は project metadata bundle が主題です。`sample16` は generated proxy endpoint の auth 境界に絞ります。

## 読み方

まず `make sample16-pack-runtime-test` を実行します。manual flow は physical table `auth_task` から generated surface `AuthTask` の `AUTH-PROXY-SERVER` artifact を publish する部分だけを取り出したものです。auth の成功 / 失敗 case は PHPUnit checker が generated handler を直接ロードして検証します。

## 起動

```sh
./sample/tutorials/sample16-authenticated-proxy/run.sh up
```

seed を再適用する場合:

```sh
./sample/tutorials/sample16-authenticated-proxy/run.sh apply-seed
```

## 検証

```sh
make sample16-pack-runtime-test
```

`sample16-pack-runtime-test` は container 内 PHPUnit で `tests/Integration/Sample16AuthenticatedProxyTest.php` を実行します。

SQLite config store profile で同じ tutorial を検証:

```sh
make sample16-pack-runtime-test-sqlite
```

手元で軽く動かす場合は、DegoDB 自身の設計メタデータを `APP_CONFIG_STORE_DIR` 配下の SQLite file に保存できます。

```sh
APP_CONFIG_STORE_DIR=work/config-store-sample16-sqlite \
  ./sample/tutorials/sample16-authenticated-proxy/run-sqlite-config.sh up
```

## Seed 内容

- `projects`
  - `project_key=SAMPLE16`
- physical table
  - `auth_task`
  - `id`, `title`, `status`, `owner_name`, `updated_at`
- DBAccess metadata
  - `auth_task.GetAuthTask`
  - generated proxy path/class surface remains `AuthTask` under `physical-logical-v1`
  - `single_proxy_auth_type=StaticBearer`
  - `auth_policy_json={"type":"static-bearer","secret_env":"DEGODB_PROXY_BEARER_TOKEN"}`
- Source Output
  - `AUTH-PROXY-SERVER`
  - `artifact_strategy=single-proxy-server`
  - `target_binding_type=single-function-proxy`

## 手動 flow

```sh
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample16-authenticated-proxy/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE16 --source=live-schema --table=auth_task

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample16-authenticated-proxy/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE16

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample16-authenticated-proxy/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE16 --source-output-key=AUTH-PROXY-SERVER --requested-by=sample16-manual --publish
```

## Auth behavior

- missing `Authorization` fails.
- malformed `Authorization` fails.
- `DEGODB_PROXY_BEARER_TOKEN` missing fails closed.
- wrong bearer token fails.
- matching bearer token passes.

## Scope

- `sample16` は `static-bearer` auth の generated single proxy server に絞る。
- legacy `ProjectToken` / request body `TOKEN` は compatibility lane として残すが、この sample の主役にはしない。
- `GetFunc` / `ProjectTokenOrGetFunc` / `LoginCookieToken` は後続 scope とする。
- HTTP browser smoke ではなく、generated endpoint class の auth method を direct verification する。

次は `sample17-multi-output-project` で同じ project から複数 Source Output を publish する capstone を見ます。
