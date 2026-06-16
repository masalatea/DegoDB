# External DB Source Import

English companion:
This study note uses `sample12-external-db-source-import` to show the smallest flow from an external named database source to DataClass output.

`sample12-external-db-source-import` は、一般ユーザーが自分の DB をつなぐ時の入口に近い tutorial です。
`database_sources` に named source を登録し、`named-live-schema:sample12_lab` から table import、DataClass sync、`DATACLASS-PHP` publish まで進みます。

## 実行

```bash
make sample12-pack-runtime-test
```

## 読むファイル

- [sample12 README](../../sample/tutorials/sample12-external-db-source-import/README.md)
- [config seed](../../sample/tutorials/sample12-external-db-source-import/seed/)
- [lab seed](../../sample/tutorials/sample12-external-db-source-import/lab-seed/)
- [reference/DATACLASS-PHP](../../sample/tutorials/sample12-external-db-source-import/reference/DATACLASS-PHP/)

## 見るポイント

- `database_sources.source_key = sample12_lab`
- import source: `named-live-schema:sample12_lab`
- external physical table: `ExternalArticle`
- generated output: `DATACLASS-PHP`

`sample12` では OpenAPI / proxy runtime は扱いません。外から API surface を確認する flow は `sample13` に分けています。
