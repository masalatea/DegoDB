# 2026-05-12 Seed Separation Progress

## Summary

- default `config_app` seed を `MTOOL` core のみへ寄せた
- optional sample/test seed を `docker/mariadb/config-sample-seed/` へ分離した
- fresh volume で `docker compose up -d` をやり直し、default state を確認した
- fresh initdb で顕在化した `016_proxy_source_output_seed.sql` の column count mismatch も修正した
- `MTOOL` 自体を scenario 1 と見なし、sample/test を scenario 2 以降として重ねる compose override 方針を追加した
- 暫定ゴールを `MTOOL` の旧 `Project 1` 36 output parity へ切り替えた
- parity bridge では `original-codes/` を new runtime から直接読まず、copied snapshot / placeholder だけを参照する方針へ修正した
- `LanguageResource` x10 も `legacy-directory-mirror` bridge へ載せ、`MTOOL` source output catalog を `36` rows まで拡張した
- `scripts/check_mtool_project1_outputs.php` を追加し、container 内 loop build で `36/36` success を確認した
- `published/source-outputs/` を Mtool-only output の昇格先として固定し、`custom/source-outputs/` と分離した
- `compose.yaml` に `published/` と `custom/` の bind mount を追加し、container 内 publish が host workspace に反映される状態へ修正した
- `create_project_output.php --publish`、`publish_project_output.php`、`check_mtool_project1_outputs.php --publish` を追加し、artifact 生成後に raw output を `published/` へ昇格できるようにした
- `MTOOL` の `36` outputs を `published/source-outputs/MTOOL/*` へ一括 publish した
- `html` bridge では `source_template_dir` に実 path ではなく `catalog://html-module/MTOOL/{source_output_key}` を入れ、resolver が canonical html module / copied snapshot / placeholder を順に解決する形へ変更した
- `scripts/bootstrap_html_module_roots.php` を追加し、copied snapshot-backed `html` 12 modules を `shared/reference/html-modules/mtool/*/current` へ bootstrap した
- `scripts/bootstrap_html_module_roots.php` を fallback source 対応へ広げ、placeholder-backed `html` 8 modules も `shared/reference/html-modules/mtool/*/current` へ bootstrap した
- html catalog resolver は `canonical-html-module=21` になり、`legacy-html-snapshot` / `legacy-html-placeholder` fallback はともに `0` になった
- `html` 21 rows の `artifact_strategy` を `html-module-catalog` として正式化し、live `config_app` も同値へ更新した
- `shared/project_output_html_module_generator.php` を追加し、`html-module-catalog` の staging 実装を `legacy-directory-mirror` から分離した
- `scripts/show_html_db_rewrite_map.php` と `docs/internal/html-db-rewrite-map.md` を追加し、`HTML-DB/current` の `160` files を current route-backed clusters へ棚卸しできるようにした
- `HTML-DB` の `project_source_output.php` / `project_source_output_edit.php` は generator staging 時に generated wrapper + `_legacy/` fallback へ切り替え、最初の actual rewrite seam を入れた
- `project_source_output_change_order.php` も current `source-outputs/change-order` route へ吸収し、`source-outputs` cluster の entry page を 3 本 current route redirect 化した
- `tables` cluster では `dbtables.php` / `dbtables_import.php` を current route redirect 化し、`dbtable_columns.php` / `dbtable_edit.php` / `dbtable_column_edit.php` は canonical table PID map があるときだけ current route へ寄せる wrapper を追加した
- `tables` cluster の current admin 側にも `table edit` / `column new/edit` workflow を追加し、bootstrap-only table から `create canonical row` へ進めるようにした
- `data-classes` cluster では `dataclasses.php` / `dataclasses_sync.php` / `dataclass_fields.php` / `dataclass_edit.php` / `dataclass_field_edit.php` を current route redirect 化し、current admin 側にも `data class edit` / `field new/edit` workflow を追加した
- `db-access-core` cluster では `da.php` / `da_edit.php` / `da_funcs.php` / `da_funcs_change_order.php` / `da_source.php` / `da_sync.php` を current route redirect 化し、legacy `DAPID` / `DAFuncPID` deep link を copied reference 経由で current `project_db_access*` route へ寄せるようにした
- `db-access-functions` cluster では `da_func_edit.php` / `da_func_move.php` / `da_func_source.php` / `da_func_endpoint.php` / `da_func_sort_order_edit.php` / `da_func_select_*` / `da_func_update_*` / `da_func_insert_*` を current `project_db_access_function*` route へ寄せ、item-level deep link は `/new` または designer list へ段階的に吸収した
- `proxy-custom` cluster では `da_proxy_custom.php` / `da_proxy_custom_edit.php` / `da_proxy_custom_func.php` / `da_proxy_custom_func_change_order.php` / `da_proxy_custom_func_edit.php` を current `project_custom_proxy*` route へ寄せ、`da_proxy_custom_endpoint.php` と single-target proxy page も current `/proxy/custom/{custom_proxy_key}/endpoint` / `/proxy/single` handoff wrapper へ切り替えた
- `compare-output-settings` cluster では `compare_output.php` / `compare_output_edit.php` / `compare_output_additional_path.php` / `compare_output_additional_path_edit.php` を current `project_compare_output*` route へ寄せ、compare-output settings 側の deep link を current route へ吸収した
- `compare-output-run` cluster では `compare_output_do.php` を current `/runs/compare-output/{project_key}` route へ寄せ、`compare_output_do_ajax.php` も known project request を current run flow へ handoff する wrapper に切り替えた
- current `/runs/compare-output/{project_key}` / job detail / job api route は admin / lab の両 site で開けるようにした
- `LanguageResource` は migration 期間は残すが、default core の恒久機能にはせず、将来的に optional module + AI-friendly 管理へ分離する roadmap を `docs/internal/language-resource-separation.md` へ切り出した

## Completed Today

- `docker/mariadb/config-initdb/027_single_proxy_source_output_seed.sql` を廃止し、default core 用の `027_project1_single_proxy_source_output_seed.sql` を追加
- `PAYPAL-PROXY-SERVER`
  - legacy `ProjectSourceOutput.PID=28` (`/proxy_paypal`) を canonical key へ remap
  - target assignment は `Project` 6 function + `PaypalSubscription.GetActiveEikaiwaSubscriptionList`
- `UPLOADER-PROXY-SERVER`
  - legacy `ProjectSourceOutput.PID=117` (`/proxy_uploader`) を canonical key へ remap
  - target assignment は `DropboxUploadToken.GetDropboxUploadToken`
