# 2026-05-13 Run AJAX Mismatch Currentization

## Summary

- `build_project_ajax.php`, `build_project_ajax_check_if_completed.php`, `compare_output_do_ajax.php` の generated wrapper で、project mismatch 時も `_legacy` worker に戻さず current handoff を返すようにした
- build run は HTML notice / compact JSON を、compare-output run は HTML notice を返し続けるので、legacy caller 互換の「handoff response」は維持される
- これにより run 系 wrapper の `_legacy` fallback はさらに減り、残る fallback は action bridge や shared-root lookup を要する箇所へ寄った

## Files

- `shared/project_output_html_module_generator.php`
- `docs/internal/html-db-rewrite-map.md`
- `docs/internal/mtool-admin-roadmap.md`
- `docs/reports/2026/2026-0512-mtool-project1-output-parity-plan.md`

## Verified

```zsh
php -l shared/project_output_html_module_generator.php
docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish
php -l published/source-outputs/MTOOL/HTML-DB/build_project_ajax.php
php -l published/source-outputs/MTOOL/HTML-DB/build_project_ajax_check_if_completed.php
php -l published/source-outputs/MTOOL/HTML-DB/compare_output_do_ajax.php
docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex
```

確認結果:

- `HTML-DB` publish artifact は `20260513-022715-5b6640d3`
- published wrapper 3 ファイルはすべて lint 通過
- `check_mtool_project1_outputs.php` は `definition_count=36`, `success_count=36`, `failure_count=0`
- published wrapper では project mismatch 時も `_legacy` を require せず、current handoff response を返すことを確認した

## Next

1. shared-root lookup を伴う action bridge で、本当に `_legacy/` が必要な guard をさらに切り分ける
2. `html-authoring` cluster の current route / canonical schema 設計へ進む
