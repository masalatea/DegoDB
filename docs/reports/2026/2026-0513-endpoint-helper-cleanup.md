# 2026-05-13 Endpoint Helper Cleanup

## Summary

- `da_func_endpoint.php` は generated wrapper から current function endpoint route へ直接 handoff するようにし、unknown / mismatch な legacy ID は nearest current `/db-access` path へ縮退させた
- `da_proxy_custom_endpoint.php` も current custom proxy endpoint preview へ直接 handoff するようにし、legacy `ReleaseType` は current `source_output_key` focus に変換する
- `endpoint_common_include.php` / `endpoint_lib_include.php` / `endpoint_test_json_client_include.php` は current handoff shim に置き換え、published main path から `_legacy` / `/home/...` 依存を外した
- これにより proxy / endpoint 系の主残件は endpoint helper cleanup ではなく、project mismatch / unsupported verb / shared-root lookup failure のような non-currentizable guard fallback 整理へ移った

## Files

- `shared/project_output_html_module_generator.php`
- `scripts/show_html_db_rewrite_map.php`
- `docs/internal/html-db-rewrite-map.md`
- `docs/internal/mtool-admin-roadmap.md`
- `docs/reports/2026/2026-0512-mtool-project1-output-parity-plan.md`

## Verified

```zsh
docker compose ps
php -l shared/project_single_proxy_page.php
php -l shared/project_db_access_function_detail_page.php
php -l shared/project_output_html_module_generator.php
php -l scripts/show_html_db_rewrite_map.php
php scripts/show_html_db_rewrite_map.php
docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish
docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex
php -l published/source-outputs/MTOOL/HTML-DB/da_func_endpoint.php
php -l published/source-outputs/MTOOL/HTML-DB/da_proxy_custom_endpoint.php
php -l published/source-outputs/MTOOL/HTML-DB/endpoint_common_include.php
php -l published/source-outputs/MTOOL/HTML-DB/endpoint_lib_include.php
php -l published/source-outputs/MTOOL/HTML-DB/endpoint_test_json_client_include.php
```

確認結果:

- Docker services は `db-config` / `db-lab` / `web-admin` / `web-lab` とも healthy
- `HTML-DB` publish artifact は `20260513-011729-a4fffdaf`
- published `HTML-DB` の endpoint wrapper / helper 5 ファイルはすべて lint 通過
- `check_mtool_project1_outputs.php` は `definition_count=36`, `success_count=36`, `failure_count=0`
- published helper files では `_legacy` / `/home/...` の runtime 依存を確認しなかった

## Next

1. project mismatch / unsupported verb / shared-root lookup failure のような non-currentizable guard fallback をさらに絞る
2. `html-authoring` cluster の current route / canonical schema 設計へ進む

## Notes

- endpoint helper file 群は完全削除ではなく shim 化であり、legacy include 呼び出し元から見た fatal 回避と current handoff を両立する
- browser 経由の end-to-end 実打ちはまだしておらず、この slice では lint / publish / parity regression / generated file inspection までを確認した