- `SAMPLE-SINGLE-PROXY-SERVER` / `SAMPLE-SINGLE-PROXY-CLIENT` は default initdb から外し、`docker/mariadb/config-sample-seed/001_single_proxy_sample_source_output_seed.sql` へ移動
- optional sample seed を流す helper として `scripts/apply_config_sample_seed.sh` を追加
- `docker/compose-scenarios/01_mtool.compose.yaml` を追加し、`MTOOL` only の scenario-local writable state を分離
- `docker/compose-scenarios/02_single_proxy_sample.compose.yaml` を追加し、core initdb + `001_single_proxy_sample_source_output_seed.sql` を staged initdb volume へコピーしてから起動する sample scenario を追加
- `docker/mariadb/config-sample-seed/010_reference_project_catalog_seed.sql` を追加し、legacy `Project 2+` を reference/test catalog として追加する最小 seed を作成
- `docker/compose-scenarios/03_reference_projects.compose.yaml` を追加し、reference project catalog 専用 scenario を追加
- `generated/scenarios/` 配下に scenario-local runtime state の置き場を追加
- sample SQL を read-only mount へ直差しすると MariaDB initdb で失敗することを確認し、`db-config-initdb` staging volume + `900_` prefix copy で順序と writable mount を解決
- `LanguageResource` の扱いを見直し、immediate canonical DB replacement を止めて migration 維持 + 将来分離の roadmap を `docs/internal/language-resource-separation.md` に整理
- `docs/internal/source-output-path-policy.md` を追加し、artifact (`generated`)、昇格済み raw output (`published`)、companion layer (`custom`) の root を固定
- `compose.yaml` に `./published:/var/www/published` と `./custom:/var/www/custom` を追加
- `scripts/publish_project_output.php` を追加し、既存 artifact のあと追い publish を可能にした
- `scripts/create_project_output.php` / `scripts/check_mtool_project1_outputs.php` に `--publish` を追加
- `shared/project_source_output_detail_page.php` に publish 状態表示、artifact ごとの publish 操作、generate+publish 操作を追加
- `shared/project_output_html_module_catalog.php` を追加し、`catalog://html-module/MTOOL/{source_output_key}` ref を resolver で `shared/reference/html-modules/` -> `legacy-source-snapshots/` -> `legacy-source-placeholders/` の順に解決するようにした
- `scripts/show_html_module_catalog.php` を追加し、HTML source output ごとの resolved kind を JSON で確認できるようにした
- `scripts/bootstrap_html_module_roots.php` を追加し、copied snapshot-backed `html` module を canonical root へ昇格できるようにした
- `php scripts/bootstrap_html_module_roots.php --project-key=MTOOL` を実行し、`HTML-DB` 以外の snapshot-backed `html` 12 modules も `shared/reference/html-modules/mtool/*/current` へコピーした
- `scripts/show_html_db_rewrite_map.php` を追加し、`shared/reference/html-modules/mtool/HTML-DB/current` の legacy files を rewrite cluster 単位で確認できるようにした
- `docs/internal/html-db-rewrite-map.md` を追加し、`HTML-DB` rewrite の current-route 対応表と最初の actual rewrite slice を固定した
- `shared/project_output_html_module_generator.php` に `HTML-DB` 用の generated entry wrapper seam を追加し、`project_source_output.php` と `project_source_output_edit.php` は staging 時に `_legacy/` fallback 付き wrapper を出力するようにした
- `shared/project_source_output_change_order_page.php` を追加し、canonical `project_source_outputs.source_output_list_order` を current route から並び替えできるようにした
- `shared/project_output_html_module_generator.php` を拡張し、`project_source_output_change_order.php` も staging 時に generated wrapper + `_legacy/` fallback へ差し替えるようにした
- `shared/project_output_html_module_generator.php` をさらに拡張し、`dbtables.php` / `dbtables_import.php` / `dbtable_columns.php` / `dbtable_edit.php` / `dbtable_column_edit.php` を `tables` cluster 用 wrapper 対象に追加した
- `shared/project_table_edit_page.php` と `shared/project_table_column_edit_page.php` を追加し、canonical `dbtable` / `dbtablecolumns` の create/update/delete を current route で扱えるようにした
- `shared/project_tables_page.php` / `shared/project_table_detail_page.php` / `shared/project_table_columns_page.php` を更新し、canonical table の `edit`、canonical column の `edit`、bootstrap-only table の `create canonical row`、canonical table の `new column` を current route へ接続した
- `shared/project_output_html_module_generator.php` の `dbtable_edit.php` wrapper は current `/edit` route へ、`dbtable_column_edit.php` wrapper は `DBTableColumnPID` が空の add-flow を current `/columns/new` へ redirect するよう更新した
- `shared/legacy_dataclass_reference.php` と `scripts/export_legacy_dataclass_reference.php` を追加し、`original-codes/mtool.sql` から `shared/reference/mtool-legacy-dataclass-catalog.json` を生成できるようにした
- `shared/project_output_html_module_generator.php` を拡張し、`dataclasses.php` / `dataclasses_sync.php` / `dataclass_fields.php` / `dataclass_edit.php` / `dataclass_field_edit.php` を `data-classes` cluster 用 wrapper 対象に追加した
- `shared/project_data_class_edit_page.php` と `shared/project_data_class_field_edit_page.php` を追加し、canonical `dataclass` / `dataclassfields` の create/update/delete を current route で扱えるようにした
- `shared/project_data_classes_page.php` / `shared/project_data_class_detail_page.php` / `shared/project_data_class_fields_page.php` を更新し、canonical data class の `edit`、canonical field の `edit`、bootstrap-only data class の `create canonical row`、canonical data class の `new field` を current route へ接続した
- `shared/legacy_db_access_reference.php` と `scripts/export_legacy_db_access_reference.php` を追加し、`original-codes/mtool.sql` から `shared/reference/mtool-legacy-db-access-catalog.json` を生成できるようにした
- `shared/project_output_html_module_generator.php` を拡張し、`da.php` / `da_edit.php` / `da_funcs.php` / `da_funcs_change_order.php` / `da_source.php` / `da_sync.php` を `db-access-core` cluster 用 wrapper 対象に追加した
- `shared/project_output_html_module_generator.php` をさらに拡張し、`da_func_edit.php` / `da_func_move.php` / `da_func_source.php` / `da_func_endpoint.php` / `da_func_sort_order_edit.php` / `da_func_select_*` / `da_func_update_*` / `da_func_insert_*` を `db-access-functions` cluster 用 wrapper 対象に追加した
- `shared/project_output_html_module_generator.php` をさらに拡張し、`da_proxy_custom.php` / `da_proxy_custom_edit.php` / `da_proxy_custom_func.php` / `da_proxy_custom_func_change_order.php` / `da_proxy_custom_func_edit.php` を `proxy-custom` cluster 用 wrapper 対象に追加した
- `da_proxy_custom_endpoint.php` と `da_edit_proxy_single_target.php` / `da_funcs_edit_proxy_single*.php` は、GET/HEAD preview を current route へ handoff しつつ、POST/action request と unknown PID だけ `_legacy/` fallback を残す wrapper に切り替えた
- `shared/project_output_html_module_generator.php` をさらに拡張し、`compare_output.php` / `compare_output_edit.php` / `compare_output_additional_path.php` / `compare_output_additional_path_edit.php` を `compare-output-settings` cluster 用 wrapper 対象に追加した
- `shared/project_output_html_module_generator.php` をさらに拡張し、`compare_output_do.php` / `compare_output_do_ajax.php` を `compare-output-run` cluster の wrapper 対象に追加した
- `shared/lab_compare_output_page.php` / `shared/lab_compare_output_job_page.php` / `shared/lab_compare_output_job_api_page.php` を更新し、current compare-output run route を admin / lab の両 site から読めるようにした

