# Sample 1 Simple Table

## 概要

- `sample/sample1-simple-table/` を追加し、`SAMPLE1` 用の最小 sample pack を作成した。
- seed には canonical `dbtable` / `dbtablecolumns` を `Article` 1 table / 3 columns、canonical `dataclass` / `dataclassfields` を `Article` 1 class / 3 fields だけ入れた。
- buildable な `project_source_outputs` row として `SAMPLE1 / DATACLASS-PHP` を追加し、`legacy-directory-mirror` で curated `data-Article.php` をそのまま bundle / publish できるようにした。
- 今回の slice では `dbaccess-*.php`、actual DB table creation、DB operation 実装は入れていない。

## 追加ファイル

- `sample/sample1-simple-table/README.md`
- `sample/sample1-simple-table/compose.yaml`
- `sample/sample1-simple-table/run.sh`
- `sample/sample1-simple-table/seed/900_010_sample1_project_seed.sql`
- `sample/sample1-simple-table/seed/900_020_sample1_table_and_data_class_seed.sql`
- `sample/sample1-simple-table/seed/900_030_sample1_source_output_seed.sql`
- `sample/sample1-simple-table/reference/DATACLASS-PHP/README.md`
- `sample/sample1-simple-table/reference/DATACLASS-PHP/data-Article.php`

## 付随更新

- `sample/README.md` に `sample1-simple-table` を追加し、基本操作例を `Sample 1` 起点へ更新した。
- root `README.md` の sample pack 起動例を `./sample/sample1-simple-table/run.sh up` へ更新した。
- `mtool/scripts/apply_config_sample_seed.sh` の usage 例を `sample1-simple-table` 起点へ更新した。

## 検証

- `php -l sample/sample1-simple-table/reference/DATACLASS-PHP/data-Article.php`
- `/bin/zsh -lc 'for f in sample/*/compose.yaml; do docker compose -f compose.yaml -f "$f" config -q || exit 1; done'`
- 既存 stack と host port が衝突していたため、isolated verify は alternate host ports で `db-config` + `web-admin` だけ起動した。
- `docker compose -f compose.yaml -f sample/sample1-simple-table/compose.yaml exec -T web-admin php /var/www/mtool/scripts/create_project_output.php --project-key=SAMPLE1 --source-output-key=DATACLASS-PHP --requested-by=codex --publish`
  - artifact key: `20260518-010800-abe1d000`
  - published root: `sample/sample1-simple-table/output/source-outputs/SAMPLE1/DATACLASS-PHP`
  - published files: `README.md`, `data-Article.php`
- `web-admin` container 内 repository 経由の確認:
  - `table_count=1`
  - `column_count=3`
  - `data_class_count=1`
  - `field_count=3`
  - `source_output_count=1`
