# HTML-DB Rewrite Map / HTML-DB rewrite map

English companion:
This map groups the remaining `HTML-DB` rewrite work into clusters and seams. Use it to decide where legacy `HTML-DB` files should land in the current app routes, and which slices are safe to rewrite first.

## 目的

- `mtool/reference/html-modules/mtool/HTML-DB/current` に残っている legacy `HTML-DB` files を、current `mtool/app/*_page.php` と planned route 群へ対応づける。
- `HTML-DB` の rewrite を「どこから actual-generated へ置き換えるか」を cluster 単位で判断できるようにする。
- copied snapshot / placeholder から canonical source root へ移した後も、`original-codes/` を直接参照せずに rewrite を進める。

## 使い方

対応表の現状は次で再生成する。

```zsh
php mtool/scripts/show_html_db_rewrite_map.php
php mtool/scripts/show_html_db_rewrite_map.php --format=json
```

## Rewrite Clusters

| Cluster | Legacy scope | Current / planned landing |
| --- | --- | --- |
| `project-detail` | `index.php` | current `project_detail_page.php` |
| `project-settings` | `project_edit*.php` | current `project_settings_page.php` |
| `tables` | `dbtables*.php`, `dbtable_*.php`, `dbtable_columns.php` | current `project_tables*`, `project_table*` |
| `data-classes` | `dataclasses*.php`, `dataclass_*.php`, `dataclass_fields*.php` | current `project_data_classes*`, `project_data_class*` |
| `db-access-core` | `da.php`, `da_edit.php`, `da_funcs*.php`, `da_source.php`, `da_sync.php` | current `project_db_access*` |
| `db-access-functions` | `da_func*.php` | current `project_db_access_function*` |
| `proxy-custom` | `da_proxy_custom*.php`, `da_edit_proxy_single_target.php`, `da_funcs_edit_proxy_single*.php` | current `project_custom_proxy*` + `/projects/{project_key}/proxy/single` + `/projects/{project_key}/proxy/custom/{custom_proxy_key}/endpoint` |
| `source-outputs` | `project_source_output*.php` | current `project_source_output*` |
| `compare-output-settings` | `compare_output*.php` settings side + template assets | current `project_compare_output*` |
| `compare-output-run` | `compare_output_do*.php` | current `lab_compare_output*` |
| `build-project` | `build_project*.php` | current `lab_build*` |
| `endpoint-test` | `endpoint_*.php` | current `lab_endpoint*` (`available-partial`) |
| `html-authoring` | `html*.php` | current `/projects/{project_key}/html*` (`available`) |
| `language-resource` | `lang_res*.php` | planned optional module / code-native replacement |
| `security-host` | `project_security*.php`, `project_host_assignment*.php` | planned `/projects/{project_key}/security` |
| `default-settings` | `default_setting*.php` | planned asset policy |
| `misc-helpers` | `create_project_group.php`, `source_comment_include.php`, `update_history.php` | ownership 未確定 |

## 現時点の判断

- `current route` が既にある cluster は、legacy file 全体を 1:1 再現するより、entry page を current route / current page composition へ寄せる方針で進める。
- `include` / `table_include` / `change_order_include` のような helper file は、current page 側に吸収するまで bridge を残してよい。
- `security-host` と `language-resource` は route 自体が未整備なので、先に current route を作る。
- `html-authoring` は live DB-backed current route へ移し、copied legacy reference は `html_key` 保持と parameter audit metadata に限定する。
- `endpoint-test` は current route と `endpoint_test_json_ajax.php` の known-project POST bridge まで入り、`endpoint_common_include.php` / `endpoint_lib_include.php` / `endpoint_test_json_client_include.php` も current handoff shim へ置き換わった。さらに `endpoint_test_json_ajax.php` の malformed `ProjectPID` / shared bootstrap missing guard も current handoff notice に置き換わり、この cluster の main path における `_legacy/` fallback はほぼ解消した。

## 最初の Actual Rewrite Slice