## Verified State

fresh volume 再初期化後の `config_app.projects`:

- `MTOOL` 1 row のみ

fresh volume 再初期化後の `MTOOL` source output catalog:

- `RUNTIME-DBCLASSES`
- `DBIMPORT-PROXY-SERVER`
- `DBIMPORT-PROXY-CLIENT`
- `PAYPAL-PROXY-SERVER`
- `UPLOADER-PROXY-SERVER`

default state では `SAMPLE-SINGLE-PROXY-SERVER` / `SAMPLE-SINGLE-PROXY-CLIENT` は入らない。

single-function proxy target counts:

- `PAYPAL-PROXY-SERVER`: 7
- `UPLOADER-PROXY-SERVER`: 1

build plan check:

- `PAYPAL-PROXY-SERVER`: `function_count=7`, `unresolved_function_count=0`, `unresolved_auth_count=0`
- `UPLOADER-PROXY-SERVER`: `function_count=1`, `unresolved_function_count=0`, `unresolved_auth_count=0`

scenario compose verification:

- `02_single_proxy_sample.compose.yaml` を temporary port override (`18081` / `18082` / `33071` / `33072`) で起動確認
- `projects` count は `1` (`MTOOL` only)
- `project_source_outputs` count は `7`
- sample rows:
  - `SAMPLE-SINGLE-PROXY-CLIENT`
  - `SAMPLE-SINGLE-PROXY-SERVER`
- sample artifact 生成確認:
  - `generated/scenarios/02-single-proxy-sample/source-outputs/MTOOL/20260512-002313-f48c776b/manifest.json`
- `03_reference_projects.compose.yaml` を temporary port override (`18091` / `18092` / `33081` / `33082`) で起動確認
- `projects` count は `8`
- generic sample project rows:
  - `SAMPLE1`
  - `SAMPLE2`
  - `SAMPLE3`
  - `SAMPLE4`
  - `SAMPLE5`
  - `SAMPLE6`
- latest `HTML-DB` publish artifact:
  - `artifact_key=20260512-080154-64df766f`
  - `source_file_count=222`
- latest full parity regression:
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`

legacy `Mtool` output coverage check before parity bridge:

- legacy inventory 上の `Project 1 (Mtool)` は 36 outputs
- fresh initdb baseline の `MTOOL` seed / live `config_app` catalog で確認できる output は 5 のみ
- present / remapped outputs:
  - `PSO 1` -> `RUNTIME-DBCLASSES`
  - `PSO 28` -> `PAYPAL-PROXY-SERVER`
  - `PSO 117` -> `UPLOADER-PROXY-SERVER`
  - `PSO 300` -> `DBIMPORT-PROXY-SERVER`
  - `PSO 301` -> `DBIMPORT-PROXY-CLIENT`
- bridge 着手前に `mtool-core` で未モデルだった slice:
  - `html` x21 (`PSO 13,14,15,16,17,18,19,20,21,27,31,32,33,34,35,36,38,83,84,150,356`)
  - `LanguageResource` x10 (`PSO 265,269,274,279,280,329,353,355,361,369`)

working-session HTML bridge verification:

- live `config_app` に `028_project1_html_source_output_seed.sql` + `029_project1_html_source_output_placeholder_override.sql` を適用し、`MTOOL` source output count は `26`
- `html` 21 rows は `legacy-directory-mirror` strategy で seed 済み
- available module は `shared/reference/legacy-source-snapshots/mtool/html/*` の copied snapshot を参照する
- unavailable module は `shared/reference/legacy-source-placeholders/mtool/html/*` の placeholder を参照する
- `HTML-DB` artifact 生成確認:
  - `source_file_count=160`
  - `generated/source-outputs/MTOOL/20260512-012829-9f914785/manifest.json`
- `HTML-MINUTES` artifact 生成確認:
  - `source_file_count=1`
  - `generated/source-outputs/MTOOL/20260512-012828-37ac85ef/manifest.json`

working-session HTML catalog-ref bridge verification:

- live `config_app` の `MTOOL` html 21 rows を `catalog://html-module/MTOOL/{source_output_key}` へ更新
- `show_html_module_catalog.php` の結果:
  - `html_source_output_count=21`
  - `canonical-html-module=1`
  - `legacy-html-snapshot=12`
  - `legacy-html-placeholder=8`
- `shared/reference/html-modules/mtool/HTML-DB/current` を追加し、`HTML-DB` を fallback ではなく canonical html module root から解決するようにした
- `HTML-DB` は canonical html module root 経由でも create + publish 成功:
  - `artifact_key=20260512-024022-a6511d01`
  - `published/source-outputs/MTOOL/HTML-DB`
- `HTML-MINUTES` は catalog ref 経由の placeholder fallback でも create + publish 成功:
  - `artifact_key=20260512-023807-472f3cb2`
  - `published/source-outputs/MTOOL/HTML-MINUTES/README.md`

working-session HTML canonical bootstrap verification:

- `php scripts/bootstrap_html_module_roots.php --project-key=MTOOL` を実行
- bootstrap 結果は `module_count=13`, `created_count=12`, `skipped_count=1`, `failure_count=0`
- copied snapshot-backed `html` module は `shared/reference/html-modules/mtool/*/current` へ集約された
- `show_html_module_catalog.php` の結果:
  - `html_source_output_count=21`
  - `canonical-html-module=13`
  - `legacy-html-snapshot=0`
  - `legacy-html-placeholder=8`
- `HTML-CHAT` は canonical html module root 経由でも create + publish 成功:
  - `artifact_key=20260512-024352-22501a0c`
  - `published/source-outputs/MTOOL/HTML-CHAT`
- bootstrap 後に `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex` を再実行
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`

