# 2026-05-12 Mtool Project 1 Output Parity Plan

## Status

- parity objective: `DONE`
- status updated at: `2026-05-27`
- completion basis:
  - `project_source_outputs` `36/36`
  - build `36/36`
  - publish `36/36`
  - `scripts/check_mtool_project1_outputs.php`
- note:
  - この plan の parity 定義は達成済み。
  - bridge retirement や canonical replacement の残件は、別 plan の後続タスクとして扱う。

## Goal

- 暫定ゴールは、`MTOOL` scenario で旧 `Project 1 (Mtool)` の `36` output を current system 上で全部再現すること。
- ここでいう再現は、`project_source_outputs` row を持つだけではなく、`php scripts/create_project_output.php` から actual artifact 生成まで通ることを指す。
- sample/test 分離の方針は維持するが、優先順位は `Project 2+` より `MTOOL Project 1` parity を先に置く。
- `original-codes/` は調査元・複製元としてのみ扱い、new runtime / new generator が直接参照してはいけない。
- copied snapshot、placeholder、Tmp 編集は parity bridge の暫定対応であり、最終的には新Mtoolの自前 Output で丸ごと置き換える。
- ただし `LanguageResource` は immediate core canonical replacement の対象からいったん外し、migration 期間は bridge を維持した上で将来分離する。

## Definition Of Done

- `[DONE]` `MTOOL` の `project_source_outputs` が legacy `Project 1` と同数の `36` rows になる。
- `[DONE]` `create_project_output.php --project-key=MTOOL --source-output-key=...` が全 `36` rows で成功する。
- `[DONE]` 各 output について `generated/source-outputs/MTOOL/<artifact-key>/manifest.json` と archive が生成される。
- `[DONE]` 既存の `RUNTIME-DBCLASSES` / `DBIMPORT-PROXY-SERVER` / `DBIMPORT-PROXY-CLIENT` / `PAYPAL-PROXY-SERVER` / `UPLOADER-PROXY-SERVER` は壊さない。

## Current State

