# sample13-openapi-api-surface

## 役割

- 役割: single-function proxy target metadata から `OPENAPI-JSON` と `API-PROXY-SERVER` を publish し、API surface と referenced proxy route を確認する tutorial sample pack
- project key: `SAMPLE13`
- runtime root: `work/sample-packs/sample13-openapi-api-surface/`
- reference output: `reference/OPENAPI-JSON/`

`sample10` は DBAccess CRUD output、`sample12` は external DB source import が主題です。`sample13` は generated API surface の読み方に絞り、OpenAPI artifact、Swagger viewer、viewer が参照する single proxy route の入口を扱います。

## 読み方

まず `make sample13-pack-runtime-test` を実行します。HTTP route まで含めて見る場合は `make sample13-http-runtime-smoke`、実ブラウザの Try It Out まで含めて見る場合は `make sample13-browser-try-it-out-smoke` を実行します。manual flow は、physical table `api_task` の import / sync 後に、generated API surface として `ApiTask` を保った `OPENAPI-JSON` と `API-PROXY-SERVER` を publish する流れを分解して確認するためのものです。

## 起動

```sh
./sample/tutorials/sample13-openapi-api-surface/run.sh up
```

seed を再適用する場合:

```sh
./sample/tutorials/sample13-openapi-api-surface/run.sh apply-seed
```

## 検証

```sh
make sample13-pack-runtime-test
```

`sample13-pack-runtime-test` は container 内 PHPUnit で `tests/Integration/Sample13OpenApiApiSurfaceOutputTest.php` を実行します。

SQLite config store profile で同じ tutorial を検証:

```sh
make sample13-pack-runtime-test-sqlite
```

Swagger viewer と referenced proxy route を HTTP 経由で検証:

```sh
make sample13-http-runtime-smoke
make sample13-http-runtime-smoke-sqlite
```

Swagger viewer の Try It Out を headless Chrome 経由で検証:

```sh
make sample13-browser-try-it-out-smoke
make sample13-browser-try-it-out-smoke-sqlite
```

手元で軽く動かす場合は、DegoDB 自身の設計メタデータを `APP_CONFIG_STORE_DIR` 配下の SQLite file に保存できます。

```sh
APP_CONFIG_STORE_DIR=work/config-store-sample13-sqlite \
  ./sample/tutorials/sample13-openapi-api-surface/run-sqlite-config.sh up
```

## Seed 内容

- `projects`
  - `project_key=SAMPLE13`
- physical table
  - `api_task`
  - `id`, `title`, `status`, `owner_name`, `due_date`, `updated_at`
- DBAccess metadata
  - `api_task.GetApiTaskList`
  - `api_task.GetApiTask`
  - generated API path/schema surface remains `ApiTask` / `ApiTaskData` under `physical-logical-v1`
- Source Output
  - `OPENAPI-JSON`
  - `artifact_strategy=openapi-json`
  - `target_binding_type=single-function-proxy`
  - `API-PROXY-SERVER`
  - `artifact_strategy=single-proxy-server`
  - `target_binding_type=single-function-proxy`

## 手動 flow

```sh
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample13-openapi-api-surface/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE13 --source=live-schema --table=api_task

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample13-openapi-api-surface/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE13

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample13-openapi-api-surface/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE13 --source-output-key=OPENAPI-JSON --requested-by=sample13-pack --publish

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample13-openapi-api-surface/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE13 --source-output-key=API-PROXY-SERVER --requested-by=sample13-pack --publish
```

publish 後の viewer route:

```txt
/runs/swagger/SAMPLE13?source_output_key=OPENAPI-JSON
```

## Scope

- `sample13` は OpenAPI JSON artifact の生成と読み方に絞る。
- actual proxy runtime の複雑な custom flow は `sample14+` へ分ける。`sample13` では OpenAPI が参照する NoSecurity single proxy route の最小実行だけを扱う。
- public raw OpenAPI route は扱わない。OpenAPI artifact は authenticated viewer / admin artifact download の内側で使う。

次は `sample14-custom-proxy-runtime` で custom proxy metadata から PHP proxy server artifact を publish します。