working-session HTML full canonical-root verification:

- `scripts/bootstrap_html_module_roots.php` を snapshot 専用から fallback source 対応へ広げた
- `php scripts/bootstrap_html_module_roots.php --project-key=MTOOL` を再実行
  - `module_count=21`
  - `created_count=8`
  - `skipped_count=13`
  - `failure_count=0`
- 追加で canonical 化された placeholder-backed module:
  - `HTML-MINUTES`
  - `HTML-REQ`
  - `HTML-SETTINGS-UPLOADER`
  - `HTML-SETTINGS-SERVER`
  - `HTML-SETTINGS-DBUSER`
  - `HTML-SETTINGS-DBCONNECTION`
  - `HTML-SETTINGS-TOP`
  - `HTML-SETTINGS-DBBACKUP`
- `show_html_module_catalog.php` の結果:
  - `html_source_output_count=21`
  - `canonical-html-module=21`
- `HTML-MINUTES` は canonical html module root 経由でも create + publish 成功:
  - `artifact_key=20260512-025231-c32dbe03`
  - `published/source-outputs/MTOOL/HTML-MINUTES`
- full regression として `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex` を再実行
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`

working-session HTML strategy rename verification:

- code / seed 側で `html-module-catalog` strategy を追加し、`html` 専用の正式 strategy 名として扱うようにした
- live `config_app` に `029_project1_html_source_output_placeholder_override.sql` を再適用し、`MTOOL` html 21 rows の `artifact_strategy` を `html-module-catalog` へ更新した
- `show_html_module_catalog.php` の結果は引き続き:
  - `html_source_output_count=21`
  - `canonical-html-module=21`
- `HTML-DB` は `html-module-catalog` 経由でも create + publish 成功:
  - `artifact_key=20260512-025947-5a163128`
  - `published/source-outputs/MTOOL/HTML-DB`
- `check_mtool_project1_outputs.php` を再実行し、`html-module-catalog` へ切り替え後も `definition_count=36`, `success_count=36`, `failure_count=0` を確認した

working-session HTML generator split verification:

- `shared/project_output_html_module_generator.php` を追加し、`html-module-catalog` は dedicated generator dispatch を通すようにした
- `shared/project_output_legacy_source_generator.php` は `legacy-directory-mirror` 専用へ戻し、HTML は `LanguageResource` と実装経路を共有しない状態にした
- `docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish` を再実行
  - `artifact_key=20260512-030336-99c51529`
  - `published/source-outputs/MTOOL/HTML-DB`
- `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex` を再実行
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`

working-session HTML-DB rewrite map verification:

- `php scripts/show_html_db_rewrite_map.php` を実行
- inventory 結果:
  - `legacy_file_count=160`
  - `cluster_count=18`
  - `unmapped_file_count=0`
  - `status[available]=3`
  - `status[available-partial]=112`
  - `status[planned]=45`
- current route-backed で最初に着手しやすい slice は `source-outputs` cluster (`project_source_output*.php`, `6` files)
- 参照文書:
  - `docs/internal/html-db-rewrite-map.md`
  - `shared/reference/html-modules/mtool/HTML-DB/README.md`

working-session HTML-DB source-output seam verification:

- `shared/project_output_html_module_generator.php` に、`MTOOL/HTML-DB` の `project_source_output.php` / `project_source_output_edit.php` を generated wrapper へ差し替える post-copy rewrite を追加
- `docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish` を再実行
  - `artifact_key=20260512-040212-d99d0932`
  - `source_file_count=162`
  - `published/source-outputs/MTOOL/HTML-DB/_legacy/project_source_output.php`
  - `published/source-outputs/MTOOL/HTML-DB/_legacy/project_source_output_edit.php`
- generated wrapper 確認:
  - `published/source-outputs/MTOOL/HTML-DB/project_source_output.php`
  - `published/source-outputs/MTOOL/HTML-DB/project_source_output_edit.php`
- `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex` を再実行
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`

working-session HTML-DB source-output change-order rewrite verification:

- `shared/project_source_output_change_order_page.php` を追加し、`/projects/MTOOL/source-outputs/change-order` で canonical `source_output_list_order` を更新できるようにした
- current `source-outputs` 一覧には `Change Order of Source Output Definitions` link を追加した
- `shared/project_output_html_module_generator.php` は `project_source_output_change_order.php` も generated wrapper 対象に含めるようになった
- `docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish` を再実行
  - `artifact_key=20260512-044424-96a4174f`
  - `published/source-outputs/MTOOL/HTML-DB/project_source_output_change_order.php` は `/projects/MTOOL/source-outputs/change-order` redirect wrapper になった
- `published/source-outputs/MTOOL/HTML-DB/_legacy/project_source_output_change_order.php` は preserved fallback として残る
- `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex` を再実行
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`

working-session HTML-DB data-classes rewrite verification:

- `php scripts/export_legacy_dataclass_reference.php --project-key=MTOOL --project-pid=1 --sql-dump=original-codes/mtool.sql --output=shared/reference/mtool-legacy-dataclass-catalog.json` を実行
  - `data_class_count=101`
  - `field_count=1025`
- route smoke:
  - `/projects/MTOOL/data-classes/Project/edit => project_data_class_edit`
  - `/projects/MTOOL/data-classes/Project/fields/new => project_data_class_field_edit`
  - `/projects/MTOOL/data-classes/Project/fields/PID/edit => project_data_class_field_edit`
  - `/projects/MTOOL/data-classes/sync => project_data_classes_sync`
- `docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish` を再実行
  - `artifact_key=20260512-055041-e822193f`
  - `source_file_count=174`