- artifact 生成まで通る bridge coverage は `36/36`。
- `published/source-outputs/MTOOL/*` への publish coverage も `36/36`。
- canonical / self-generated output として実装済みは `5/36`。
- 暫定 bridge の内訳は `html x21` と `LanguageResource x10`。
- `LanguageResource x10` は current parity 維持のため残すが、次段では canonical DB module 化より optional module 分離と AI-friendly replacement roadmap を優先する。
- current generator は `generated-bootstrap-dbclasses` / `custom-proxy-*` / `single-proxy-*` に加え、copy-tree bridge としての `html-module-catalog` dedicated generator を持つ。
- current app DB では `html` / `htmlParameter` / `htmlTemplate` live row を current HTML authoring route から read/write できる。一方で `LanguageResource` の canonical table はまだ無い。
- `html` は `html-module-catalog` strategy、`LanguageResource` は `legacy-directory-mirror` strategy で copied snapshot / placeholder から artifact 化できる bridge を入れた。
- `html` bridge は `catalog://html-module/MTOOL/{source_output_key}` ref へ寄せ、resolver が `canonical-html-module` / `legacy-html-snapshot` / `legacy-html-placeholder` を切り替える。
- `scripts/bootstrap_html_module_roots.php` により、copied snapshot-backed / placeholder-backed `html` module は `shared/reference/html-modules/mtool/*/current` へ bootstrap できる。
- 現時点の html catalog 解決結果は `canonical-html-module=21`、`legacy-html-snapshot=0`、`legacy-html-placeholder=0`。
- ただし `21` roots のうち `8` は placeholder 由来の scaffold であり、canonical generator への置換対象として残る。
- `html-module-catalog` の staging 実装は `shared/project_output_html_module_generator.php` へ分離済みで、`legacy-directory-mirror` は `LanguageResource` bridge 用の経路として残る。
- `scripts/show_html_db_rewrite_map.php` により、`HTML-DB/current` の `160` files は `18` clusters (`available=14`, `available-partial=116`, `planned=30`) に分類でき、未分類は `0`。
- `HTML-DB` の最初の actual rewrite slice は `source-outputs` cluster (`project_source_output*.php`, `6` files) を第 1 候補とする。
- `shared/project_output_html_module_generator.php` では、`MTOOL/HTML-DB` の `project_source_output.php`、`project_source_output_edit.php`、`project_source_output_change_order.php` を generated wrapper へ差し替え、existing row の update/delete POST、blank add-flow GET/POST、change-order reorder/reset POST も current route へ bridge できる。
- current route 側では `shared/project_source_output_new_page.php` / `shared/project_source_output_edit_page.php` / `shared/project_source_outputs_page.php` / `shared/project_source_output_change_order_page.php` が `bridge_errors` と delete success を表示でき、`shared/source_output_repository_pdo.php` には canonical delete support が入った。
- published `project_source_output_edit.php` / `project_source_output_change_order.php` の `_legacy` fallback は消えた。legacy add form に current 必須の `source_output_key` / `name` が無いため、blank add-flow は current `/new` handoff 上で manual confirmation 付きにしている。
- current `project_source_outputs` schema は canonical field subset を保持し、`ProgramLanguage` / `ClassType` / `ReleaseTargetType` / `SourceTemplateDir` / `SourceOutputDir` / `SourceTempOutputDir` / `ProxyBaseURL` / `AutoloadFilenameSuffix` / `SourceTextCharCode` を中心に扱う。`CustomFileExtention` などの legacy-only fields は別 slice に残す。
- `tables` cluster では `dbtables.php` / `dbtables_import.php` を current route redirect 化し、`dbtable_columns.php` / `dbtable_edit.php` / `dbtable_column_edit.php` は canonical `dbtable.PID -> name` map を使って current route へ寄せる。
- `dbtables.php` / `dbtable_columns.php` は unsupported verb / project mismatch / unknown PID でも `_legacy/` を require せず、nearest current table list / detail route へ縮退する。
- `dbtable_edit.php` / `dbtable_column_edit.php` は legacy POST/save を維持しつつ、invalid GET deep link は current table list / columns route へ縮退する。
- `scripts/export_legacy_dbtable_reference.php` で生成した `shared/reference/mtool-legacy-dbtable-catalog.json` を使うことで、current working session の live `config_app.dbtable` count が `0` でも self-host `DBTablePID` redirect map を維持できる。
- current route 側には `shared/project_table_edit_page.php` と `shared/project_table_column_edit_page.php` を追加し、canonical `dbtable` / `dbtablecolumns` の create/update/delete を current admin route で扱える。
- `project_tables_page.php` / `project_table_detail_page.php` / `project_table_columns_page.php` も `create canonical row` / `table edit` / `new column` / `column edit` の current route 導線を持つ。
- `data-classes` cluster では `dataclasses.php` / `dataclasses_sync.php` / `dataclass_fields.php` / `dataclass_edit.php` / `dataclass_field_edit.php` を current route redirect 化した。
- `dataclasses.php` / `dataclass_fields.php` は unsupported verb / project mismatch / unknown PID でも `_legacy/` を require せず、nearest current data-class list / detail route へ縮退する。
- `dataclass_edit.php` / `dataclass_field_edit.php` は blank add flow または legacy POST/save だけ `_legacy/` を残し、invalid GET deep link は current list / fields route へ縮退する。
- `scripts/export_legacy_dataclass_reference.php` で生成した `shared/reference/mtool-legacy-dataclass-catalog.json` を使うことで、current canonical `dataclass` row が空でも self-host `DataClassPID` redirect map を維持できる。
- current route 側には `shared/project_data_class_edit_page.php` と `shared/project_data_class_field_edit_page.php` を追加し、canonical `dataclass` / `dataclassfields` の create/update/delete を current admin route で扱える。
- `project_data_classes_page.php` / `project_data_class_detail_page.php` / `project_data_class_fields_page.php` も `create canonical row` / `data class edit` / `new field` / `field edit` の current route 導線を持つ。
- `db-access-core` cluster では `da.php` / `da_edit.php` / `da_funcs.php` / `da_funcs_change_order.php` / `da_source.php` / `da_sync.php` を current route redirect 化した。
- `scripts/export_legacy_db_access_reference.php` で生成した `shared/reference/mtool-legacy-db-access-catalog.json` を使うことで、runtime は copied reference だけで legacy `DAPID` / `DAFuncPID` deep link を current `db-access` route へ解決する。
- `da_edit.php` は mapped `DAPID` のときだけ current `/edit` へ、`da_funcs.php` は mapped `DAPID` を current `/functions` へ寄せ、known `filterdafuncPID` があれば current function detail へ redirect する。
- `da_funcs_change_order.php` と `da_sync.php` は preview-only GET のときだけ current route へ寄せ、legacy action parameter 付き request だけ `_legacy/` fallback を残す。
- `db-access-functions` cluster では `da_func_edit.php` / `da_func_move.php` / `da_func_source.php` / `da_func_endpoint.php` / `da_func_sort_order_edit.php` / `da_func_select_*` / `da_func_update_*` / `da_func_insert_*` を current `project_db_access_function*` route へ寄せる generated wrapper seam を追加した。
- `da_func_source.php` / `da_func_select_where.php` / `da_func_select_target_fields.php` / `da_func_select_having.php` / `da_func_update_delete_where.php` / `da_func_update_delete_where_input_aid.php` / `da_func_insert_target_fields.php` / `da_func_update_target_fields.php` は project mismatch / unknown `DAFuncPID` / unknown `DAPID` / unsupported verb でも nearest current function/list route へ縮退する。
- `da_func_move.php` / `da_func_sort_order_edit.php` と `da_func_*_edit.php` は legacy POST/save semantics を維持しつつ、invalid GET deep link は current move/detail/designer list route へ縮退する。
- `proxy-custom` cluster では `da_proxy_custom.php` / `da_proxy_custom_edit.php` / `da_proxy_custom_func.php` / `da_proxy_custom_func_change_order.php` / `da_proxy_custom_func_edit.php` を current `project_custom_proxy*` route へ寄せる generated wrapper seam を追加した。
- copied legacy `daCustomProxy.PID -> custom_proxy_key` bridge map により、custom proxy list / add / edit / functions deep link は current route へ寄る。
- `shared/project_single_proxy_page.php` と `shared/project_custom_proxy_endpoint_page.php` を追加し、`da_edit_proxy_single_target.php` / `da_funcs_edit_proxy_single*.php` / `da_proxy_custom_endpoint.php` の GET/HEAD preview は current `/proxy/single` / `/proxy/custom/{custom_proxy_key}/endpoint` へ handoff する generated wrapper に切り替えた。
- `da_funcs_edit_proxy_single_setting_edit.php` は legacy auth-only POST を current `project_db_access_function_detail_page.php` の bridge save へ変換する wrapper に切り替えた。unknown `DAFuncPID` POST は current single proxy page の bridge error に、unknown `SingleProxy_SingleGetFuncPID` は current function detail の bridge error に寄せ、blank auth type への clear も current save で扱える。
- `project_single_proxy_page.php` は current route で bulk target save を受けられるようになり、必要な function row が未作成なら preview default を使って最小限だけ canonical row を bootstrap してから target assignment を保存する。
- `da_funcs_edit_proxy_single_target.php` は legacy `IsTargetOfSimpleProxyWithProjectSourceOutputAndDAFuncPID[]` checkbox payload を current `source_output_keys_by_function[...]` へ変換して内部 dispatch する wrapper に切り替わり、single proxy bulk target POST も current 化した。unknown checkbox pair は legacy 同様に無視し、unknown `DAPID` POST は current validation へ寄せる。
- `da_proxy_custom_edit.php` は legacy create/update/delete POST を current `/projects/{project_key}/proxy/custom` list/detail save へ bridge する wrapper に切り替わり、`TargetProjectSourceOutputPIDList[]` の unknown PID は legacy 同様に無視する。unknown `SingleGetFuncPID` / `daCustomProxyPID` は current page の bridge error に寄せる。
- `da_proxy_custom_func_edit.php` / `da_proxy_custom_func_change_order.php` も legacy `daCustomProxyFunc.PID` bridge map を使って current functions page の step create/update/delete/reorder/reset action へ内部 dispatch できるようになった。unknown `daCustomProxyPID` は current list page の bridge error に、unknown step/order PID は current functions validation に寄せる。
- `da_proxy_custom_endpoint.php` の current preview は legacy `CUSTOMPROXYSERVER` semantics に合わせて custom proxy server target のみを候補にし、POST/action request と unknown PID だけ `_legacy/` fallback を維持する。
- `compare-output-settings` cluster では `compare_output.php` / `compare_output_edit.php` / `compare_output_additional_path.php` / `compare_output_additional_path_edit.php` を current `project_compare_output*` route へ寄せる generated wrapper seam を追加した。
- `compare_output.php` / `compare_output_additional_path.php` は project mismatch / unknown `CompareOutputPID` / unsupported verb でも current settings list へ縮退する。
- `compare_output_edit.php` / `compare_output_additional_path_edit.php` は legacy POST/save だけ `_legacy/` に残し、invalid GET deep link は current settings / additional-paths list へ縮退する。
- `CompareOutput.PID` / `CompareOutputAdditionalPath.PID` の copied bridge map により、settings list / add / edit / additional paths deep link は current route へ寄る。
- `compare-output-run` cluster では `compare_output_do.php` を current `/runs/compare-output/{project_key}` route へ寄せ、`compare_output_do_ajax.php` も known project request を current run flow へ handoff する wrapper に切り替えた。
- current `/runs/compare-output/{project_key}` / job detail / job api route は admin / lab の両 site で開けるようにし、published `HTML-DB` からの relative redirect を受けられるようにした。
- `build-project` cluster では current `/runs/builds/{project_key}` / job detail / job api route を追加し、published `HTML-DB` から `build_project*.php` handoff を受けられるようにした。
- `build_project_ajax_check_if_completed.php` は current build screen を指す JSON handoff を返す wrapper に切り替わり、legacy `BuildToken` polling を current file-based job manifest flow へ畳み込んだ。
- `endpoint-test` cluster では current `/runs/endpoints/{project_key}` / `/api/runs/endpoints/{job_key}` route を追加し、single-function proxy candidate または manual URL で JSON endpoint test を実行できるようにした。
- `endpoint_test_json_ajax.php` は GET/HEAD を current endpoint test route へ redirect する wrapper に切り替え、known-project POST も current endpoint test job service へ bridge する。`endpoint_common_include.php` / `endpoint_lib_include.php` / `endpoint_test_json_client_include.php` も current handoff shim に置き換わり、malformed `ProjectPID` / shared bootstrap missing guard では endpoint URL と短い request JSON を prefill した current handoff notice を返すようになった。
- `project_db_access_function_detail_page.php` も query string の `bridge_errors` を受けられるようにし、`da_funcs_edit_proxy_single_target.php` / `da_funcs_edit_proxy_single_setting_edit.php` / `da_proxy_custom_edit.php` / `da_proxy_custom_func_edit.php` / `da_proxy_custom_func_change_order.php` の shared-root missing / unsupported verb / malformed guard は current page への redirect で処理できるようにした。さらに `ProjectPID` mismatch も current handoff へ寄せ、published `HTML-DB` の proxy / endpoint wrappers から `_legacy` 参照が消えた。
- proxy current pages は query string の `bridge_errors` も表示できるようになり、`da_funcs_edit_proxy_single_setting_edit.php` / `da_proxy_custom_edit.php` / `da_proxy_custom_func_edit.php` / `da_proxy_custom_func_change_order.php` の unknown / missing legacy PID error path は shared root 経由の internal POST ではなく current page への redirect で処理できるようになった。
- current build job service smoke として `generated/build-jobs/MTOOL/20260512-065342-50e181ac/manifest.json` が生成され、selected `HTML-DB` 1 件で `successful_count=1`, `failed_count=0` を確認した。
- 最新の `HTML-DB` publish は `artifact_key=20260513-053623-0be8674b`, `source_file_count=229` で通っている。
- `scripts/check_mtool_project1_outputs.php` による container 内 loop build は `36/36 success`。
- `scripts/check_mtool_project1_outputs.php --publish` による create + publish も `36/36 success`。
- `compose.yaml` は `/var/www/published` と `/var/www/custom` を host bind mount する。
- `LanguageResource` の将来方針は [language-resource-separation.md](<repo-root>/docs/internal/language-resource-separation.md) を正本とする。

