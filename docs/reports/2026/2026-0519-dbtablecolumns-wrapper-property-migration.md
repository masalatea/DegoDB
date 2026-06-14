# 2026-05-19 dbtablecolumns wrapper-property migration

## 目的

- `dbtablecolumns` は promoted runtime reference 上では `generated-layered-stub` のまま残っていた。
- 理由は `ADDITIONAL CLASS DEFINITION` に `ColumnListOrderSupposedToBe` という wrapper property と helper method 2 本が同居しており、既存の `method-only` lane では吸えなかったためである。
- この slice では `dbtablecolumns` を wrapper/base 形式へ昇格し、`bootstrap_data_class_count` を 1 件減らすことを目的にした。

## 実装

- `legacy_data_class_editable_area_migrator.php`
  - `ADDITIONAL CLASS DEFINITION` 解析に `property_names` と `has_unsupported_code` を追加した。
  - property 宣言は `has_non_method_code=true` のまま扱いつつ、property + method だけなら `unsupported` にはしないようにした。
  - 新しい support lane `app_legacy_data_class_supports_wrapper_property_method_wrapper_base_migration()` を追加した。
- `project_output_runtime_generator.php`
  - legacy flat source だけでなく、promoted runtime reference の `generated-layered-stub` に対しても `_base/data-*.php` を migration source として解析する helper を追加した。
  - `dbtablecolumns` を `generated` として数え、`canonical_data_class_count=84` / `bootstrap_data_class_count=15` を manifest へ反映するようにした。
- `project_output_service.php`
  - build-plan で upgrade 可能な `generated-layered-stub` root entry を passthrough せず wrapper/base 再出力対象に振り分けるようにした。
  - 対応した root entry では `_base/` / `_wrappers/` companion を final bundle へ残さないようにした。
- regression
  - `sample12-dbtablecolumns-wrapper-property` を追加した。
  - `mtool/scripts/check_sample12_dbtablecolumns_wrapper_property_outputs.php`
  - `tests/Integration/Sample12DbtablecolumnsWrapperPropertyOutputTest.php`
  - `Makefile` と `tests/bootstrap.php` を更新した。

## 確認結果

- `php mtool/scripts/check_sample12_dbtablecolumns_wrapper_property_outputs.php`
  - pass
- `make test`
  - `11 tests, 118 assertions`
- `make mtool-self-loop-check`
  - first run で actual は `canonical_data_class_count=84` / `bootstrap_data_class_count=15` になり、expected snapshot 更新が必要なことを確認
  - `mtool/reference/mtool-self-loop-expected-output.json` を更新後、pass
- promoted artifact
  - `20260519-030155-7c603f73` を `make promote-runtime-reference` 相当で `mtool/reference/dbclasses/` へ promote
- promoted default reference 再確認
  - self-loop artifact `20260519-030308-ea4eead2` で再度 pass

## 現在値

- `sql_regenerated_dbaccess_count=98`
- `sql_regenerated_function_count=505`
- `canonical_helper_function_count=7`
- `canonical_data_class_count=84`
- `data_entity_count=99`
- `plain_data_candidate_count=63`
- `non_plain_data_candidate_count=36`
- `bootstrap_data_class_count=15`
- `legacy_delegate_function_count=0`

## 残件

- latest manifest 上で remaining `data-*` は 15 class
  - `Project`
  - `ProjectSourceOutput`
  - `ProjectUser`
  - `Req`
  - `SpecContent`
  - `daCustomProxy`
  - `daCustomProxyFunc`
  - `dafunc`
  - `dafuncinserttargetfields`
  - `dafuncselecthaving`
  - `dafuncselectwhere`
  - `dafuncupdatedeletewhere`
  - `dafuncupdatetargetfields`
  - `htmlTemplate`
  - `htmlTemplateParameter`
