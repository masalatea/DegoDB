# 2026-05-13 Proxy GET Unknown ID Redirects

## Summary

- `shared/project_output_html_module_generator.php` を更新し、proxy 系 wrapper の GET/HEAD unknown PID deep link を current list / functions route へ redirect するようにした
- 対象は single proxy (`da_edit_proxy_single_target.php`, `da_funcs_edit_proxy_single_setting_edit.php` 系) と custom proxy (`da_proxy_custom*.php`) の generated wrapper
- POST/action request は従来どおり current bridge を優先し、unknown ID を current action へ安全に変換できない場合だけ `_legacy/` fallback を残す
- rewrite map / roadmap / parity plan も「残件は POST/action unknown ID fallback と endpoint helper cleanup」という表現へ更新した

## Files

- `shared/project_output_html_module_generator.php`
- `scripts/show_html_db_rewrite_map.php`
- `docs/internal/html-db-rewrite-map.md`
- `docs/internal/mtool-admin-roadmap.md`
- `docs/reports/2026/2026-0512-mtool-project1-output-parity-plan.md`

## Verified

```zsh
php -l shared/project_output_html_module_generator.php
php -l scripts/show_html_db_rewrite_map.php
php scripts/show_html_db_rewrite_map.php
docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish
docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex
php -l published/source-outputs/MTOOL/HTML-DB/da_edit_proxy_single_target.php
php -l published/source-outputs/MTOOL/HTML-DB/da_funcs_edit_proxy_single_setting_edit.php
php -l published/source-outputs/MTOOL/HTML-DB/da_proxy_custom_endpoint.php
php -l published/source-outputs/MTOOL/HTML-DB/da_proxy_custom_edit.php
php -l published/source-outputs/MTOOL/HTML-DB/da_proxy_custom_func_change_order.php
```

確認結果:

- `HTML-DB` publish artifact: `20260513-002208-8161d5ac`
- parity regression: `definition_count=36`, `success_count=36`, `failure_count=0`
- published proxy wrapper 群の lint はすべて通過

## Remaining

1. proxy / endpoint の POST/action unknown ID fallback をさらに縮める
2. `endpoint_common_include.php` / `endpoint_lib_include.php` / `endpoint_test_json_client_include.php` の cleanup 方針を決める
3. `html-authoring` cluster の current route 設計へ進む

## Notes

- この slice では GET/HEAD deep link のみを current redirect 側へ寄せた。legacy PID を current canonical key へ安全に変換できない POST/action request はまだ `_legacy/` fallback を維持している
- `project_source_output` / `compare-output` など他 cluster の unknown PID fallback まではまだ触っていない