## Required Changes

1. `html` の canonical schema と actual generator を `config-initdb` / `artifact_strategy` に追加する。
2. `LanguageResource` は bridge 維持のまま module boundary と optional policy を固定する。
3. `LanguageResource` の将来置換は DB 中心 canonical schema を急がず、AI-friendly source of truth への移行設計を先に固める。
4. copied snapshot / placeholder / Tmp layer を対象 slice ごとに最終 generator または後継 source of truth へ段階的に吸収する。
5. `project_source_output_detail_page` と validation を strategy policy に追従させる。
6. `scripts/check_mtool_project1_outputs.php` を回帰チェックとして維持する。

## Implementation Order

1. bridge phase
   - `html` 21 rows を `html-module-catalog` で seed
   - copied snapshot-backed / placeholder-backed `html` module は `shared/reference/html-modules/mtool/*/current` へ bootstrap 済み
   - `LanguageResource` 10 rows を `legacy-directory-mirror` で seed
   - `36` rows 全件の loop build を通す
2. canonical replacement phase
   - `html` canonical schema / repository / generator を実装
   - copied snapshot / placeholder / Tmp layer を削る
3. language resource hold / separation phase
   - `LanguageResource` bridge を migration 期間の互換 layer として維持する
   - default core 非依存、optional module 化、sample/scenario 分離方針を固定する
   - AI-friendly replacement roadmap を正本化する
