# 2026-05-13 Proxy Bridge Error Route Reduction

## Summary

- `project_single_proxy_page.php`, `project_custom_proxies_page.php`, `project_custom_proxy_detail_page.php`, `project_custom_proxy_functions_page.php` が query string の `bridge_errors` も表示できるようにした。
- `shared/project_output_html_module_generator.php` の proxy wrapper を更新し、unknown / missing legacy PID の error path は internal POST dispatch ではなく current list/detail/functions page への redirect で処理するようにした。
- 対象は `da_funcs_edit_proxy_single_setting_edit.php`, `da_proxy_custom_edit.php`, `da_proxy_custom_func_edit.php`, `da_proxy_custom_func_change_order.php`。
- これにより、`sharedRoot` を解決できない環境でも error-only path で `_legacy/` へ戻らず current page 側に寄せられるようになった。残る shared-root 依存は actual save/reorder/job dispatch の POST/action semantics が中心である。

## Files

- `shared/project_proxy_route_common.php`
- `shared/project_single_proxy_page.php`
- `shared/project_custom_proxies_page.php`
- `shared/project_custom_proxy_detail_page.php`
- `shared/project_custom_proxy_functions_page.php`
- `shared/project_output_html_module_generator.php`
- `docs/internal/html-db-rewrite-map.md`
- `docs/internal/mtool-admin-roadmap.md`
- `docs/reports/2026/2026-0512-mtool-project1-output-parity-plan.md`
- `docs/reports/2026/2026-0513-proxy-bridge-error-route-reduction.md`

## Verified

```zsh
php -l shared/project_proxy_route_common.php
php -l shared/project_single_proxy_page.php
php -l shared/project_custom_proxies_page.php
php -l shared/project_custom_proxy_detail_page.php
php -l shared/project_custom_proxy_functions_page.php
php -l shared/project_output_html_module_generator.php
docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish
php -l published/source-outputs/MTOOL/HTML-DB/da_funcs_edit_proxy_single_setting_edit.php
php -l published/source-outputs/MTOOL/HTML-DB/da_proxy_custom_edit.php
php -l published/source-outputs/MTOOL/HTML-DB/da_proxy_custom_func_edit.php
php -l published/source-outputs/MTOOL/HTML-DB/da_proxy_custom_func_change_order.php
docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex
```

確認結果:

- `HTML-DB` publish artifact: `20260513-041005-84840e10`
- published wrapper lint は対象 4 ファイルで通過
- `check_mtool_project1_outputs.php` は `definition_count=36`, `success_count=36`, `failure_count=0`

## Remaining `_legacy`

- shared root が無い状態で actual current save/reorder/job dispatch を実行する POST/action path
- `da_funcs_edit_proxy_single_target.php` の bulk save 自体
- `endpoint_test_json_ajax.php` の current job dispatch 自体
- `html-authoring` cluster
