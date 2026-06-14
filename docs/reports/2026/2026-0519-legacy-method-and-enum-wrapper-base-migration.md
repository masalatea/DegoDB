# 2026-05-19 legacy method-and-enum wrapper/base migration

## 目的

- `dbtablecolumns` 移行後も、`generated-layered-stub` の `data-*` が 15 class 残っていた。
- このうち 12 class は `ADDITIONAL CLASS DEFINITION` に method 群だけを持ち、wrapper property は無く、trailing enum class と top-level helper function を `BOTTOM` に持つだけだった。
- この slice では、その broad lane を wrapper/base へ吸収して `bootstrap_data_class_count` を `15 -> 3` まで減らすことを目的にした。

## 実装

- `legacy_data_class_editable_area_migrator.php`
  - `app_legacy_data_class_supports_method_and_enum_wrapper_base_migration()` を追加した。
  - `ADDITIONAL CLASS DEFINITION` の method 群、`BOTTOM` の top-level helper function、base 側へ送る trailing enum class を分離できるようにした。
- `project_output_runtime_generator.php`
  - `app_project_output_runtime_data_legacy_migration_support()` が `supports_legacy_method_and_enum_migration` を返すようにした。
  - 対象 class は `generated-legacy-method-and-enum-wrapper-base` として manifest に記録するようにした。
- `project_output_service.php`
  - build-plan で対象 `generated-layered-stub` entry を wrapper/base 再出力対象へ振り分けるようにした。
- regression
  - `sample13-req-method-and-enum` を追加した。
  - `mtool/scripts/check_sample13_req_method_and_enum_outputs.php`
  - `tests/Integration/Sample13ReqMethodAndEnumOutputTest.php`
  - `Makefile` と `tests/bootstrap.php` を更新した。

## ApacheHostSetting の扱い

- `ApacheHostSettingData` は `InitializeByTemplate()` で `ApacheHostSettingTemplate` を展開し、VirtualHost filename / log filename / config text を組み立てるための DTO だった。
- `lib_mtool_apache.php` では `ApacheHostSetting` / `ApacheHostSettingTemplate` を使って Apache config file を Dropbox へ出力し、log monitor snapshot の対象 log file も組み立てていた。
- `project_host_assignment_edit_include.php` では host assignment UI の選択肢を作るために `ApacheHostSettingDBAccess::GetAllList()` を読んでいた。
- つまり用途は Apache config 出力と host assignment infra であり、current app runtime 自体の自己生成 bundle が依存する class ではない。
- このため `ApacheHostSetting` / `ApacheHostSettingTemplate` は runtime/self-loop scope から引き続き明示除外とし、必要なら後段の infra catalog / host-assignment module 側で別管理する。

## 確認結果

- `php mtool/scripts/check_sample13_req_method_and_enum_outputs.php --requested-by=codex`
  - pass
  - reference mismatch は `data-Req.php` / `base/data-ReqBase.php` の EOF 差だけだったため、reference 側を末尾改行なしへ同期した
- `make test`
  - `12 tests, 124 assertions`
- `make mtool-self-loop-check`
  - first run で actual は `canonical_data_class_count=96` / `bootstrap_data_class_count=3` となり、`mtool/reference/mtool-self-loop-expected-output.json` の更新だけが必要なことを確認した
  - `data-Project.php` の representative digest を更新し、`_wrappers/data-Project.php` / `_base/data-Project.php` を `base/data-ProjectBase.php` へ差し替えた後に pass
- promote
  - artifact `20260519-031821-49b3d04f` を `php mtool/scripts/promote_runtime_reference.php --requested-by=codex --artifact-key=20260519-031821-49b3d04f` で `mtool/reference/dbclasses/` へ promote
  - promoted file count: `502`
  - promoted total bytes: `1422582`
- promoted default reference 再確認
  - self-loop artifact `20260519-031842-6f6c108f` で再度 pass

## 現在値

- `sql_regenerated_dbaccess_count=98`
- `sql_regenerated_function_count=505`
- `canonical_helper_function_count=7`
- `canonical_data_class_count=96`
- `data_entity_count=99`
- `plain_data_candidate_count=63`
- `non_plain_data_candidate_count=36`
- `bootstrap_data_class_count=3`
- `legacy_delegate_function_count=0`

## broad lane で吸えた class

- `Project`
- `ProjectSourceOutput`
- `Req`
- `daCustomProxy`
- `daCustomProxyFunc`
- `dafunc`
- `dafuncinserttargetfields`
- `dafuncselecthaving`
- `dafuncselectwhere`
- `dafuncupdatedeletewhere`
- `dafuncupdatetargetfields`
- `htmlTemplateParameter`

## 残件

- latest manifest 上で remaining `data-*` は 3 class
  - `ProjectUser`
  - `SpecContent`
  - `htmlTemplate`
- それぞれの blocker は次の通り
  - `ProjectUser`
    - `BOTTOM` 内に trailing enum class が残る
  - `SpecContent`
    - method + top-level helper のみで、base 側へ送る trailing enum class が無い
  - `htmlTemplate`
    - `BOTTOM` 内に class 定義があり、top-level helper-only ではない