- 第 1 候補は `source-outputs` cluster。
- 理由は、current `project_source_outputs_page.php`、`project_source_output_detail_page.php`、`project_source_output_edit_page.php` が既に揃っていて、legacy scope も比較的小さいため。
- `project_source_output.php` は current list route へ redirect し、`project_source_output_edit.php` は known existing row の update/delete POST を current `/edit` action へ bridge、blank add-flow GET/POST も current `/new` handoff へ切り替わった。`project_source_output_change_order.php` は reorder/reset POST を current `/change-order` へ bridge する。
- `project_source_outputs_page.php`、`project_source_output_edit_page.php`、`project_source_output_change_order_page.php` は wrapper 由来の `bridge_errors` と delete success を受けられるようにし、unknown PID / mismatch error path も current page 側へ寄せた。

## 実装済みの最初の Seam

- `mtool/app/project_output_html_module_generator.php` は `MTOOL/HTML-DB` の staging 時に `project_source_output.php`、`project_source_output_edit.php`、`project_source_output_change_order.php` を generated wrapper へ差し替える。
- 退避した legacy 実装は artifact / published output の `_legacy/` 配下へ複製される。
- `mtool/app/project_source_output_route_common.php` は query / POST の `bridge_errors` を current page で吸い上げる helper を持ち、`project_source_outputs_page.php` / `project_source_output_edit_page.php` / `project_source_output_change_order_page.php` が wrapper 側 validation/error をそのまま表示できる。
- `mtool/app/project_source_output_new_page.php` を追加し、advanced create form と legacy add-flow handoff の current 受け皿を `/projects/{project_key}/source-outputs/new` に置いた。
- `mtool/app/source_output_repository.php` / `mtool/app/source_output_repository_pdo.php` には delete support を追加し、current `/edit` page から canonical definition の update/delete を完了できるようにした。
- `project_source_output.php` は `/projects/{project_key}/source-outputs` へ、`project_source_output_edit.php` は legacy `ProjectSourceOutputPID` を current `source_output_key` へ解決して `/edit` へ、blank add-flow は `/source-outputs/new` へ、`project_source_output_change_order.php` は `/source-outputs/change-order` へ handoff する。
- published `project_source_output_edit.php` / `project_source_output_change_order.php` から `_legacy/` fallback は消えた。一方で current schema が canonical field subset だけを保持しているため、legacy-only fields は引き続き current handoff 上の注意事項として残る。proxy strategy / binding は `ClassType` / `ProxyBaseURL` / mapped target server source output から current `/new` page で初期推定し、key / name は legacy dir / URL を `safe-prefill` / `warning-candidate` / `manual-only` に分類して扱う。
- `tables` cluster では `dbtables.php` と `dbtables_import.php` を current route redirect 化した。
- `dbtables_import_for_each.php` は pure GET preview のときだけ `/projects/{project_key}/tables/import?table={TableName}` へ redirect し、`DoImport` / `DoImportAll` / `FieldName` / `IncludeOrder` 付きの legacy action は `_legacy/` fallback を残す。
- current `project_tables_import_page.php` / `project_table_import_service.php` / `scripts/import_project_tables.php` は focused table scope (`table=` / `--table=`) を受け取り、1 table 単位の preview / apply を current route 側でも扱える。
- `mtool/scripts/export_legacy_dbtable_reference.php --host-side` で host-side `original-codes/mtool.sql` dump から `mtool/reference/mtool-legacy-dbtable-catalog.json` を生成し、runtime は copied reference だけを使って legacy `DBTablePID -> current table name` の self-host slice map を引く。
- current route 側には `project_table_edit_page.php` と `project_table_column_edit_page.php` を追加し、`/projects/{project_key}/tables/{table}/edit` と `/projects/{project_key}/tables/{table}/columns/{column}/edit|new` で canonical `dbtable` / `dbtablecolumns` を直接編集できるようにした。
- `project_tables_page.php` は canonical tables と bootstrap-only candidates を混在表示し、fallback row から `create canonical row` へ進める。
- `project_table_detail_page.php` / `project_table_columns_page.php` も `table edit` / `new column` / `column edit` の current route 導線を持つようにした。
- `dbtables.php` と `dbtable_columns.php` は copied legacy dbtable catalog を使って current list/detail/columns route へ寄せ、unsupported verb / project mismatch / unknown target も current table list へ縮退する。
- `dbtable_edit.php` は current table edit route へ redirect し、legacy POST/save を `_legacy/` に残したまま invalid GET deep link は current table list へ縮退する。
- `dbtable_column_edit.php` は `DBTableColumnPID` が空なら current `columns/new` へ、legacy column PID があるときは current columns page へ redirect し、legacy POST/save を `_legacy/` に残したまま invalid GET deep link は current table list へ縮退する。
- current working session の live `config_app.dbtable` count はまだ `0` だが、self-host `DBTablePID` bridge は canonical `dbtable` row の有無に依存しない copied reference ベースへ切り替わった。
- `data-classes` cluster では `dataclasses.php` / `dataclasses_sync.php` / `dataclass_fields.php` / `dataclass_edit.php` / `dataclass_field_edit.php` を current route redirect 化した。
- `mtool/scripts/export_legacy_dataclass_reference.php --host-side` で host-side `original-codes/mtool.sql` dump から `mtool/reference/mtool-legacy-dataclass-catalog.json` を生成し、runtime は copied reference だけを使って legacy `DataClassPID -> current data class name` の self-host slice map を引く。
- current route 側には `project_data_class_edit_page.php` と `project_data_class_field_edit_page.php` を追加し、`/projects/{project_key}/data-classes/{data_class}/edit` と `/projects/{project_key}/data-classes/{data_class}/fields/{field}/edit|new` で canonical `dataclass` / `dataclassfields` を直接編集できるようにした。
- `project_data_classes_page.php` / `project_data_class_detail_page.php` / `project_data_class_fields_page.php` も `create canonical row` / `data class edit` / `new field` / `field edit` の current route 導線を持つようにした。
- `dataclasses.php` と `dataclass_fields.php` は copied legacy dataclass catalog を使って current list/detail/fields route へ寄せ、unsupported verb / project mismatch / unknown target も current data-class list へ縮退する。
- `dataclass_edit.php` は blank add flow と POST/save を `_legacy/` に残しつつ、invalid GET deep link は current data-class list へ縮退する。
- `dataclass_field_edit.php` は `DataClassFieldPID` が空なら current `fields/new` へ、legacy field PID があるときは current fields page へ redirect し、legacy POST/save を `_legacy/` に残したまま invalid GET deep link は current data-class list へ縮退する。
- `db-access-core` cluster では `da.php` / `da_edit.php` / `da_funcs.php` / `da_funcs_change_order.php` / `da_source.php` / `da_sync.php` を current route redirect 化した。
- `mtool/scripts/export_legacy_db_access_reference.php --host-side` で host-side `original-codes/mtool.sql` dump から `mtool/reference/mtool-legacy-db-access-catalog.json` を生成し、runtime は copied reference だけを使って legacy `DAPID` / `DAFuncPID` の self-host slice map を引く。
- `da.php` は `/projects/{project_key}/db-access` へ、`da_edit.php` は mapped `DAPID` のときだけ current `/edit` へ、`da_funcs.php` は mapped `DAPID` を current `/functions` へ寄せ、known `filterdafuncPID` があれば current function detail へ redirect する。
- `da.php` と `da_source.php` の redirect-only guard も current side に寄せ、project mismatch / unsupported verb / unknown target は current list または nearest current source route へ縮退するようにした。
- `da_funcs_change_order.php` と `da_sync.php` は preview-only GET のときだけ current route へ寄せ、legacy action parameter (`NewSortOrder`, `doReset`, `DoSync*`, `DataClassName`) が付く request は `_legacy/` fallback を残す。
- `da_source.php` は legacy `PID` を copied reference で current `db-access/{db_access_key}/source` に写し、unknown PID も current db-access list route へ縮退する。
- `db-access-functions` cluster では `da_func_edit.php` / `da_func_move.php` / `da_func_source.php` / `da_func_endpoint.php` / `da_func_sort_order_edit.php` / `da_func_select_*` / `da_func_update_*` / `da_func_insert_*` を current `project_db_access_function*` route へ寄せる generated wrapper seam を追加した。
- `da_func_source.php` / `da_func_select_where.php` / `da_func_select_target_fields.php` / `da_func_select_having.php` / `da_func_update_delete_where.php` / `da_func_update_delete_where_input_aid.php` / `da_func_insert_target_fields.php` / `da_func_update_target_fields.php` は project mismatch / unknown `DAFuncPID` / unknown `DAPID` / unsupported verb でも nearest current function/list route へ縮退するようになり、preview list/source wrapper の `_legacy/` fallback は main path から外れた。
- `da_func_move.php` / `da_func_sort_order_edit.php` は legacy POST/save semantics を残しつつ、invalid GET deep link は current move/detail route または nearest current list route へ縮退する。
- `da_func_select_where_input_aid.php` / `da_func_select_where_change_order.php` / `da_func_update_delete_where_change_order.php` は invalid GET と unsupported verb を current route へ寄せ、interactive filter state や `NewSortOrder` / `doReset` などの action request だけ `_legacy/` fallback を残す。
- current canonical key と 1:1 対応しない `da_func_*_edit.php` の item PID deep link は、add-flow を current `/new` へ、既存 item PID 付き deep link は current designer list へ寄せる pragmatic bridge とした。legacy POST/save と item-level canonical mapping 不在は別 slice に残す。
- `da_func_edit.php` は blank add flow がまだ current route key を確定できないため、known edit deep link の current detail redirect だけを吸収し、add-flow 自体は `_legacy/` fallback を維持する。
- `proxy-custom` cluster では `da_proxy_custom.php` / `da_proxy_custom_edit.php` / `da_proxy_custom_func.php` / `da_proxy_custom_func_change_order.php` / `da_proxy_custom_func_edit.php` を current `project_custom_proxy*` route へ寄せる generated wrapper seam を追加した。
- copied legacy `daCustomProxy.PID -> custom_proxy_key` bridge map を使い、`da_proxy_custom_edit.php` は add-flow を current list page へ、existing custom proxy edit deep link を current detail route へ寄せるだけでなく、legacy create/update/delete POST も current list/detail save へ bridge する。`TargetProjectSourceOutputPIDList[]` の unknown PID は legacy 同様に無視し、`SingleGetFuncPID` が current `function_name` に解決できないときは current page 側の bridge error で保存を止める。
- `da_proxy_custom_func.php` / `da_proxy_custom_func_edit.php` / `da_proxy_custom_func_change_order.php` は current functions page へ寄せ、legacy `daCustomProxyFunc.PID` bridge map を使って step create/update/delete/reorder/reset POST も current action へ変換する。unknown `daCustomProxyPID` POST は current list page で bridge error を出し、unknown step/order PID は current functions page の validation/not-found に寄せる。
- GET-only preview wrapper (`da_proxy_custom.php`, `da_proxy_custom_func.php`, `da_edit_proxy_single_target.php`, `da_funcs_edit_proxy_single_setting.php`) は unsupported verb / project mismatch でも `_legacy/` へ戻さず、nearest current list / functions route へ縮退するようにした。
- `da_proxy_custom_endpoint.php` は current `/projects/{project_key}/proxy/custom/{custom_proxy_key}/endpoint` へ handoff する generated wrapper に切り替え、legacy `CUSTOMPROXYSERVER` semantics に合わせて custom proxy server target のみを preview 対象にする。
- `da_edit_proxy_single_target.php` / `da_funcs_edit_proxy_single*.php` も current `/projects/{project_key}/proxy/single` へ handoff する generated wrapper に切り替えた。`da_funcs_edit_proxy_single_setting_edit.php` は legacy auth-only POST を current function detail の bridge save へ変換し、unknown `DAFuncPID` POST は current single proxy page の bridge error に、unknown `SingleProxy_SingleGetFuncPID` は current function detail の bridge error に寄せる。さらに `da_funcs_edit_proxy_single_target.php` の legacy bulk target POST も current `project_single_proxy_page.php` の bulk save へ変換でき、unknown checkbox pair は legacy 同様に無視し、GET/HEAD の unknown PID deep link は current list へ吸収するようになった。
- current proxy pages (`project_single_proxy_page.php`, `project_custom_proxies_page.php`, `project_custom_proxy_detail_page.php`, `project_custom_proxy_functions_page.php`) は POST だけでなく query string の `bridge_errors` も表示できるようにした。
- その結果、single/custom proxy wrapper の unknown / missing legacy PID error path は、`sharedRoot` が解決できない環境でも current list/detail/functions page へ redirect して error を表示できるようになり、error-only fallback の `_legacy/` 依存をさらに減らした。
- `compare-output-settings` cluster では `compare_output.php` / `compare_output_edit.php` / `compare_output_additional_path.php` / `compare_output_additional_path_edit.php` を current `project_compare_output*` route へ寄せる generated wrapper seam を追加した。
- copied legacy `CompareOutput.PID -> compare_output_key` と `CompareOutputAdditionalPath.PID -> additional_path_key` bridge map を使い、settings list / add / edit / additional paths deep link を current route へ寄せる。
- `compare_output.php` と `compare_output_additional_path.php` は project mismatch / unknown `CompareOutputPID` / unsupported verb でも current settings list へ縮退するようになった。
- `compare_output_edit.php` と `compare_output_additional_path_edit.php` は legacy POST/save を `_legacy/` に残しつつ、invalid GET deep link や mismatched additional-path binding は current settings/additional-paths list へ縮退する。
- `compare_output_template_for_*.txt` と `compare_ignore_dir_setting_regex.txt` など compare-output 実行系 asset は、`compare-output-run` slice に入るまで現状維持とする。
- `compare-output-run` cluster では `compare_output_do.php` を current `/runs/compare-output/{project_key}` route へ寄せる generated wrapper seam を追加した。
- `compare_output_do_ajax.php` も known project request については current `/runs/compare-output/{project_key}` への handoff に寄せ、GET/HEAD は redirect、POST は current run screen への移行案内 HTML を返す wrapper へ置き換えた。
- `build-project` cluster では current `/runs/builds/{project_key}` / `/runs/builds/{job_key}` / `/api/runs/builds/{job_key}` route を追加し、selected `ProjectSourceOutput` definition を `generate + write output` する file-based build job UI / API を入れた。
- `build_project.php` / `build_project_for_each.php` は current build run route へ redirect し、`build_project_ajax.php` / `build_project_ajax_check_if_completed.php` は current build screen への handoff notice / JSON を返す wrapper へ置き換えた。
- `compare_output_do.php` と `build_project.php` の redirect-only guard も current side に寄せ、project mismatch / unsupported verb は current run route へ縮退するようにした。
- `compare_output_do_ajax.php` と `build_project_ajax*.php` も project mismatch で legacy worker に戻らず、current notice / JSON handoff を返すようにした。