- `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex` を再実行
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`

working-session HTML-DB db-access-core rewrite verification:

- `php scripts/export_legacy_db_access_reference.php --project-key=MTOOL --project-pid=1 --sql-dump=original-codes/mtool.sql --output=shared/reference/mtool-legacy-db-access-catalog.json` を実行
  - `db_access_count=101`
  - `function_count=518`
- `shared/project_output_html_module_generator.php` に `da.php` / `da_edit.php` / `da_funcs.php` / `da_funcs_change_order.php` / `da_source.php` / `da_sync.php` の generated wrapper seam を追加
- published wrapper 確認:
  - `published/source-outputs/MTOOL/HTML-DB/da.php`
  - `published/source-outputs/MTOOL/HTML-DB/da_edit.php`
  - `published/source-outputs/MTOOL/HTML-DB/da_funcs.php`
  - `published/source-outputs/MTOOL/HTML-DB/da_funcs_change_order.php`
  - `published/source-outputs/MTOOL/HTML-DB/da_source.php`
  - `published/source-outputs/MTOOL/HTML-DB/da_sync.php`
- `docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish` を再実行
  - `artifact_key=20260512-060029-91039e43`
  - `source_file_count=180`
- `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex` を再実行
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`

working-session HTML-DB db-access-functions rewrite verification:

- `shared/project_output_html_module_generator.php` に `da_func_edit.php` / `da_func_move.php` / `da_func_source.php` / `da_func_endpoint.php` / `da_func_sort_order_edit.php` / `da_func_select_*` / `da_func_update_*` / `da_func_insert_*` の generated wrapper seam を追加
- `da_func_select_where_input_aid.php` は bare preview だけ current `input-aid` route へ寄せ、legacy filter / candidate-selection state は `_legacy/` fallback を残すようにした
- `da_func_select_where_change_order.php` / `da_func_update_delete_where_change_order.php` は preview-only GET を current route へ寄せ、legacy `NewSortOrder` / `doReset` action は `_legacy/` fallback を残すようにした
- `da_func_*_edit.php` の add-flow は current `/new` へ寄せ、existing item PID deep link は current designer list へ寄せる pragmatic bridge を追加した
- published wrapper 確認:
  - `published/source-outputs/MTOOL/HTML-DB/da_func_edit.php`
  - `published/source-outputs/MTOOL/HTML-DB/da_func_select_where_input_aid.php`
  - `published/source-outputs/MTOOL/HTML-DB/da_func_select_where_edit.php`
  - `published/source-outputs/MTOOL/HTML-DB/da_func_update_delete_where_change_order.php`
  - `published/source-outputs/MTOOL/HTML-DB/da_func_insert_target_field_edit.php`
- `docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish` を再実行
  - `artifact_key=20260512-061043-cbf1d69e`
  - `source_file_count=201`
- `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex` を再実行
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`

working-session HTML-DB proxy-custom rewrite verification (initial state):

- `shared/project_output_html_module_generator.php` に `da_proxy_custom.php` / `da_proxy_custom_edit.php` / `da_proxy_custom_func.php` / `da_proxy_custom_func_change_order.php` / `da_proxy_custom_func_edit.php` の generated wrapper seam を追加した
- copied legacy `daCustomProxy.PID -> custom_proxy_key` bridge map により、custom proxy list / add / edit / functions deep link を current `project_custom_proxy*` route へ寄せるようにした
- この時点では `da_proxy_custom_endpoint.php` と `da_edit_proxy_single_target.php` / `da_funcs_edit_proxy_single*.php` に対応 current route がまだ無かったため、explicit `_legacy/` wrapper として残すようにした
- published wrapper 確認:
  - `published/source-outputs/MTOOL/HTML-DB/da_proxy_custom.php`
  - `published/source-outputs/MTOOL/HTML-DB/da_proxy_custom_edit.php`
  - `published/source-outputs/MTOOL/HTML-DB/da_proxy_custom_func_edit.php`
  - `published/source-outputs/MTOOL/HTML-DB/da_proxy_custom_endpoint.php`
  - `published/source-outputs/MTOOL/HTML-DB/da_edit_proxy_single_target.php`
- `docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish` を再実行
  - `artifact_key=20260512-061854-7b041dee`
  - `source_file_count=211`
- `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex` を再実行
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`

working-session HTML-DB compare-output-settings rewrite verification:

- `shared/project_output_html_module_generator.php` に `compare_output.php` / `compare_output_edit.php` / `compare_output_additional_path.php` / `compare_output_additional_path_edit.php` の generated wrapper seam を追加した
- copied legacy `CompareOutput.PID -> compare_output_key` と `CompareOutputAdditionalPath.PID -> additional_path_key` bridge map により、settings list / add / edit / additional paths deep link を current `project_compare_output*` route へ寄せるようにした
- compare-output 実行系 asset (`compare_output_template_for_*.txt`, `compare_ignore_dir_setting_regex.txt`) は、`compare_output_do*.php` rewrite までは触らない方針に固定した
- published wrapper 確認:
  - `published/source-outputs/MTOOL/HTML-DB/compare_output.php`
  - `published/source-outputs/MTOOL/HTML-DB/compare_output_edit.php`
  - `published/source-outputs/MTOOL/HTML-DB/compare_output_additional_path.php`
  - `published/source-outputs/MTOOL/HTML-DB/compare_output_additional_path_edit.php`
- `docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish` を再実行
  - `artifact_key=20260512-062743-411d932f`
  - `source_file_count=215`
- `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex` を再実行
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`

working-session HTML-DB compare-output-run entry rewrite verification:

- `shared/project_output_html_module_generator.php` に `compare_output_do.php` / `compare_output_do_ajax.php` の wrapper seam を追加した
- `compare_output_do.php` は current `/runs/compare-output/{project_key}` route へ redirect し、`compare_output_do_ajax.php` も known project request を current run flow へ handoff する wrapper とした
- `shared/lab_compare_output_page.php` / `shared/lab_compare_output_job_page.php` / `shared/lab_compare_output_job_api_page.php` を更新し、current compare-output run route を admin / lab の両 site から読めるようにした
- published wrapper 確認:
  - `published/source-outputs/MTOOL/HTML-DB/compare_output_do.php`
  - `published/source-outputs/MTOOL/HTML-DB/compare_output_do_ajax.php`
- `docker compose exec -T web-admin curl -I -s http://127.0.0.1/runs/compare-output/MTOOL` を確認
  - `302 Found`
  - `Location: /login?redirect=%2Fruns%2Fcompare-output%2FMTOOL`
- `docker compose exec -T web-lab curl -I -s http://127.0.0.1/runs/compare-output/MTOOL` を確認
  - `302 Found`
  - `Location: /login?redirect=%2Fruns%2Fcompare-output%2FMTOOL`
- `docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish` を再実行
  - `artifact_key=20260512-063822-eb3c4ff7`
  - `source_file_count=217`
