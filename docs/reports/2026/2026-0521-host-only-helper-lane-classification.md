# 2026-05-21 Host-Only Helper Lane Classification

## 結論

- host-only helper inventory は、current では `explicit export` / `last-resort staging` / `provenance metadata` の 3 lane に分けて扱うのが妥当である。
- `export_legacy_*_reference.php` 群、`export_legacy_table_schema_reference.php`、`export_mtool_db_access_seed.php` は、current runtime / admin が読む durable input を再生成する helper なので archive 候補ではない。
- `bootstrap_dbclasses.sh` と `make bootstrap-dbclasses` だけは staged legacy copy を作るための `last-resort staging` helper であり、current repo で archive 候補として絞ってよい。
- `source_dump_path` / `bootstrap-reference` は helper ではなく provenance metadata なので、retirement ではなく rename migration の要否として別タスクで扱う。

## 背景

- `2026-0520-original-codes-helper-inventory.md` までで、`original-codes/` 参照は host-only helper と provenance metadata に絞れていた。
- `2026-0520-host-side-export-helper-explicit-gate.md` と `2026-0520-bootstrap-dbclasses-last-resort-gate.md` で、helper 実行には明示 flag が必要になった。
- ただし「どの helper が current durable input を更新し、どの helper が staging だけを作るか」は 1 箇所にまとまっていなかった。
- priority は helper の casual use 防止から、archive 候補の実質整理へ移っている。

## 1. explicit export

- `mtool/scripts/export_legacy_dataclass_reference.php`
  - `mtool/reference/mtool-legacy-dataclass-catalog.json` を再生成する。
  - `mtool/app/legacy_dataclass_reference.php` と `mtool/app/project_output_html_module_generator.php` が current runtime で読む。
- `mtool/scripts/export_legacy_dbtable_reference.php`
  - `mtool/reference/mtool-legacy-dbtable-catalog.json` を再生成する。
  - `mtool/app/legacy_dbtable_reference.php` と `mtool/app/project_output_html_module_generator.php` が current runtime で読む。
- `mtool/scripts/export_legacy_db_access_reference.php`
  - `mtool/reference/mtool-legacy-db-access-catalog.json` を再生成する。
  - `mtool/app/legacy_db_access_reference.php`、`mtool/app/project_html_repository.php`、`mtool/app/project_output_html_module_generator.php` が current runtime で読む。
- `mtool/scripts/export_legacy_html_reference.php`
  - `mtool/reference/mtool-legacy-html-catalog.json` を再生成する。
  - `mtool/app/legacy_html_reference.php`、`mtool/app/project_html_repository.php`、`mtool/app/html_template_repository.php` が current runtime で読む。
- `mtool/scripts/export_legacy_language_resource_reference.php`
  - `mtool/reference/mtool-legacy-language-resource-catalog.json` を再生成する。
  - `mtool/app/legacy_language_resource_reference.php`、`mtool/app/project_language_resource_catalog_loader.php`、`mtool/app/language_resource_file_catalog.php` が current runtime で読む。
- `mtool/scripts/export_legacy_table_schema_reference.php`
  - copied legacy table schema reference を再生成する temporary schema export helper であり、`--sql-dump` ではなく host-side temporary schema を読む。
  - `mtool/app/legacy_table_schema_reference.php`、`mtool/app/project_table_import_source.php`、`mtool/app/project_table_import_service.php` が current runtime で読む。
- `mtool/scripts/export_mtool_db_access_seed.php`
  - `mtool/docker/mariadb/config-seed/019_project_db_access_class_function_seed.sql`
  - `mtool/docker/mariadb/config-seed/020_project_db_access_designer_seed.sql`
  - `mtool/docker/mariadb/config-seed/022_backfill_runtime_legacy_selectlist_sort_order_columns.sql`
  - 上記の current canonical seed/backfill file を再生成する。

### 判断

