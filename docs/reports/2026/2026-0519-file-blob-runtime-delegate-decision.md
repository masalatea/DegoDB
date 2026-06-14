# File/Blob Runtime Delegate Decision

## 目的

- `file parameter` gap について、legacy 実装の具体的 contract を確認したうえで current runtime generator の扱いを固定する。

## 確認できた legacy contract

- `original-codes/mtool.sql`
  - `InsertDegoWorkplaceFile` と `UpdateDegoWorkplaceFile` の generated source が残っている。
  - どちらも SQL 文字列に bare `?` を置くだけでは終わらず、`prepare()` -> `bind_param("b")` -> `fopen($obj->File, "r")` -> `send_long_data()` で blob を流し込んでいる。
- `original-codes/mtool_work_lib/dbclasses/dbaccess-email_buffer_attachment.php`
  - 別系統の blob insert でも同じ `bind_param("b")` / `send_long_data()` パターンを使っている。
- `original-codes/mtool.sql`
  - `DBACCESSCLASS-FUNCTION-INSERT-BLOB` / `DBACCESSCLASS-FUNCTION-UPDATE-BLOB` の template catalog entry と Dropbox metadata は残っている。
  - ただし template file 本体は repo には無く、確認できるのは generated output と metadata までだった。

## 判断

- `IsBlobTarget=1` は単に SQL 断片の差ではなく、prepared statement と file path 読み出しを伴う別 contract とみなす。
- そのため current runtime SQL regeneration では、`is_blob_target=1` の insert/update は明示的に legacy delegate へ落とす。
- `parameter_data_type=file` が単体で見つかった場合も、防御的に unsupported のまま維持する。

## current baseline への影響

- current canonical metadata では live row が無い。
  - `project_db_access_function_insert_target_fields.parameter_data_type='file'`: `0`
  - `project_db_access_function_update_target_fields.parameter_data_type='file'`: `0`
  - `project_db_access_functions.is_blob_target=1`: `0`
- したがって今回の変更は baseline の regenerated count や self-loop を変えず、将来 blob target が再導入されたときの誤生成防止が主目的である。

## 実装

- `mtool/app/project_output_runtime_sql_generator.php`
  - insert/update generation の入口で `is_blob_target=1` を検出したら `legacy-delegate` を返す。
  - `parameter_data_type=file` の defensive guard comment を、`send_long_data()` 前提の contract に合わせて更新した。
- `mtool/app/project_db_access_function_detail_page.php`
  - `IsBlobTarget=1` を legacy blob contract 検出済み function にだけ保存できるようにし、contract status も表示する。
- `mtool/app/project_db_access_function_insert_update_target_field_common.php`
  - `file` data type は `IsBlobTarget=1` かつ legacy blob contract 検出済み function にだけ許可する。
- `mtool/app/db_access_repository_pdo.php`
  - detail page 以外の sync / single-proxy / 将来の repository caller から保存されても同じ制約になるよう、repository 層で `is_blob_target=1` と `parameter_data_type=file` を再検証する。
- `mtool/app/db_access_seed_export_guard.php`
  - direct SQL seed/export 導線でも同じ contract を確認し、unsupported な blob/file metadata を seed SQL へ書き出す前に止める。
- `mtool/scripts/export_mtool_db_access_seed.php`
  - `--dbclasses-root` を受け取り、current runtime reference を使って blob/file contract preflight を実行する。
- `tests/Integration/RuntimeSqlGeneratorTest.php`
  - `InsertDegoWorkplaceFile` / `UpdateDegoWorkplaceFile` 相当の blob target が regenerate されず delegate されることを固定した。
- `tests/Integration/BlobContractGuardTest.php`
  - UI validation / repository guard / seed export guard の全てで legacy blob contract 必須になることを固定した。