- `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex` を再実行
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`

working-session HTML-DB tables entry rewrite verification:

- live `config_app.dbtable` count を確認したところ `ProjectPID=1` rows は `0` だった
- `shared/project_output_html_module_generator.php` は `dbtables.php` / `dbtables_import.php` / `dbtable_columns.php` / `dbtable_edit.php` / `dbtable_column_edit.php` も generated wrapper 対象に含めるようになった
- `docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish` を再実行
  - `artifact_key=20260512-045116-b45a46eb`
  - `source_file_count=168`
  - `published/source-outputs/MTOOL/HTML-DB/dbtables.php` は `/projects/MTOOL/tables` redirect wrapper になった
  - `published/source-outputs/MTOOL/HTML-DB/dbtables_import.php` は `/projects/MTOOL/tables/import` redirect wrapper になった
  - `published/source-outputs/MTOOL/HTML-DB/dbtable_columns.php` / `dbtable_edit.php` / `dbtable_column_edit.php` は empty table PID map を持つ partial wrapper になり、現状は `_legacy/` fallback が有効
- `_legacy/` preserved fallback:
  - `published/source-outputs/MTOOL/HTML-DB/_legacy/dbtables.php`
  - `published/source-outputs/MTOOL/HTML-DB/_legacy/dbtables_import.php`
  - `published/source-outputs/MTOOL/HTML-DB/_legacy/dbtable_columns.php`
  - `published/source-outputs/MTOOL/HTML-DB/_legacy/dbtable_edit.php`
  - `published/source-outputs/MTOOL/HTML-DB/_legacy/dbtable_column_edit.php`
- `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex` を再実行
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`

working-session HTML-DB table-focus import rewrite verification:

- `shared/project_table_import_service.php` は focused table scope を受けるようになり、`app_project_table_import_preview(..., $focusTableName)` / `app_project_table_import_apply(..., $focusTableName)` で 1 table 単位の diff / apply を current service から扱えるようになった
- `shared/project_tables_import_page.php` は `?table=` focus を受け取り、legacy `dbtables_import_for_each.php` の pure preview 導線を current import page に集約できるようになった
- `scripts/import_project_tables.php` は `--table=NAME` を受けるようになり、focused import を CLI からも叩ける
- `shared/project_output_html_module_generator.php` は `dbtables_import_for_each.php` も generated wrapper 対象に含め、pure GET preview だけ `/projects/MTOOL/tables/import?table={TableName}` へ redirect し、`DoImport` / `DoImportAll` / `FieldName` / `IncludeOrder` を含む旧 action request は `_legacy/` fallback を残す
- `docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish` を再実行
  - `artifact_key=20260512-045720-24b34188`
  - `published/source-outputs/MTOOL/HTML-DB/dbtables_import_for_each.php` は current focused import wrapper になった
  - `published/source-outputs/MTOOL/HTML-DB/_legacy/dbtables_import_for_each.php` は preserved fallback として残る
- focused preview verification:
  - `docker compose exec -T web-admin php -r '... app_project_table_import_preview($app, "MTOOL", "live-schema", "project_source_outputs") ...'`
  - `ok=true`
  - `source_table_count=1`
  - `table_names=["project_source_outputs"]`
- `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex` を再実行
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`

working-session HTML-DB legacy dbtable PID bridge verification:

- `scripts/export_legacy_dbtable_reference.php` を追加し、developer utility として `original-codes/mtool.sql` から legacy `dbtable` catalog snapshot を抽出できるようにした
- `php scripts/export_legacy_dbtable_reference.php --project-key=MTOOL --project-pid=1 --sql-dump=original-codes/mtool.sql --output=shared/reference/mtool-legacy-dbtable-catalog.json` を実行
  - `table_count=90`
  - output: `shared/reference/mtool-legacy-dbtable-catalog.json`
- `shared/legacy_dbtable_reference.php` を追加し、runtime は copied reference JSON だけを読むようにした
- `shared/project_output_html_module_generator.php` の `legacy_table_pid_map` は canonical `dbtable` snapshot ではなく、まず `shared/reference/mtool-legacy-dbtable-catalog.json` + self-host alias map から legacy `DBTablePID -> current table name` を構築するようになった
- resulting self-host map example:
  - `1 => projects`
  - `41 => project_source_outputs`
  - `45 => project_custom_proxies`
  - `83 => project_db_access_function_source_output_targets`
  - `215 => project_compare_outputs`
- `docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish` を再実行
  - `artifact_key=20260512-051713-9a918c3e`
  - `published/source-outputs/MTOOL/HTML-DB/dbtable_columns.php`
  - `published/source-outputs/MTOOL/HTML-DB/dbtable_edit.php`
  - `published/source-outputs/MTOOL/HTML-DB/dbtable_column_edit.php`
  - 上記 wrapper には legacy `DBTablePID` map が埋め込まれ、`config_app.dbtable` count が `0` のままでも self-host slice redirect が有効になった
- `docker compose exec -T db-config mariadb -uroot -pconfig_root_local_2026 -N -e "SELECT COUNT(*) FROM dbtable WHERE ProjectPID=1;" config_app`
  - result: `0`
  - canonical `dbtable` row はまだ未投入だが、bridge 自体は copied legacy reference により稼働可能
- `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex` を再実行
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`

working-session LanguageResource bridge verification:

- live `config_app` に `030_project1_language_resource_source_output_seed.sql` + `031_project1_language_resource_source_output_placeholder_override.sql` を適用し、`MTOOL` source output count は `36`
- `LanguageResource` 10 rows は `legacy-directory-mirror` strategy で seed 済み
- available module は `shared/reference/legacy-source-snapshots/mtool/language-resource/*` の copied snapshot を参照する
- unavailable module は `shared/reference/legacy-source-placeholders/mtool/language-resource/*` の placeholder を参照する
- copied snapshot rows:
  - `LANGRES-PHP-DEV-LIB`
  - `LANGRES-PHP-MTOOL-LIB`
- placeholder rows:
  - `LANGRES-PHP-MATSUESOFT-LIB`
  - `LANGRES-PHP-JA-WEB-LIB`
  - `LANGRES-PHP-PUBLIC-WEB-LIB`
  - `LANGRES-JAVA-MATSUESOFT-COMMON`
  - `LANGRES-SWIFT-IOS`
  - `LANGRES-CS-UWP-COMMONSTRINGS`
  - `LANGRES-CS-DEGODB-RESOURCES`
  - `LANGRES-PHP-JA-WEB-LIB-ALT`
