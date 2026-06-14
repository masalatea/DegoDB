# 2026-05-13 DB Access Function Route Currentization

## Summary

- `shared/project_output_html_module_generator.php` の db-access-function wrapper を更新し、invalid GET deep link と unsupported verb を current route 側へ縮退させる範囲を広げた。
- `da_func_source.php` / `da_func_select_where.php` / `da_func_select_target_fields.php` / `da_func_select_having.php` / `da_func_update_delete_where.php` / `da_func_update_delete_where_input_aid.php` / `da_func_insert_target_fields.php` / `da_func_update_target_fields.php` は project mismatch / unknown `DAFuncPID` / unknown `DAPID` / unsupported verb でも nearest current function/list route へ寄せる。
- `da_func_move.php` / `da_func_sort_order_edit.php` / `da_func_select_where_edit.php` / `da_func_update_delete_where_edit.php` / `da_func_select_target_field_edit.php` / `da_func_select_having_edit.php` / `da_func_insert_target_field_edit.php` / `da_func_update_target_field_edit.php` は legacy POST/save semantics を維持しつつ、invalid GET deep link だけ current move/detail/designer list route へ寄せる。
- `da_func_select_where_input_aid.php` / `da_func_select_where_change_order.php` / `da_func_update_delete_where_change_order.php` は invalid GET と unsupported verb を current route へ寄せ、interactive filter state や `NewSortOrder` / `doReset` の action semantics だけ `_legacy/` fallback を残す。
- `da_func_edit.php` は blank add flow で stable current key をまだ決められないため、known edit deep link の current detail redirect のみ吸収し、blank add 自体は `_legacy/` fallback を残した。

## Files

- `shared/project_output_html_module_generator.php`
- `docs/internal/html-db-rewrite-map.md`
- `docs/internal/mtool-admin-roadmap.md`
- `docs/reports/2026/2026-0513-db-access-function-route-currentization.md`

## Verified

```zsh
php -l shared/project_output_html_module_generator.php
docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex
```

確認結果:

- 最新の `HTML-DB` publish artifact は `20260513-034603-1188d624`
- 再確認で生成した non-published `HTML-DB` artifact は `20260513-035312-ed404240`
- published `da_func_*` wrapper lint は slice 作業時に通過済み
- `check_mtool_project1_outputs.php` は `definition_count=36`, `success_count=36`, `failure_count=0`

## Remaining `_legacy`

- `da_func_edit.php` の blank add flow
- designer item-level canonical mapping がまだ無い `da_func_*_edit.php` deep link
- interactive filter / candidate selection state
- `NewSortOrder` / `doReset` を含む reorder action semantics
- shared-root lookup failure などの non-currentizable guard fallback

## Next Focus

- `da_funcs_edit_proxy_single_target.php`
- `da_funcs_edit_proxy_single_setting_edit.php`
- `da_proxy_custom_edit.php`
- `da_proxy_custom_func_edit.php`
- `da_proxy_custom_func_change_order.php`
- `endpoint_test_json_ajax.php`
