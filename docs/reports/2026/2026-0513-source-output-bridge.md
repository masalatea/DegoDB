# 2026-05-13 Source Output Bridge

## Summary

- `shared/project_source_output_route_common.php` に `bridge_errors` helper を追加し、current source-output pages が wrapper 由来の validation / mismatch error を直接表示できるようにした。
- `shared/source_output_repository.php` / `shared/source_output_repository_pdo.php` に delete support を追加し、`shared/project_source_output_edit_page.php` から canonical definition の update/delete を完結できるようにした。
- `shared/project_source_outputs_page.php` は delete success と `bridge_errors` を表示し、`shared/project_source_output_change_order_page.php` も wrapper bridge error を current page 側で受けられるようにした。
- `shared/project_source_output_new_page.php` を追加し、advanced create form と legacy add-flow の current handoff 受け皿を `/projects/{project_key}/source-outputs/new` に置いた。legacy handoff では `ClassType` / `ProxyBaseURL` / mapped target server source output を見て strategy / binding / runtime/template default を初期推定し、legacy dir / URL から安全に読める場合は tentative `source_output_key` / `name` も補う。
- `shared/project_output_html_module_generator.php` の `project_source_output_edit.php` wrapper は existing row の update/delete POST を current `/projects/{project_key}/source-outputs/{source_output_key}/edit` へ bridge し、blank add-flow GET/POST も current `/projects/{project_key}/source-outputs/new` へ handoff する。legacy `TargetServerProjectSourceOutputPID` が current key に解決できる場合は `/new` handoff へ bridge hint として渡す。`project_source_output_change_order.php` wrapper は reorder/reset POST を current `/projects/{project_key}/source-outputs/change-order` へ bridge する。
- published `project_source_output_edit.php` / `project_source_output_change_order.php` から `_legacy/` fallback は消えた。legacy add form に current 必須の `source_output_key` / `name` が無い問題は緩和したが、tentative suggestion は safe な場合だけに留め、`/new` handoff 上では manual confirmation を引き続き要求する。
- tentative identity helper は `html`、proxy、`LanguageResource`、`DBAccess` を対象にし、既存 key と衝突する候補は自動投入しない。`LanguageResource` だけは duplicate dir を許容するため、`-ALT` が空いている場合に限って fallback key を提案する。
- cluster status はまだ `available-partial` である。理由は `CustomFileExtention` など legacy-only source-output fields が current schema にまだ載っておらず、stable `source_output_key` / `name` も duplicate / ambiguous case では legacy add form から確定できないためである。

## Files

- `shared/project_source_output_route_common.php`
- `shared/source_output_repository.php`
- `shared/source_output_repository_pdo.php`
- `shared/project_source_output_edit_page.php`
- `shared/project_source_output_new_page.php`
- `shared/project_source_outputs_page.php`
- `shared/project_source_output_change_order_page.php`
- `shared/project_output_html_module_generator.php`
- `scripts/show_html_db_rewrite_map.php`
- `docs/internal/html-db-rewrite-map.md`
- `docs/reports/2026/2026-0512-mtool-project1-output-parity-plan.md`
- `docs/internal/mtool-admin-roadmap.md`

## Verification

```zsh
php -l shared/project_source_output_route_common.php
php -l shared/source_output_repository.php
php -l shared/source_output_repository_pdo.php
php -l shared/project_source_output_edit_page.php
php -l shared/project_source_output_new_page.php
php -l shared/project_source_outputs_page.php
php -l shared/project_source_output_change_order_page.php
php -l shared/project_output_html_module_generator.php
php -l scripts/show_html_db_rewrite_map.php
php scripts/show_html_db_rewrite_map.php

docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish

php -l published/source-outputs/MTOOL/HTML-DB/project_source_output_edit.php
php -l published/source-outputs/MTOOL/HTML-DB/project_source_output_change_order.php
rg -n "_legacy/|source-outputs/new|legacy_target_server_source_output_key|TargetServerProjectSourceOutputPID" published/source-outputs/MTOOL/HTML-DB/project_source_output_edit.php published/source-outputs/MTOOL/HTML-DB/project_source_output_change_order.php

docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex

docker compose exec -T web-admin php -r 'require "/var/www/shared/project_source_output_new_page.php"; $input = app_source_output_form_defaults(); $input["class_type"] = "html"; $input["source_output_dir"] = "/legacy/ftp/www/dev.matsuesoft.com/settings/dbuser"; $hints = ["legacy_target_server_source_output_key" => "", "legacy_source_output_dir" => $input["source_output_dir"], "legacy_source_template_dir" => ""]; var_export(app_project_source_output_new_bridge_html_identity("Mtool", $input, $hints));'
docker compose exec -T web-admin php -r 'require "/var/www/shared/project_source_output_new_page.php"; $input = app_source_output_form_defaults(); $input["class_type"] = "DBaaSProxyClient"; $input["program_language"] = "cs"; $input["proxy_base_url"] = "https://dev.matsuesoft.com/proxy_dbimport"; $hints = ["legacy_target_server_source_output_key" => "DBIMPORT-PROXY-SERVER", "legacy_source_output_dir" => "", "legacy_source_template_dir" => ""]; var_export(app_project_source_output_new_bridge_proxy_identity("Mtool", $input, $hints));'
docker compose exec -T web-admin php -r 'require "/var/www/shared/project_source_output_new_page.php"; $input = app_source_output_form_defaults(); $input["class_type"] = "LanguageResource"; $input["program_language"] = "php"; $input["source_output_dir"] = "/legacy/ftp/www/ja.matsuesoft.com/lib"; $hints = ["legacy_target_server_source_output_key" => "", "legacy_source_output_dir" => $input["source_output_dir"], "legacy_source_template_dir" => ""]; var_export(app_project_source_output_new_bridge_language_resource_identity("Mtool", $input, $hints, ["LANGRES-PHP-JA-WEB-LIB" => true]));'
```

確認結果:

- rewrite map: `status[available]=14`, `status[available-partial]=116`, `status[planned]=30`
- published `HTML-DB`: `artifact_key=20260513-055055-ffd968a6`, `source_file_count=229`
- published `project_source_output_change_order.php`: `php -l` 成功、`_legacy` hit `0`
- published `project_source_output_edit.php`: `php -l` 成功、`_legacy` hit `0`、blank add-flow は `/projects/MTOOL/source-outputs/new` handoff へ切り替わり、`legacy_target_server_source_output_key` handoff も含む
- `check_mtool_project1_outputs.php`: `definition_count=36`, `success_count=36`, `failure_count=0`
- helper smoke: `HTML-SETTINGS-DBUSER`、`DBIMPORT-PROXY-CLIENT`、duplicate 時の `LANGRES-PHP-JA-WEB-LIB-ALT` を候補化できることを確認した
- tentative key/name slice では current admin page と docs だけを更新したため、publish / parity regression は再実行していない。最新 full result は上記 publish artifact と `36/36` を継続して参照する

## Remaining Focus

- legacy-only source-output fields を current schema に載せるか、migration 期間の切り捨て対象として固定するかの整理。
- legacy add-flow で `source_output_key` / `name` をまだ安全に提案できない duplicate / ambiguous case をどう扱うか、また canonical path へどこまで自動補正するかの整理。
- canonical item mapping / malformed guard 整理と remaining non-currentizable fallback の縮小。

## Restart Reference

- latest handoff: `docs/reports/2026/2026-0513-end-of-day-handoff.md`
- copy/paste prompt: `docs/reports/2026/2026-0513-resume-prompt.md`
