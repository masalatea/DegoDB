# 2026-05-12 End Of Day Handoff

> Superseded on 2026-05-13. For restart, use `docs/reports/2026/2026-0513-end-of-day-handoff.md` and `docs/reports/2026/2026-0513-resume-prompt.md`.

## Summary

- `da_funcs_edit_proxy_single_setting_edit.php` の legacy auth-only POST を current 側へ bridge できるようにした
- current 側では `project_db_access_function_detail_page.php` に `bridge_mode=legacy-single-proxy-auth` を追加し、未送信項目を保持したまま auth 系だけ更新できる
- wrapper 側では legacy `DAFuncPID` / `SingleProxy_SingleGetFuncPID` を current `source_name` / `function_name` へ変換して内部 dispatch する
- blank auth type を明示的に空へ戻すケースと、`SingleProxy_SingleGetFuncPID` 数値から `function_name` への変換も確認済み
- `project_single_proxy_page.php` に bulk target save を追加し、legacy `IsTargetOfSimpleProxyWithProjectSourceOutputAndDAFuncPID[]` checkbox payload を current bulk save へ bridge できるようにした
- `project_custom_proxies_page.php` / `project_custom_proxy_detail_page.php` / `project_custom_proxy_functions_page.php` で custom proxy の create/update/delete/step reorder を current action として受けられるようにした
- `project_output_html_module_generator.php` は `da_proxy_custom_edit.php` / `da_proxy_custom_func_edit.php` / `da_proxy_custom_func_change_order.php` の legacy POST を current route へ bridge し、`daCustomProxyFunc.PID` / `SingleGetFuncPID` / `TargetProjectSourceOutputPIDList[]` を canonical id / key へ変換する
- `HTML-DB` を再 publish し、`artifact_key=20260512-235329-2809a1d6` で更新した
- `scripts/check_mtool_project1_outputs.php` は引き続き `36/36 success`

## Current Position

- `MTOOL` Project 1 parity は維持できている
- 全体進捗の目安は `82-84%`
- mainline 日常利用における legacy runtime 依存は概ね `8-12%`
- 残る主な legacy 依存は以下に絞られた
  - `endpoint_test_json_ajax.php` POST worker
  - unknown PID fallback の残り
  - `html-authoring` cluster

## Files Touched In This Slice

- `shared/project_single_proxy_page.php`
- `shared/project_custom_proxies_page.php`
- `shared/project_custom_proxy_detail_page.php`
- `shared/project_custom_proxy_functions_page.php`
- `shared/custom_proxy_repository.php`
- `shared/custom_proxy_repository_pdo.php`
- `shared/project_db_access_function_detail_page.php`
- `shared/project_output_html_module_generator.php`
- `scripts/show_html_db_rewrite_map.php`
- `docs/internal/html-db-rewrite-map.md`
- `docs/reports/2026/2026-0512-mtool-project1-output-parity-plan.md`
- `docs/internal/mtool-admin-roadmap.md`

## Verified

```zsh
php -l shared/project_custom_proxies_page.php
php -l shared/project_custom_proxy_detail_page.php
php -l shared/project_custom_proxy_functions_page.php
php -l shared/custom_proxy_repository_pdo.php
php -l shared/project_single_proxy_page.php
php -l shared/project_db_access_function_detail_page.php
php -l shared/project_output_html_module_generator.php
docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish
docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex
```

確認結果:

- `HTML-DB` publish 成功
- 最新 publish artifact: `20260512-235329-2809a1d6`
- parity regression: `definition_count=36`, `success_count=36`, `failure_count=0`

## Tomorrow First Step

最初の着手は `endpoint_test_json_ajax.php` POST worker を current `/runs/endpoints/{project_key}` workflow へ吸収すること。

方針:

1. legacy POST payload / helper include 依存を確認する
2. current endpoint test page / API で custom proxy preview worker 相当の入力を受ける bridge design を決める
3. `shared/project_output_html_module_generator.php` の endpoint wrapper を POST bridge 対応にする
4. `HTML-DB` を再 publish する
5. `36/36` regression を再確認する

## Resume Commands

```zsh
cd <repo-root>

docker compose ps

php -l shared/project_custom_proxies_page.php
php -l shared/project_custom_proxy_detail_page.php
php -l shared/project_custom_proxy_functions_page.php
php -l shared/custom_proxy_repository_pdo.php
php -l shared/project_output_html_module_generator.php

php scripts/show_html_db_rewrite_map.php

sed -n '1,260p' shared/project_custom_proxy_endpoint_page.php
sed -n '1,260p' shared/lab_endpoint_test_page.php
sed -n '1,220p' 'original-codes/dev web/db/endpoint_test_json_ajax.php'
sed -n '1,220p' 'original-codes/dev web/db/endpoint_common_include.php'
rg -n "endpoint_test_json_ajax.php|da_proxy_custom_endpoint.php" shared/project_output_html_module_generator.php shared/lab_endpoint_test_page.php

docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish
docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex
```

## Restart Prompt

```text
<repo-root> の MTOOL rewrite 作業を再開してください。

今日の到達点:
- da_funcs_edit_proxy_single_setting_edit.php の legacy auth-only POST は current 化済み
- da_funcs_edit_proxy_single_target.php の legacy bulk target POST も current 化済み
- project_single_proxy_page.php で bulk target save を受けられる
- da_proxy_custom_edit.php / da_proxy_custom_func_edit.php / da_proxy_custom_func_change_order.php の legacy POST / action semantics も current 化済み
- HTML-DB 最新 publish artifact は 20260512-235329-2809a1d6
- check_mtool_project1_outputs.php は 36/36 success

前提:
- original-codes/ は runtime や generator の直接入力には使わない
- copied snapshot / placeholder / tmp bridge は暫定対応としてのみ許容
- published/source-outputs/ が Mtool output の昇格先
- custom/source-outputs/ は companion layer
- PSR-4 対応 namespace / directory layout は最終段の TODO とし、当面は parity 優先で pragmatic な file placement を許容する

次のタスク:
1. endpoint_test_json_ajax.php の legacy POST worker semantics を調査
2. current endpoint test route で custom proxy preview worker 相当の request を受ける bridge を設計する
3. shared/project_output_html_module_generator.php の endpoint wrapper を POST bridge 対応に更新
4. HTML-DB を再 publish
5. check_mtool_project1_outputs.php で 36/36 success を再確認

最初に以下を実行してください:
- docker compose ps
- php -l shared/project_custom_proxy_endpoint_page.php
- php -l shared/lab_endpoint_test_page.php
- php -l shared/project_output_html_module_generator.php
- php scripts/show_html_db_rewrite_map.php
- sed -n '1,260p' shared/project_custom_proxy_endpoint_page.php
- sed -n '1,260p' shared/lab_endpoint_test_page.php
- sed -n '1,220p' 'original-codes/dev web/db/endpoint_test_json_ajax.php'
- sed -n '1,220p' 'original-codes/dev web/db/endpoint_common_include.php'
- rg -n "endpoint_test_json_ajax.php|da_proxy_custom_endpoint.php" shared/project_output_html_module_generator.php shared/lab_endpoint_test_page.php

参照ドキュメント:
- docs/reports/2026/2026-0512-end-of-day-handoff.md
- docs/reports/2026/2026-0512-mtool-project1-output-parity-plan.md
- docs/internal/html-db-rewrite-map.md
- docs/internal/mtool-admin-roadmap.md
```