## 次の順番

entry wrapper seam としては `source-outputs` -> `tables` -> `data-classes` -> `db-access-core` -> `db-access-functions` -> `proxy-custom` -> `compare-output-settings` -> `compare-output-run` -> `build-project` -> `endpoint-test` -> `html-authoring` まで current route handoff を入れた。

`source-outputs` cluster では current list/detail/new/edit/change-order page が bridge error と delete success を受けられるようになり、existing edit update/delete POST、blank add-flow GET/POST、change-order reorder/reset POST も current route へ bridge された。published `project_source_output_edit.php` / `project_source_output_change_order.php` から `_legacy` fallback は消えた。legacy add-flow の proxy strategy / binding は current `/new` page で初期推定し、key / name は `safe-prefill` / `warning-candidate` / `manual-only` に分けて扱う。ただし legacy-only fields は未移植のため、この cluster はまだ `available-partial` のままにしている。

`proxy-custom` cluster は GET/HEAD preview に加え custom proxy POST/action semantics まで current route へ寄せられ、single-function auth edit POST、single proxy bulk target POST、`endpoint_test_json_ajax.php` known-project POST も current bridge へ吸収できた。さらに endpoint helper include 群も current handoff shim へ切り替わり、unknown / missing legacy PID の error path だけでなく proxy save/reorder の shared-root missing / unsupported verb / malformed guard も query `bridge_errors` redirect や current handoff で current page 側へ寄せられるようになった。published `HTML-DB` の proxy / endpoint wrappers から `_legacy` 参照は外れた。`html-authoring` cluster も live `html` / `htmlParameter` row を current `/projects/{project_key}/html*` route で read/write できる `available` 段階へ進み、`htmls.php` / `html_parameters.php` は current handoff、`html_edit.php` / `html_parameter_edit.php` の legacy POST も current action に bridge される。copied reference (`mtool/reference/mtool-legacy-html-catalog.json`) は既存 `html_key` 保持と template-parameter audit metadata に限定した。compare-output / build 実行系では、legacy AJAX batch / BuildToken semantics を current project-scoped run flow と file-based job manifest へ統合した。

1. canonical item mapping / malformed guard 整理
2. remaining non-currentizable fallback の縮小
3. `language-resource` separation / optional module policy

この順だと、`project detail -> tables -> data classes -> db access -> source output` の canonical flow に沿って rewrite を積み上げられる。
