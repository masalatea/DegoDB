# 2026-05-20 original-codes Helper Inventory

## later update

- `app_runtime_storage_legacy_dbclasses_*()` helper は live code / test から未使用と確認できたため、same-day 後続変更で削除した。
- これで current code に残る `original-codes/` 参照は、host-side script helper と provenance metadata にさらに絞られた。
- `export_legacy_table_schema_reference.php` は `export_legacy_*_reference.php` 群の中でも例外で、`--sql-dump` ではなく temporary imported legacy schema への `--dsn` / `--schema-name` を受ける helper だった。
- したがって current docs では「`export_legacy_*_reference.php` 群 = すべて dump-path helper」とは書かず、table schema helper を別枠で扱う。

## 前提

- runtime reference history や rollback 導線の durable 化は、この時点では扱わない。
- 後で `git` 化する前提なので、今回は「今残っている `original-codes/` 参照が runtime dependency なのか、host-only helper なのか」を切り分けることに集中する。

## 結論

- current runtime / generator / Docker container が `original-codes/` を直接入力として読む mainline は残っていない。
- 現在の `original-codes/` 参照は、ほぼ次の 2 系統に整理できる。
  - host 側でだけ使う明示 helper
  - reference JSON / resource manifest に残る provenance metadata
- したがって次段で見るべきなのは「さらに helper を減らせるか」であり、「runtime がいつの間にか `original-codes/` を読んでいるか」ではない。

## 1. 残す host-only helper

- `mtool/scripts/bootstrap_dbclasses.sh`
  - host-side legacy recovery helper。
  - default は `work/legacy-recovery/dbclasses` への staged copy で、authoritative runtime reference overwrite は explicit override のみ。
- `mtool/scripts/export_legacy_dataclass_reference.php`
- `mtool/scripts/export_legacy_dbtable_reference.php`
- `mtool/scripts/export_legacy_db_access_reference.php`
- `mtool/scripts/export_legacy_html_reference.php`
- `mtool/scripts/export_legacy_language_resource_reference.php`
  - いずれも `--sql-dump=...` を受ける host-side export helper。
  - help では host filesystem 上の dump path を明示して使うことを案内済み。
- `mtool/scripts/export_legacy_table_schema_reference.php`
  - host-side schema snapshot helper。
  - `--dsn` / `--schema-name` で temporary imported legacy schema を読む想定で、`original-codes/` path を直接受けない。
- `mtool/scripts/export_mtool_db_access_seed.php`
  - current canonical DB と legacy metadata から seed 3 file を再生成する host-side export helper。
  - `--sql-dump` は host filesystem path、`--dbclasses-root` は current runtime reference preflight に使う。

## 2. test / fixture 系

- `tests/fixtures/legacy-dbclasses/README.md`
  - migration sample / declaration test の入力は curated fixture copy であり、`original-codes/` 全体を test runtime に持ち込まないことを明記している。
- `tests/README.md`
  - 同じ boundary を test 方針として明記している。

## 3. provenance metadata として残るもの

- `mtool/reference/*-catalog.json`
  - `source_dump_path: "original-codes/mtool.sql"` を持つ。
  - これは「どの dump から切り出した reference か」を示す provenance であり、current runtime がその path を開くための設定ではない。
- `mtool/resources/manifest.json`
- `sample/sample3-school-booking/resources/manifest.json`
- `sample/sample5-email-management/resources/manifest.json`
- `sample/sample7-minutes/resources/manifest.json`
  - `origin.type=bootstrap-reference` と `origin.source_dump_path=original-codes/mtool.sql` を持つ。
  - file catalog / inspector が origin metadata として保持しているだけで、runtime path resolution には使っていない。

## 根拠メモ

- `app_fetch_project_language_resource_catalog()` は file catalog または copied reference を読むが、`origin.source_dump_path` を open しない。
- `app_language_resource_file_catalog_*()` は `source_dump_path` を manifest に書き戻すが、用途は provenance 維持である。
- `legacy_*_reference.php` loader 群も `source_dump_path` を読み込むが、表示や再出力のために保持しているだけで path 解決には使わない。
- 一時的に置いた `app_runtime_storage_legacy_dbclasses_*()` helper も live code / test から参照されていないことを確認し、same-day 後続変更で削除した。

## 今回の補正

- `mtool/scripts/export_mtool_db_access_seed.php`
  - `sql-dump が見つかりません` の error を `host-side path expected` つきに揃えた。
- `docs/internal/runtime-architecture.md`
- `docs/internal/source-output-path-policy.md`
  - `source_dump_path` は provenance metadata であり、runtime input path ではないことを追記した。

## 検証

- `rg -n "original-codes|legacy_dbclasses_path|sql-dump|bootstrap_dbclasses" mtool/app mtool/scripts tests sample compose.yaml`
- `docker compose exec -T web-admin sh -lc 'test ! -e /var/www/original-codes'`
- `docker compose exec -T web-lab sh -lc 'test ! -e /var/www/original-codes'`
- `php -l mtool/scripts/export_mtool_db_access_seed.php`

## 次

- `bootstrap_dbclasses.sh` をさらに減らすかどうかは、host-side emergency helper を今どこまで残したいかの判断になる。
- provenance metadata の `source_dump_path` は現時点では無害なので、次段で触るとしても rename / wording cleanup 程度で十分である。