4. regression phase
   - `scripts/check_mtool_project1_outputs.php` を継続利用し、`36/36` を維持する

## Code Touchpoints

- `shared/domain_validation.php`
  - allowed `artifact_strategy`
  - caption
  - generation support
  - binding scope
- `shared/project_output_service.php`
  - generator dispatch
  - source tree staging
- 新規 generator 候補
  - `shared/project_output_html_generator.php`
  - `shared/project_output_language_resource_generator.php`
- `shared/project_source_output_detail_page.php`
  - strategy caption / build path 説明
- `docker/mariadb/config-initdb/`
  - `html` の canonical schema
- `docker/mariadb/config-sample-seed/mtool-core/`
  - `MTOOL` parity seed
- `shared/reference/legacy-source-snapshots/`
  - direct `original-codes` dependency を避けるための copied snapshot
- `shared/reference/legacy-source-placeholders/`
  - source 未回収 row の placeholder
- `scripts/`
  - all-output verification script

## Naming Policy

- `MTOOL` core 内の row は canonical key を使う。
- `html` 系は `HTML-*` prefix、`LanguageResource` 系は `LANGRES-*` prefix を第一候補にする。
- ただし duplicate target dir を持つ legacy row は、dir 名だけでは key を一意に決めず、legacy `PSO` をメモした上で suffix を付ける。
- copied snapshot は repo 内の curated path へ複製し、generator の入力はその curated path のみとする。

