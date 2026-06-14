# 2026-05-19 top-level declaration wrapper/base migration

## 目的

- `generated-layered-stub` に残っていた最後の `data-*` 3 class (`ProjectUser`, `SpecContent`, `htmlTemplate`) を wrapper/base へ吸収する。
- self-generated runtime bundle 入力でも promoted runtime reference 入力でも、`bootstrap_data_class_count=0` の full self-loop を通す。

## 実装

- `generated_catalog.php`
  - runtime data file の property 抽出を「analysis path 全体の `public $...`」ではなく「primary class の declared property」基準に変えた。
  - これにより `htmlTemplate` の `SortedhtmlTemplateDataContainer::$htmlTemplate` / `$ChildList` が DTO property として混入しなくなった。
- `legacy_data_class_editable_area_migrator.php`
  - legacy data class の `generated_property_names` も primary class 限定で抽出するようにした。
  - `htmlTemplate` の top-level declaration migration で helper support class の property が declared property set に混ざらないようにした。
- `project_output_runtime_generator.php`
  - `ProjectUserInOtherProjectEmailForDropboxSharing`
  - `ProjectUserInOtherProjectProjectPID`
  - 上記 2 property を `ProjectUser` の supplemental property として canonical raw property 側へ明示補完した。
  - legacy DTO 本体には残っているが canonical metadata / table schema には無い compatibility property として扱う。
- `project_output_service.php`
  - build-plan が `supports_legacy_top_level_declaration_migration` を upgrade 対象として扱うよう修正した。
  - これにより overlay 判定だけでなく final runtime bundle 出力でも `root wrapper + base/data-*Base.php` へ再配置されるようになった。

## regression

- `tests/Integration/SelfGeneratedRuntimeResolverTest.php`
  - layered `_base/data-Complex.php` に secondary class を混ぜても primary DTO property だけを読むことを確認するよう更新した。
- `tests/Integration/LegacyTopLevelDeclarationMigrationTest.php`
  - `htmlTemplate` migration で helper class property が `generated_property_names` に含まれないことを確認する assertion を追加した。
  - `ProjectUser` の promoted runtime reference が build-plan 上で passthrough ではなく wrapper/base 再出力対象になることを確認する test を追加した。

## ApacheHostSetting の扱い

- `ApacheHostSetting` / `ApacheHostSettingTemplate` は runtime/self-loop scope から引き続き明示除外とした。
- 用途は Apache config template 展開、log monitor snapshot、host assignment infra であり、current app runtime 自体の自己生成 bundle が依存する DTO / DBAccess ではない。

## 確認結果

- `make test`
  - `17 tests, 154 assertions`
- `make mtool-self-loop-check`
  - artifact `20260519-034316-924a01f2` で pass
  - `canonical_data_class_count=99`
  - `bootstrap_data_class_count=0`
  - warning `[]`
- promote
  - latest verified artifact `20260519-034316-924a01f2` を `php mtool/scripts/promote_runtime_reference.php --requested-by=codex --artifact-key=20260519-034316-924a01f2` で `mtool/reference/dbclasses/` へ promote
- expected snapshot
  - `mtool/reference/mtool-self-loop-expected-output.json` を `99/0` baseline へ更新した
  - `ProjectUser` / `SpecContent` / `htmlTemplate` の root/base digest を監視対象へ追加した

## 現在値

- `sql_regenerated_dbaccess_count=98`
- `sql_regenerated_function_count=505`
- `canonical_helper_function_count=7`
- `canonical_data_class_count=99`
- `data_entity_count=99`
- `plain_data_candidate_count=63`
- `non_plain_data_candidate_count=36`
- `bootstrap_data_class_count=0`
- `legacy_delegate_function_count=0`

## 追加メモ

- `ProjectUser` の compatibility property 2件は first-party code 上の実使用は見つからなかったが、legacy DTO surface を壊さないため declared property として維持した。
- final runtime reference から `_base/data-*.php` / `_wrappers/data-*.php` は消えたが、bootstrap reader と build-plan は historical self-generated bundle 入力として引き続き読める。
