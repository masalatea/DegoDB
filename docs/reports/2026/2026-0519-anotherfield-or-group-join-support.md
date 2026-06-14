# 2026-05-19 anotherfield OR-group join support

## 目的

- `parameter_type=anotherfield` を含む select join 条件で、non-empty `or_group` が付いた row を current runtime SQL generator でも扱えるようにする。
- 併せて legacy `ORGroupType=andorand` 相当の grouping を current generator に通す。

## 実装

- `mtool/app/project_output_runtime_sql_generator.php`
  - `app_project_output_runtime_sql_resolve_or_group_type()` を追加し、blank / `default` を `orandor` として解釈するようにした。
  - `app_project_output_runtime_sql_grouped_condition_parts()` を legacy grouping に寄せて組み直した。
    - blank `or_group` row は常に `and`
    - `orandor` は `(... or ...) and (... or ...)`
    - `andorand` は blank row があれば `blank and ((... and ...) or (... and ...))`
  - `app_project_output_runtime_sql_build_select_from_sql()` が join ON clause でも上記 grouping を使うようにした。
  - `app_project_output_runtime_sql_try_generate_select_method()` / update / delete も `functionItem.or_group_type` を grouped condition へ渡すようにした。

## テスト

- `tests/Integration/RuntimeSqlGeneratorTest.php`
  - `andorand` grouped condition の式展開
  - `anotherfield + or_group` の join ON clause 生成
  - `try_generate_select_method()` が `or_group_type` を join condition へ反映すること

## 確認結果

- targeted test
  - `docker compose -f compose.yaml -f sample/sample1-simple-table/compose.yaml exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/RuntimeSqlGeneratorTest.php`
  - `3 tests, 8 assertions`

## 補足

- sample seed 上の current canonical metadata では `project_db_access_function_select_wheres.or_group != ''` も `project_db_access_function_select_havings` も 0 件だった。
- そのためこの slice は current baseline を変えるためではなく、future row が入った時に unnecessary delegate へ落ちないようにする先回り実装である。
- permanent docs からは `parameter_type=anotherfield` + non-empty `or_group` を main remaining gap から外した。残る主な gap は HAVING と file parameter である。
