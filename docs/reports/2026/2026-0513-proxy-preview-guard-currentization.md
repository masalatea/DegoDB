# 2026-05-13 Proxy Preview Guard Currentization

## Summary

- `da_proxy_custom.php` と `da_proxy_custom_func.php` の GET-only preview wrapper で、unsupported verb / project mismatch を `_legacy/` ではなく nearest current route へ縮退させた
- `da_edit_proxy_single_target.php` と `da_funcs_edit_proxy_single_setting.php` も同様に、unsupported verb / project mismatch を current `/projects/{project_key}/proxy/single` へ寄せるようにした
- これにより proxy preview list/functions 周辺の `_legacy/` 依存をさらに減らし、残る guard fallback は action bridge や shared-root lookup を伴う箇所へ寄った

## Files

- `shared/project_output_html_module_generator.php`
- `docs/internal/html-db-rewrite-map.md`
- `docs/reports/2026/2026-0512-mtool-project1-output-parity-plan.md`

## Verified

```zsh
php -l shared/project_output_html_module_generator.php
docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish
php -l published/source-outputs/MTOOL/HTML-DB/da_proxy_custom.php
php -l published/source-outputs/MTOOL/HTML-DB/da_proxy_custom_func.php
php -l published/source-outputs/MTOOL/HTML-DB/da_edit_proxy_single_target.php
php -l published/source-outputs/MTOOL/HTML-DB/da_funcs_edit_proxy_single_setting.php
docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex
```

確認結果:

- `HTML-DB` publish artifact は `20260513-012459-9ac00314`
- 対象 4 wrapper は published output 上でも lint 通過
- `check_mtool_project1_outputs.php` は `definition_count=36`, `success_count=36`, `failure_count=0`
- published wrapper では `da_proxy_custom.php` / `da_edit_proxy_single_target.php` の comment と実装が current route 縮退に更新され、`_legacy` 参照を持たないことを確認した

## Next

1. shared-root lookup が必要な action bridge で、本当に `_legacy/` を残す必要がある guard をさらに切り分ける
2. `html-authoring` cluster の current route 設計に進む
