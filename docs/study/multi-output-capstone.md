# Multi Output Capstone

English companion:
This study note uses `sample17-multi-output-project` to show how one project can publish multiple Source Outputs from the same canonical metadata.

`sample17-multi-output-project` は、current tutorial lane の final capstone です。
`CapstoneTask` 1 table と read function 2 本を使い、同じ `SAMPLE17` project から `DATACLASS-PHP`、`DBACCESS-PHP`、`HTML-PAGE`、`OPENAPI-JSON` を publish します。

## 実行

```bash
make sample17-pack-runtime-test
```

## 読むファイル

- [sample17 README](../../sample/tutorials/sample17-multi-output-project/README.md)
- [seed](../../sample/tutorials/sample17-multi-output-project/seed/)
- [reference/DATACLASS-PHP](../../sample/tutorials/sample17-multi-output-project/reference/DATACLASS-PHP/)
- [reference/DBACCESS-PHP](../../sample/tutorials/sample17-multi-output-project/reference/DBACCESS-PHP/)
- [reference/HTML-PAGE](../../sample/tutorials/sample17-multi-output-project/reference/HTML-PAGE/)
- [reference/OPENAPI-JSON](../../sample/tutorials/sample17-multi-output-project/reference/OPENAPI-JSON/)

## 見るポイント

- `900_020_sample17_table_seed.sql` creates the live table only; canonical table / DataClass metadata is created by import / sync.
- `900_025_sample17_db_access_seed.sql` defines read metadata that feeds both `DBACCESS-PHP` and `OPENAPI-JSON`.
- `900_040_sample17_source_output_seed.sql` keeps four output definitions in the same project.
- `reference/` stores actual generated output from the runtime, not imitation files.

## Boundary

`sample17` is about multi-output publish from one project. Project metadata bundle export / import remains in `sample15`, and the current authenticated proxy security baseline remains in `sample16` as static bearer auth.
