# Proxy POST Unknown ID Current Errors

## Summary

- `da_funcs_edit_proxy_single_target.php`
- `da_funcs_edit_proxy_single_setting_edit.php`
- `da_proxy_custom_edit.php`
- `da_proxy_custom_func_edit.php`
- `da_proxy_custom_func_change_order.php`

上記 wrapper の unknown legacy ID POST は、`_legacy/` fallback ではなく current page 側の validation / bridge error で止める方針へ寄せた。

## Changes

- `shared/project_proxy_route_common.php` に `bridge_errors` 正規化 helper を追加した。
- `shared/project_single_proxy_page.php` / `shared/project_db_access_function_detail_page.php` / `shared/project_custom_proxies_page.php` / `shared/project_custom_proxy_detail_page.php` / `shared/project_custom_proxy_functions_page.php` は `bridge_errors` を 422 error として表示できるようにした。
- destructive action (`delete-step`, `reset-step-order`, custom proxy `delete`) は `bridge_errors` がある場合に実行しないようにした。
- single proxy bulk target wrapper は unknown checkbox pair を legacy 同様に無視し、unknown `DAPID` POST は current page の invalid key error に寄せる。
- single proxy auth wrapper は unknown `DAFuncPID` POST を current single proxy page の bridge error に寄せ、unknown `SingleProxy_SingleGetFuncPID` は current function detail で bridge error を出して保存を止める。
- custom proxy edit wrapper は unknown `daCustomProxyPID` POST を current list/detail 側へ寄せ、unknown `TargetProjectSourceOutputPIDList[]` は legacy 同様に無視する。unknown `SingleGetFuncPID` は bridge error で止める。
- custom proxy step/change-order wrapper は unknown `daCustomProxyPID` POST を current list page へ寄せ、unknown step/order PID は current functions page の validation/not-found に寄せる。

## Verification

- `php -l shared/project_proxy_route_common.php`
- `php -l shared/project_single_proxy_page.php`
- `php -l shared/project_db_access_function_detail_page.php`
- `php -l shared/project_custom_proxies_page.php`
- `php -l shared/project_custom_proxy_detail_page.php`
- `php -l shared/project_custom_proxy_functions_page.php`
- `php -l shared/project_output_html_module_generator.php`
- `docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish`
  - published artifact: `20260513-003928-474f2574`
- `docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex`
  - `definition_count=36`
  - `success_count=36`
  - `failure_count=0`