- `LANGRES-PHP-DEV-LIB` artifact 生成確認:
  - `source_file_count=1`
  - `generated/source-outputs/MTOOL/20260512-015739-3f949525/manifest.json`
- `LANGRES-PHP-MTOOL-LIB` artifact 生成確認:
  - `source_file_count=4`
  - `generated/source-outputs/MTOOL/20260512-015739-acd7b4fa/manifest.json`

full Project 1 output parity verification:

- `scripts/check_mtool_project1_outputs.php` を追加し、`docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --requested-by=codex` を実行
- container 内 loop build の結果は `definition_count=36`, `success_count=36`, `failure_count=0`
- bridge 付き current system 上では `MTOOL Project 1` の旧 `36` outputs がすべて artifact 化できる状態になった

published output verification:

- `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --requested-by=codex --publish` を実行
- result log は `tmp/check_mtool_project1_outputs_publish_20260512.json`
- publish 結果は `definition_count=36`, `success_count=36`, `failure_count=0`
- `published/source-outputs/MTOOL/` 直下の source output dir 数は `36`
- `published/source-outputs/MTOOL/RUNTIME-DBCLASSES/` には `_base/`, `_wrappers/`, `_support/` を含む layered runtime bundle が展開済み

working-session HTML-DB table workflow verification:

- `php -l`:
  - `shared/project_table_edit_page.php`
  - `shared/project_table_column_edit_page.php`
  - `shared/project_tables_page.php`
  - `shared/project_table_detail_page.php`
  - `shared/project_table_columns_page.php`
  - `shared/router.php`
  - `shared/http.php`
  - `shared/project_output_html_module_generator.php`
- `docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish`
  - `artifact_key=20260512-053226-99822ca3`
- published wrapper check:
  - `published/source-outputs/MTOOL/HTML-DB/dbtable_edit.php` は mapped `DBTablePID` を `/projects/MTOOL/tables/{table}/edit` へ redirect
  - `published/source-outputs/MTOOL/HTML-DB/dbtable_column_edit.php` は `DBTableColumnPID` 空時に `/projects/MTOOL/tables/{table}/columns/new` へ redirect
- `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex`
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`

working-session proxy route verification:

- `shared/project_single_proxy_page.php` / `shared/project_custom_proxy_endpoint_page.php` / `shared/project_proxy_route_common.php` を追加し、current `/projects/{project_key}/proxy/single` と `/projects/{project_key}/proxy/custom/{custom_proxy_key}/endpoint` route を実装した
- `shared/project_output_html_module_generator.php` は `da_edit_proxy_single_target.php` / `da_funcs_edit_proxy_single*.php` / `da_proxy_custom_endpoint.php` を current proxy route handoff wrapper へ更新した
- `da_proxy_custom_endpoint.php` の current preview は legacy `CUSTOMPROXYSERVER` semantics に合わせて custom proxy server target のみを候補にし、`ReleaseType=Release` は `DBIMPORT-PROXY-SERVER` へ解決される
- `docker compose exec -T web-admin curl -I -s http://127.0.0.1/projects/MTOOL/proxy/single`
  - `302` to `/login?redirect=%2Fprojects%2FMTOOL%2Fproxy%2Fsingle`
- `docker compose exec -T web-admin curl -I -s http://127.0.0.1/projects/MTOOL/proxy/custom/DB-IMPORT/endpoint`
  - `302` to `/login?redirect=%2Fprojects%2FMTOOL%2Fproxy%2Fcustom%2FDB-IMPORT%2Fendpoint`
- `docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish` を再実行
  - `artifact_key=20260512-080154-64df766f`
  - `source_file_count=222`
  - `published_file_count=222`
- `published/source-outputs/MTOOL/HTML-DB/da_proxy_custom_endpoint.php` は custom proxy server target だけを埋め込む wrapper になった
- `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex` を再実行
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`

## Commands

core-only clean reset:

```zsh
docker compose down -v
docker compose up -d
```

scenario 1 (`MTOOL`) clean reset:

```zsh
docker compose -f compose.yaml -f docker/compose-scenarios/01_mtool.compose.yaml down -v
docker compose -f compose.yaml -f docker/compose-scenarios/01_mtool.compose.yaml up -d
```

scenario 2 (`single proxy sample`) clean reset:

```zsh
docker compose -f compose.yaml -f docker/compose-scenarios/02_single_proxy_sample.compose.yaml down -v
docker compose -f compose.yaml -f docker/compose-scenarios/02_single_proxy_sample.compose.yaml up -d
```

scenario 3 (`reference projects`) clean reset:

```zsh
docker compose -f compose.yaml -f docker/compose-scenarios/03_reference_projects.compose.yaml down -v
docker compose -f compose.yaml -f docker/compose-scenarios/03_reference_projects.compose.yaml up -d
```

scenario 2 を base stack と並行に検証したい場合:

```zsh
CONFIG_DB_HOST_PORT=33071 \
LAB_DB_HOST_PORT=33072 \
ADMIN_HTTP_PORT=18081 \
LAB_HTTP_PORT=18082 \
docker compose -f compose.yaml -f docker/compose-scenarios/02_single_proxy_sample.compose.yaml up -d
```

optional sample/test seed apply:

```zsh
./scripts/apply_config_sample_seed.sh
```

single file only:

```zsh
./scripts/apply_config_sample_seed.sh 001_single_proxy_sample_source_output_seed.sql
```

create + publish single output:

```zsh
docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=RUNTIME-DBCLASSES --requested-by=codex --publish
```

publish latest artifact only:

```zsh
docker compose exec -T web-admin php /var/www/scripts/publish_project_output.php --project-key=MTOOL --source-output-key=RUNTIME-DBCLASSES
```

show html module catalog status:

```zsh
docker compose exec -T web-admin php /var/www/scripts/show_html_module_catalog.php
```

bootstrap copied html snapshots into canonical roots:

```zsh
php scripts/bootstrap_html_module_roots.php --project-key=MTOOL
```

create + publish all MTOOL outputs:

```zsh
docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex --publish
```

## Build Project Rewrite Update

- `shared/build_job_service.php` を追加し、selected `ProjectSourceOutput` definition を `generate + publish` して `generated/build-jobs/{project_key}/{job_key}/manifest.json` へ記録する file-based build job service を入れた
- `shared/lab_build_page.php` / `shared/lab_build_job_page.php` / `shared/lab_build_job_api_page.php` を追加し、current `/runs/builds/{project_key}` / `/runs/builds/{job_key}` / `/api/runs/builds/{job_key}` を admin / lab の両 site から開けるようにした
- `shared/router.php` / `shared/http.php` / `shared/project_detail_page.php` を更新し、Build 実行 module は current route available 扱いに切り替えた
- `shared/project_output_html_module_generator.php` で `build_project.php` / `build_project_for_each.php` / `build_project_ajax.php` / `build_project_ajax_check_if_completed.php` を current build flow handoff wrapper へ差し替えた
- `php -l` は `shared/build_job_service.php` / `shared/lab_build_page.php` / `shared/lab_build_job_page.php` / `shared/lab_build_job_api_page.php` / `shared/router.php` / `shared/http.php` / `shared/project_detail_page.php` / `shared/project_output_html_module_generator.php` で通った
- `docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish` を再実行
  - `artifact_key=20260512-065308-645dd44c`
  - `source_file_count=221`
- `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex`
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`
- `docker compose exec -T web-admin curl -I -s http://127.0.0.1/runs/builds/MTOOL`
  - `302` to `/login?redirect=%2Fruns%2Fbuilds%2FMTOOL`