## Legacy Coverage Backlog

### Already Covered

| Legacy PSO | ClassType | Current key |
| --- | --- | --- |
| 1 | DBAccess | `RUNTIME-DBCLASSES` |
| 28 | DBaaSProxyServer | `PAYPAL-PROXY-SERVER` |
| 117 | DBaaSProxyServer | `UPLOADER-PROXY-SERVER` |
| 300 | DBaaSProxyServer | `DBIMPORT-PROXY-SERVER` |
| 301 | DBaaSProxyClient | `DBIMPORT-PROXY-CLIENT` |

### Bridge-Covered `html` Outputs

| Legacy PSO | ProgramLanguage | Legacy dir | Note |
| --- | --- | --- | --- |
| 13 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/db` | project db module |
| 14 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/chat` | chat module |
| 15 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/minutes` | minutes module |
| 16 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/req` | req module |
| 17 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/spec` | spec module |
| 18 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/test` | test module |
| 19 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/settings/uploader` | uploader settings |
| 20 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/settings/apache` | apache settings |
| 21 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/systemsettings/dropbox` | dropbox settings |
| 27 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/systemsettings/specialholiday` | special holiday |
| 31 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/settings/server` | server settings |
| 32 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/settings/dbuser` | db user settings |
| 33 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/settings/dbconnection` | db connection settings |
| 34 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/systemsettings/security` | security settings |
| 35 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/systemsettings/internaluser` | internal user settings |
| 36 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/settings` | settings top |
| 38 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/systemsettings/htmltemplate` | html template settings |
| 83 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/settings/dropbox` | dropbox settings alt |
| 84 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/systemsettings/apache` | apache system settings |
| 150 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/systemsettings/projectgroup` | project group settings |
| 356 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/settings/dbbackup` | db backup settings |

### Bridge-Covered `LanguageResource` Outputs

| Legacy PSO | ProgramLanguage | Legacy dir | Note |
| --- | --- | --- | --- |
| 265 | `php` | `/legacy/ftp/www/dev.matsuesoft.com/lib` | php runtime lib |
| 269 | `php` | `/Common Lib/mtool_lib` | common mtool lib |
| 274 | `php` | `/Common Lib/matsuesoft_lib` | common matsuesoft lib |
| 279 | `php` | `/legacy/ftp/www/ja.matsuesoft.com/lib` | ja web lib |
| 280 | `php` | `/legacy/ftp/www/www.matsuesoft.com/lib` | public web lib |
| 329 | `java` | `/Java Library/UTF-8 (For Android or so)/MatsuesoftCommonLib - Language Resource - tmp output` | java lib |
| 353 | `swift` | `/Swift Library - tmp output/Language Resource` | swift lib |
| 355 | `cs` | `/C# Library/Common Strings for UWP - tmp output` | csharp common strings |
| 361 | `cs` | `/C# Library/DegoDBCommonLib/Resources` | csharp dego resources |
| 369 | `php` | `/legacy/ftp/www/ja.matsuesoft.com/lib` | duplicate target dir with `PSO 279`; key policy 要検討 |

