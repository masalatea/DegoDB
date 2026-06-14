# 2026-05-13 Proxy Shared-Root Guard Reduction

## Summary

- `shared/project_output_html_module_generator.php` の proxy wrappers を更新し、`sharedRoot === ''` や unsupported verb / malformed guard で `_legacy/` に戻る経路を current page redirect へ置き換えた
- 対象は `da_funcs_edit_proxy_single_target.php`, `da_funcs_edit_proxy_single_setting_edit.php`, `da_proxy_custom_edit.php`, `da_proxy_custom_func_edit.php`, `da_proxy_custom_func_change_order.php`
- `shared/project_db_access_function_detail_page.php` も query string の `bridge_errors` を受けるようにし、single proxy auth save wrapper から redirect error を表示できるようにした
- `ProjectPID` mismatch も current handoff へ寄せ、published `HTML-DB` の proxy / endpoint wrappers から `_legacy` 参照が消えた
- publish artifact `20260513-042347-f41487a2` と `check_mtool_project1_outputs.php` `36/36 success` を確認した

## Files

- `shared/project_db_access_function_detail_page.php`
- `shared/project_output_html_module_generator.php`
- `docs/internal/html-db-rewrite-map.md`
- `docs/internal/mtool-admin-roadmap.md`
- `docs/reports/2026/2026-0512-mtool-project1-output-parity-plan.md`
- `docs/reports/2026/2026-0513-proxy-shared-root-guard-reduction.md`

## Verified

```zsh
php -l shared/project_db_access_function_detail_page.php
php -l shared/project_output_html_module_generator.php
docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish
php -l published/source-outputs/MTOOL/HTML-DB/da_funcs_edit_proxy_single_target.php
php -l published/source-outputs/MTOOL/HTML-DB/da_funcs_edit_proxy_single_setting_edit.php
php -l published/source-outputs/MTOOL/HTML-DB/da_proxy_custom_edit.php
php -l published/source-outputs/MTOOL/HTML-DB/da_proxy_custom_func_edit.php
php -l published/source-outputs/MTOOL/HTML-DB/da_proxy_custom_func_change_order.php
php -l published/source-outputs/MTOOL/HTML-DB/endpoint_test_json_ajax.php
docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex
```

確認結果:

- `HTML-DB` publish artifact: `20260513-042347-f41487a2`
- published proxy / endpoint wrappers 6 本は lint 通過
- `check_mtool_project1_outputs.php`: `definition_count=36`, `success_count=36`, `failure_count=0`
- published `HTML-DB` の proxy / endpoint wrappers から `_legacy` 参照が消えた

## Remaining

- `html-authoring` cluster の current route / wrapper seam
- canonical item mapping / malformed guard の一般整理