- `docker compose exec -T web-lab curl -I -s http://127.0.0.1/runs/builds/MTOOL`
  - `302` to `/login?redirect=%2Fruns%2Fbuilds%2FMTOOL`
- `docker compose exec -T web-admin php -r '... app_build_job_create(...) ...'` で current build service を smoke し、`generated/build-jobs/MTOOL/20260512-065342-50e181ac/manifest.json` が生成されることを確認した
  - `selected_source_output_count=1`
  - `successful_count=1`
  - `failed_count=0`

working-session endpoint-test rewrite verification:

- `php -l` は `shared/endpoint_test_job_service.php` / `shared/lab_endpoint_test_page.php` / `shared/lab_endpoint_test_job_api_page.php` / `shared/router.php` / `shared/http.php` / `shared/project_detail_page.php` / `shared/project_db_access_function_endpoint_page.php` / `shared/project_output_html_module_generator.php` / `scripts/show_html_db_rewrite_map.php` で通った
- `php scripts/show_html_db_rewrite_map.php` を再実行
  - `legacy_file_count=160`
  - `cluster_count=18`
  - `unmapped_file_count=0`
  - `status[available]=8`
  - `status[available-partial]=116`
  - `status[planned]=36`
- current `/runs/endpoints/{project_key}` route を追加し、`docker compose exec -T web-admin curl -I -s http://127.0.0.1/runs/endpoints/MTOOL` / `docker compose exec -T web-lab curl -I -s http://127.0.0.1/runs/endpoints/MTOOL` の両方で `302` to `/login?redirect=%2Fruns%2Fendpoints%2FMTOOL` を確認した
- `web-admin` CLI では `curl extension` が無かったため、`shared/endpoint_test_job_service.php` に PHP stream fallback を追加した
- `docker compose exec -T web-admin php -r '... app_endpoint_test_job_create(...) ...'` で current endpoint test service を smoke し、`generated/endpoint-test-jobs/MTOOL/20260512-071805-ecba505e/manifest.json` が生成されることを確認した
  - `status=completed`
  - `http_code=200`
  - target: `http://127.0.0.1/health`
- `docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish` を再実行
  - `artifact_key=20260512-071817-68c6ed96`
  - `source_file_count=222`
  - `published_file_count=222`
- `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex`
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`

## Remaining Work

- `MTOOL` の旧 `Project 1` 36 outputs は bridge で生成できる状態になり、`html` は `21` 件すべて canonical root 配下へ集約できたので、残件は canonical root 内の placeholder-backed scaffold を actual generator / actual source of truth へ置き換えることと、`LanguageResource` x10 の将来分離方針整理へ切り替わった
- copied snapshot / placeholder / Tmp 編集は parity bridge の暫定対応としてだけ扱い、最終的には新Mtoolの自前 Output で丸ごと置き換える
- `scripts/check_mtool_project1_outputs.php` の結果を baseline 化し、bridge から canonical generator へ差し替えても同じ key 群で通るよう維持する
- `published/source-outputs/MTOOL/*` は埋まったので、次段は各 dir の中身を bridge から canonical generator へ順次置き換える
- `html` は strategy 名の上でも `legacy-directory-mirror` から切り離せたので、bridge 由来なのは `LanguageResource` 側へ明確に閉じた
- `HTML-DB` rewrite は `da_proxy_custom_endpoint.php` / single-target proxy page の GET/HEAD preview まで current route へ寄せたので、次段は proxy の POST/action semantics と remaining html routes の整理に進む
- `da_proxy_custom_endpoint.php` と single-target proxy page は GET/HEAD handoff 済みで、残る `_legacy/` hold は POST/action request と `endpoint_test_json_ajax.php` POST worker 互換に限られる
- `LanguageResource` は bridge を当面維持しつつ、optional module 化と AI-friendly source of truth への置換を後段で進める
- full parity 完了後に、`Project 2+` の sample/test pack policy と配布導線を二段目で整理する
- `ApacheHostSetting` 8 row の扱いは current parity goal では core scope 外のままにし、必要なら別 source output または別 feature slice として扱う

## Resume Point

次の作業起点は `MTOOL Project 1 output parity` の second phase:

- `da_proxy_custom_endpoint.php` と single-target proxy page は GET/HEAD preview を current `/projects/{project_key}/proxy/single` / `/projects/{project_key}/proxy/custom/{custom_proxy_key}/endpoint` へ handoff 済みで、次段は POST/action semantics を current form workflow へ吸収する
- `endpoint_common_include.php` / `endpoint_lib_include.php` / `endpoint_test_json_client_include.php` は `endpoint_test_json_ajax.php` POST fallback が消えるタイミングまで legacy helper として維持する
- placeholder 由来で canonical root に入った `HTML-MINUTES` / `HTML-REQ` / `HTML-SETTINGS-UPLOADER` / `HTML-SETTINGS-SERVER` / `HTML-SETTINGS-DBUSER` / `HTML-SETTINGS-DBCONNECTION` / `HTML-SETTINGS-TOP` / `HTML-SETTINGS-DBBACKUP` を actual generator または後継 source of truth で置き換える
- `shared/reference/html-modules/mtool/*/current` を canonical 編集点として、`HTML-DB` などから new Mtool の rewrite を始める
- `LanguageResource` bridge 10 rows は維持し、`docs/internal/language-resource-separation.md` に沿って optional module 分離方針を固める
- `scripts/check_mtool_project1_outputs.php` を回帰チェックとして使い、`html` 側を進めても `36/36` を維持する
