# Project Metadata Export / Import

English companion:
This study note uses `sample15-project-metadata-export-import` to show the smallest project metadata bundle export / import flow.

`sample15-project-metadata-export-import` は、project-scoped canonical metadata bundle を export し、import preview / apply で同じ project の `project-core` metadata を復元する tutorial です。
generated code ではなく、設計 metadata を持ち運ぶことに絞ります。

## 実行

```bash
make sample15-pack-runtime-test
```

## 読むファイル

- [sample15 README](../../sample/tutorials/sample15-project-metadata-export-import/README.md)
- [seed](../../sample/tutorials/sample15-project-metadata-export-import/seed/)
- [reference/PROJECT-METADATA-BUNDLE](../../sample/tutorials/sample15-project-metadata-export-import/reference/PROJECT-METADATA-BUNDLE/)
- [Project Metadata Bundle](../project-metadata-bundle.md)

## 見るポイント

- `PROJECT-METADATA-BUNDLE` は generated code ではなく、canonical metadata の bundle です。
- test は export した bundle と reference bundle を比較し、その後 `preview -> apply` で import を検証します。
- `project-core` scope だけを扱います。

## Boundary

`sample15` では `database_sources` sidecar / secret file、別 project key への rename import、generated code publish は扱いません。これらは bundle format の応用として後続で分けます。
