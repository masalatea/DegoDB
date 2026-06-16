# sample13-openapi-api-surface

## 役割

- 役割: single-function proxy target metadata から `OPENAPI-JSON` を publish し、API surface を `openapi.json` として確認する tutorial sample pack
- project key: `SAMPLE13`
- runtime root: `work/sample-packs/sample13-openapi-api-surface/`
- reference output: `reference/OPENAPI-JSON/`

`sample10` は DBAccess CRUD output、`sample12` は external DB source import が主題です。`sample13` は generated API surface の読み方に絞り、OpenAPI artifact と Swagger viewer の入口を扱います。

## 読み方

まず `make sample13-pack-runtime-test` を実行します。manual flow は、`ApiTask` の import / sync 後に `OPENAPI-JSON` だけを publish する流れを分解して確認するためのものです。

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

## Seed 内容

- `projects`
  - `project_key=SAMPLE13`
- physical table
  - `ApiTask`
  - `Id`, `Title`, `Status`, `OwnerName`, `DueDate`, `UpdatedAt`
- DBAccess metadata
  - `ApiTask.GetApiTaskList`
  - `ApiTask.GetApiTask`
- Source Output
  - `OPENAPI-JSON`
  - `artifact_strategy=openapi-json`
  - `target_binding_type=single-function-proxy`

## 手動 flow

```sh
docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample13-openapi-api-surface/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/import_project_tables.php --project-key=SAMPLE13 --source=live-schema --table=ApiTask

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample13-openapi-api-surface/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/sync_project_data_classes.php --project-key=SAMPLE13

docker compose -f compose.yaml -f compose.local-db-config.yaml -f sample/tutorials/sample13-openapi-api-surface/compose.yaml exec -T web-admin \
  php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE13 --source-output-key=OPENAPI-JSON --requested-by=sample13-pack --publish
```

publish 後の viewer route:

```txt
/runs/swagger/SAMPLE13?source_output_key=OPENAPI-JSON
```

## Scope

- `sample13` は OpenAPI JSON artifact の生成と読み方に絞る。
- actual proxy runtime の実行は `sample14+` へ分ける。
- public raw OpenAPI route は扱わない。OpenAPI artifact は authenticated viewer / admin artifact download の内側で使う。

次は `sample14-custom-proxy-runtime` で custom proxy metadata から PHP proxy server artifact を publish します。