## Immediate Next Step

- `php scripts/show_html_db_rewrite_map.php` を使い、`HTML-DB/current` の route-backed cluster を基準に rewrite 順を固定する。
- `source-outputs` cluster では `project_source_output.php` / `project_source_output_edit.php` / `project_source_output_change_order.php` の current handoff が入り、existing row の update/delete POST、blank add-flow GET/POST、change-order reorder/reset POST も current route へ bridge 済みである。
- `project_source_output_new_page.php` を追加し、legacy add flow の current 受け皿を `/projects/{project_key}/source-outputs/new` に置いた。`project_source_outputs_page.php` / `project_source_output_edit_page.php` / `project_source_output_change_order_page.php` も wrapper 由来の `bridge_errors` と delete success を表示できる。
- published `project_source_output_edit.php` / `project_source_output_change_order.php` の `_legacy` fallback は解消した。legacy add-flow では safe な場合だけ tentative `source_output_key` / `name` suggestion を出す。次段の課題は legacy-only field 群と、duplicate / ambiguous case をどこまで current policy として補助できるかである。
- `tables` cluster では `dbtables.php` / `dbtables_import.php` / `dbtables_import_for_each.php` の entry rewrite まで current route へ吸収済みで、`dbtables_import_for_each.php` は pure preview を focused current import page へ寄せ、legacy action request だけ `_legacy/` fallback を残した。
- `tables` cluster では copied legacy dbtable catalog (`shared/reference/mtool-legacy-dbtable-catalog.json`) により self-host slice の `DBTablePID` redirect map も有効化した。`config_app.dbtable` が空でも `dbtable_columns.php` / `dbtable_edit.php` / `dbtable_column_edit.php` は mapped PID なら current route へ寄る。
- `dbtables.php` / `dbtable_columns.php` は unsupported verb / project mismatch / unknown PID でも current table list へ縮退するようになり、list/detail preview 系の `_legacy/` fallback は main path から外れた。
- `tables` cluster の current page 側では `table edit` / `column new` / `column edit` workflow まで current route 化できた。今後の実課題は canonical table metadata を実データで増やし、fallback preview を current canonical flow へ置き換えることに寄る。
- `dbtable_edit.php` は mapped table なら current edit route へ寄るようになり、`dbtable_column_edit.php` も `DBTableColumnPID` が空の add-flow は current `columns/new` へ吸収した。両者とも legacy POST/save は維持したまま invalid GET deep link を current list 側へ縮退させた。
- 残る `_legacy/` fallback は主に legacy POST/save semantics と `DBTableColumnPID -> current column` の 1:1 deep-link 不可ケースで、column-level PID bridge をどうするかは別課題として分離できた。
- `data-classes` cluster では copied legacy dataclass catalog (`shared/reference/mtool-legacy-dataclass-catalog.json`) により self-host slice の `DataClassPID` redirect map を current route に載せた。`dataclass_field_edit.php` は add-flow を current `fields/new` へ吸収し、legacy field PID を持つ deep link は field list へ寄せて `_legacy/` fallback を最小化した。
- `dataclasses.php` / `dataclass_fields.php` も unsupported verb / project mismatch / unknown PID で current list 側へ縮退するようになり、list/detail preview 系の `_legacy/` fallback は main path から外れた。
- `dataclass_edit.php` / `dataclass_field_edit.php` は blank add flow または legacy POST/save を残しつつ、invalid GET deep link は current list / fields route へ縮退する。
- `db-access-core` cluster では copied legacy db access catalog (`shared/reference/mtool-legacy-db-access-catalog.json`) により self-host slice の `DAPID` / `DAFuncPID` redirect map を current route に載せた。`da.php` / `da_edit.php` / `da_funcs.php` / `da_funcs_change_order.php` / `da_source.php` / `da_sync.php` は preview entry を current route へ吸収した。
- `db-access-functions` cluster では `da_func_edit.php` / `da_func_move.php` / `da_func_source.php` / `da_func_endpoint.php` / `da_func_sort_order_edit.php` / `da_func_select_*` / `da_func_update_*` / `da_func_insert_*` を current `project_db_access_function*` route へ寄せる generated wrapper seam を追加した。
- `da_func_source.php` / `da_func_select_where.php` / `da_func_select_target_fields.php` / `da_func_select_having.php` / `da_func_update_delete_where.php` / `da_func_update_delete_where_input_aid.php` / `da_func_insert_target_fields.php` / `da_func_update_target_fields.php` は project mismatch / unknown `DAFuncPID` / unknown `DAPID` / unsupported verb でも nearest current function/list route へ縮退するようになり、preview list/source wrapper の `_legacy/` fallback は main path から外れた。
- `da_func_move.php` / `da_func_sort_order_edit.php` は legacy POST/save を残しつつ、invalid GET deep link は current move/detail route または nearest current list route へ縮退する。
- `da_func_select_where_input_aid.php` は invalid GET deep link だけ current `input-aid` route へ寄せ、legacy filter / candidate selection state は `_legacy/` fallback を残す。
- `da_func_select_where_change_order.php` / `da_func_update_delete_where_change_order.php` は invalid GET deep link と unsupported verb を current change-order route へ寄せ、legacy `NewSortOrder` / `doReset` action は `_legacy/` fallback を残す。
- `da_func_*_edit.php` の item PID deep link は add-flow を current `/new` へ寄せ、existing item PID は current designer list へ寄せる pragmatic bridge とした。legacy POST/save を持つ edit wrapper は invalid GET deep link だけ current route に縮退し、item-level canonical mapping は別 slice に切り出せる。
- `proxy-custom` cluster のうち multi-step custom proxy entry page (`da_proxy_custom*.php`) は current `project_custom_proxy*` route へ寄せた。
- `da_proxy_custom_endpoint.php` と single-target proxy page (`da_edit_proxy_single_target.php`, `da_funcs_edit_proxy_single*.php`) の GET/HEAD preview も current `/projects/{project_key}/proxy/single` / `/projects/{project_key}/proxy/custom/{custom_proxy_key}/endpoint` へ handoff できるようになった。
- `project_source_output.php`, `da.php`, `da_source.php`, `compare_output_do.php`, `build_project.php` の redirect-only wrapper も project mismatch / unsupported verb を current list/run route へ畳み、GET preview entry 側の `_legacy/` 依存をさらに削った。
- `build_project_ajax.php`, `build_project_ajax_check_if_completed.php`, `compare_output_do_ajax.php` も project mismatch で legacy worker に戻らず、current notice / JSON handoff を返すようにした。
- 残る `_legacy/` fallback は主に POST/action semantics、interactive filter state、shared-root lookup failure、blank add flow のような non-currentizable guard に絞られた。
- `compare-output-settings` cluster (`compare_output*.php` の settings 側) も current `project_compare_output*` route へ寄せた。
- `compare_output.php` / `compare_output_additional_path.php` は project mismatch / unknown `CompareOutputPID` / unsupported verb でも current settings list へ縮退するようになり、`compare_output_edit.php` / `compare_output_additional_path_edit.php` も legacy POST/save 以外の GET mismatch は current list 側へ寄った。
- `compare-output-run` cluster も `compare_output_do.php` / `compare_output_do_ajax.php` の handoff までは current route へ寄せた。
- compare-output 実行系 asset は current admin compare-output settings で管理できるので、entry wrapper slice としては完了扱いにしてよい。
- `html-authoring` cluster も copied legacy html reference と live `html` / `htmlParameter` row を current `/projects/{project_key}/html*` route で扱える `available` 段階まで進んだ。`htmls.php` / `html_parameters.php` は current handoff、`html_edit.php` / `html_parameter_edit.php` の legacy POST は current save action へ bridge される。copied reference は既存 `html_key` 保持と template-parameter audit metadata に限定する。
- 次の deep-link rewrite 対象は canonical item mapping / malformed guard の整理と remaining non-currentizable fallback の縮小である。
- placeholder 由来で canonical root に入った `HTML-MINUTES` / `HTML-REQ` / `HTML-SETTINGS-UPLOADER` / `HTML-SETTINGS-SERVER` / `HTML-SETTINGS-DBUSER` / `HTML-SETTINGS-DBCONNECTION` / `HTML-SETTINGS-TOP` / `HTML-SETTINGS-DBBACKUP` をどこから canonical `html` schema または後継 source of truth へ吸収するかを切る。
- `shared/reference/html-modules/mtool/*/current` を canonical 編集点として使い始め、`HTML-DB` から bridge snapshot 依存を段階的に薄くする。
- `LanguageResource` bridge 10 rows は維持しつつ、optional module 化と AI-friendly replacement roadmap を先に固定する。
- `scripts/check_mtool_project1_outputs.php` を回し続け、`html` canonical 化後も `36/36` を維持する。
