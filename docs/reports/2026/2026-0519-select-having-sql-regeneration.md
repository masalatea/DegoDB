# 2026-05-19 select having SQL regeneration

## 目的

- current runtime SQL generator で `select_having` を legacy delegate なしに再生成できるようにする。
- canonical `select_target_fields` を左辺/右辺の source of truth とし、`argument` / `fixed` / `field` の 3 種を扱う。

## 実装

- `mtool/app/project_output_runtime_sql_generator.php`
  - `app_project_output_runtime_sql_select_target_field_map()` を追加し、`select_target_field_id` から target-field catalog を引けるようにした。
  - `app_project_output_runtime_sql_select_target_field_expression_by_id()` を追加し、legacy `GetReferencingFieldColumnIfThereis()` 相当の式を current generator 側で解決するようにした。
  - `app_project_output_runtime_sql_compile_select_having_parts()` を追加し、`left_target_prefix/suffix` と `right_target_prefix/suffix` を保ったまま `argument` / `fixed` / `field` の右辺を組み立てるようにした。
  - `app_project_output_runtime_sql_try_generate_select_method()` は `GROUP BY` の後に `HAVING` を組み立てるようにした。引数消費順は legacy と同じく `WHERE` の後、`LIMIT` の前である。

## テスト

- `tests/Integration/RuntimeSqlGeneratorTest.php`
  - `WHERE` 引数の後に `HAVING` 引数を消費し、その後に `LIMIT` 引数を消費すること
  - `HAVING` が `field` 比較と `fixed` 比較を含む場合でも式展開できること

## 確認結果

- `make test`
  - `22 tests, 173 assertions`
- `make mtool-self-loop-check`
  - `ok: true`
  - artifact: `20260519-041345-695e62ac`
  - generation summary:
    - `mode=canonical-dbaccess-partial-sql-regenerated`
    - `generated_dbaccess_count=99`
    - `fallback_dbaccess_count=0`
    - `canonical_function_count=611`
    - `sql_regenerated_dbaccess_count=98`
    - `sql_regenerated_function_count=505`
    - `canonical_helper_function_count=7`
    - `canonical_data_class_count=99`
    - `plain_data_candidate_count=63`
    - `non_plain_data_candidate_count=36`
    - `bootstrap_data_class_count=0`
    - `legacy_delegate_function_count=0`
    - `warnings=[]`

## 補足

- sample seed / current MTOOL metadata では `project_db_access_function_select_havings` が 0 件のため、今回の slice で current generation summary 自体は変わっていない。
- ただし future row が入った際に `select having is not supported yet` で delegate へ落ちる経路は解消された。
- main remaining gap は `file parameter` である。
