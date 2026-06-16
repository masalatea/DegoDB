# OpenAPI API Surface

English companion:
This study note uses `sample13-openapi-api-surface` to show the smallest OpenAPI artifact publish flow from single-function proxy target metadata.

`sample13-openapi-api-surface` は、DBAccess function を API surface として外から読むための tutorial です。
`ApiTask` の list/detail function を `OPENAPI-JSON` に bind し、`openapi.json` / `build-plan.json` を actual output として publish します。

## 実行

```bash
make sample13-pack-runtime-test
```

## 読むファイル

- [sample13 README](../../sample/tutorials/sample13-openapi-api-surface/README.md)
- [seed](../../sample/tutorials/sample13-openapi-api-surface/seed/)
- [reference/OPENAPI-JSON](../../sample/tutorials/sample13-openapi-api-surface/reference/OPENAPI-JSON/)

## 見るポイント

- `project_source_outputs.source_output_key = OPENAPI-JSON`
- `artifact_strategy = openapi-json`
- `target_binding_type = single-function-proxy`
- `project_db_access_function_source_output_targets`
- generated output: `openapi.json`
- viewer route: `/runs/swagger/SAMPLE13?source_output_key=OPENAPI-JSON`

`sample13` では actual proxy runtime execution は扱いません。custom proxy runtime は `sample14` に分けています。
