# 2026-05-19 file parameter gap audit

## 目的

- runtime SQL regeneration に残る `file parameter` gap を、その場しのぎで埋めずに legacy semantics と current usage を確認する。

## 確認したこと

- legacy build helper
  - `original-codes/mtool_lib/lib_mtool_build_source_parameter.php`
  - PHP 向け `GetFileValueOfDBAccessClassEscapeVariable()` は file data type を bare `?` として返している。
- legacy build flow
  - `original-codes/mtool_lib/lib_mtool_build_dafunc.php`
  - insert/update target field が file data type の場合に `ParamNameForFile` を保持しているが、repo 上で参照できる template / runtime source からは明確な binding contract を確認できなかった。
- current canonical metadata
  - `project_db_access_function_insert_target_fields.parameter_data_type='file'`: `0`
  - `project_db_access_function_update_target_fields.parameter_data_type='file'`: `0`
  - `project_db_access_functions.is_blob_target=1`: `0`

## 判断

- 現時点では current MTOOL runtime/self-loop に file/blob target function は存在しない。
- 一方で legacy PHP helper の bare `?` をそのまま current generator へ持ち込むと、prepared statement ではない現在の runtime query 実行と整合しない可能性が高い。
- そのため `app_project_output_runtime_sql_value_parts()` は `file parameter data type is not supported yet` のまま維持し、推測実装は入れない。

## 次に必要なもの

- 実在する blob target function の canonical row か、legacy generated output の具体例
- file/blob value を SQL へどう渡していたかの end-to-end contract
  - literal path/string として入れていたのか
  - upload bridge で別保存していたのか
  - prepared statement / manual post-processing が別途あったのか

## 補足

- この audit は `select_having` 対応後の remaining gap を整理するための確認であり、current baseline への code-path 変更はコメント追加のみ。
