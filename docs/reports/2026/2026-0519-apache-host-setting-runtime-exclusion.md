# ApacheHostSetting Runtime Exclusion

## later update

- この時点では self-loop mismatch が残っていたが、同日中に `LanguageResource*` designer child row の backfill を入れて canonical overlay mismatch を解消した。
- その後 full self-loop は pass し、`mtool/reference/dbclasses/` の promote と default switch も完了した。
- 最新の到達点は `docs/reports/2026/2026-0519-self-generated-runtime-reference-promotion.md` を参照。

## 目的

- self-generated runtime bundle / self-loop から `ApacheHostSetting` / `ApacheHostSettingTemplate` を明確に対象外にする。
- host assignment は引き続き `project_host_assignments` の denormalized row で保持し、Apache VirtualHost template 出力専用の legacy class を current runtime artifact へ持ち込まない。

## legacy usage の確認結果

- `original-codes/mtool_lib/dbclasses/data-ApacheHostSetting.php` の `InitializeByTemplate()` は Apache config filename / access log / error log / template body を組み立てる用途だった。
- `original-codes/mtool_lib/lib_mtool_apache.php` は `ApacheHostSetting` / `ApacheHostSettingTemplate` を使って Apache 設定ファイル出力と log watch 向けの path 解決を行っていた。
- current core API / source-output truth に必要な host assignment 情報は `ProjectHostSetting` visible fields を denormalize した `project_host_assignments` に残しており、Apache template 自体は runtime/self-loop の入力に不要と判断してよい。

## 実装

- `mtool/app/project_output_runtime_generator.php`
  - `ApacheHostSetting` / `ApacheHostSettingTemplate` を explicit excluded source name として定義。
  - root `data-*.php` / `dbaccess-*.php` の entity 列挙から除外。
  - `base/`, `_base/`, `_wrappers/`, `_support/legacy-dbaccess/` の companion file を stage copy 前に除外。
  - `autoload_mtool.php` から該当 include 行を削除して、欠落 file を autoload しないようにした。
- `mtool/app/project_output_service.php`
  - layered bundle build plan 側でも同じ除外判定を通し、artifact build 時の passthrough / layered file へ Apache 系が入らないようにした。
- `tests/Integration/SelfGeneratedRuntimeResolverTest.php`
  - Apache 系 source が entity catalog と runtime file filter から落ちること、`autoload_mtool.php` から include 行が消えることを固定。

## 検証

- `make test`
  - `8 tests, 102 assertions` で通過。
- full self-loop
  - 実行コマンド:
    - `APP_DB_HOST=127.0.0.1 APP_DB_PORT=33061 APP_DB_NAME=config_app APP_DB_USER=config_app APP_DB_PASSWORD=config_app_local_2026 APP_CONFIG_DB_HOST=127.0.0.1 APP_CONFIG_DB_PORT=33061 APP_CONFIG_DB_NAME=config_app APP_CONFIG_DB_USER=config_app APP_CONFIG_DB_PASSWORD=config_app_local_2026 APP_REFERENCE_ROOT=work/artifacts/source-outputs/MTOOL/20260518-071523-06754a82/bundle/mtool-source-output-runtime-dbclasses-20260518-071523-06754a82/mtool php mtool/scripts/check_mtool_self_loop.php --requested-by=self-generated-bootstrap-check`
  - 生成 artifact:
    - `work/artifacts/source-outputs/MTOOL/20260519-002006-21aff31f/`
  - 確認結果:
    - new artifact 配下に `ApacheHostSetting` / `ApacheHostSettingTemplate` file は残らない。
    - `autoload_mtool.php` にも両 source の include 行は残らない。

## 残課題

- self-loop 自体はまだ expected baseline mismatch で失敗する。
- 今回の artifact summary は以下:
  - `generated_dbaccess_count=99`
  - `canonical_function_count=611`
  - `sql_regenerated_dbaccess_count=54`
  - `sql_regenerated_function_count=71`
  - `canonical_data_class_count=48`
  - `data_entity_count=99`
  - `plain_data_candidate_count=63`
  - `non_plain_data_candidate_count=36`
  - `bootstrap_data_class_count=51`
  - `legacy_delegate_function_count=434`
  - warning 29 件
- digest mismatch は引き続き `base/dbaccess-ProjectBase.php` と `base/dbaccess-dbtableBase.php`。
- durable reference への promote と default switch は、上記 canonical overlay mismatch を解消してから進める。