- これらは「legacy 由来の host-side helper」ではあるが、出力先は current repo 内の durable input である。
- archive すると refresh path だけが失われ、current app/runtime の参照先自体は残るため、運用上は `stale durable input を固定化する` 方向の risk になる。
- したがって current では archive 候補に含めない。

## 2. last-resort staging

- `mtool/scripts/bootstrap_dbclasses.sh`
- `make bootstrap-dbclasses ACKNOWLEDGE_LAST_RESORT=1`
  - host-side `original-codes/mtool_lib/dbclasses` を `work/legacy-recovery/dbclasses` へ staged copy する。
  - authoritative runtime reference は更新しない。
  - current repair / rollback 主系は `make restore-runtime-reference-snapshot ARTIFACT_KEY=...` であり、この helper は durable input refresh ではなく staging 専用である。

### 判断

- この lane は current runtime input を再生成しない。
- したがって host-only helper inventory の中で、archive 候補として本当に絞るべき対象はこの lane だけである。
- ただし current では `not ready` のままにする。
  - authoritative runtime reference repair は self-generated snapshot restore へ移った。
  - sample / migration test も fixtures へ移った。
  - read-only diff / inspection も host-side reference や promoted snapshot を直接見れば足りる。
  - ただし host-side で quarantined された writable full-tree legacy copy を使う emergency preparation の代替手順まではまだ docs 化していない。

### 短縮版

- 残す理由
  - host-side quarantined full-tree emergency preparation の導線がまだ残っているため。
- archive できる条件
  - 上記 emergency preparation lane を不要化するか、同等の host-side 代替手順を docs 化できた時。

## 3. provenance metadata

- `mtool/reference/*-catalog.json` の `source_dump_path`
- `mtool/resources/manifest.json` と `sample/*/resources/manifest.json` の `origin.type=bootstrap-reference`
- 同 manifest の `origin.source_dump_path`
- `mtool/app/project_html_repository.php`、`mtool/app/project_language_resource_catalog_loader.php`、`mtool/app/language_resource_file_catalog.php` の provenance-only note / comment

### 判断

- これは helper ではなく、historical origin を残す stored metadata である。
- current runtime がこれらの path/value を open path として使う導線はない。
- したがって archive 候補ではなく、rename するなら DB row / manifest / docs をまとめて動かす dedicated migration task にする。

## まとめ

- `explicit export`
  - current durable input refresh helper
  - archive 候補ではない
- `last-resort staging`
  - staged legacy copy 専用 helper
  - archive 候補は `bootstrap_dbclasses.sh` 系だけ
- `provenance metadata`
  - rename migration の論点であり helper retirement ではない

## 検証

- `rg -n "mtool-legacy-dataclass-catalog|legacy_dataclass_reference" mtool/app docs tests`
- `rg -n "mtool-legacy-dbtable-catalog|legacy_dbtable_reference" mtool/app docs tests`
- `rg -n "mtool-legacy-db-access-catalog|legacy_db_access_reference" mtool/app docs tests`
- `rg -n "mtool-legacy-html-catalog|legacy_html_reference" mtool/app docs tests`
- `rg -n "mtool-legacy-language-resource-catalog|legacy_language_resource_reference" mtool/app docs tests`
- `rg -n "export_mtool_db_access_seed|019_project_db_access_class_function_seed|020_project_db_access_designer_seed|022_backfill_runtime_legacy_selectlist_sort_order_columns" mtool/app docs tests mtool/docker`
- `rg -n "export_legacy_table_schema_reference|legacy_table_schema_reference" mtool/app docs tests`

## 次

1. `bootstrap_dbclasses.sh` を archive するかどうかは、staged legacy copy の代替手順を先に定義できるかで判断する
2. `source_dump_path` / `bootstrap-reference` rename は DB row / manifest migration 前提の別タスクとして切り出す
3. runtime replacement 本体は引き続き simple lane を direct replacement、complex lane を sample gate で進める
