# 2026-05-13 Redirect-Only Guard Currentization

## Summary

- `project_source_output.php`, `da.php`, `da_source.php`, `compare_output_do.php`, `build_project.php` の redirect-only wrapper で、project mismatch / unsupported verb を `_legacy/` ではなく current list/run route へ縮退させた
- proxy preview wrapper に続いて、source-output / db-access core / compare-output run / build run の GET entry 側 `_legacy` 依存をさらに削った
- 残る guard fallback は edit/save や reorder/sync の action semantics、shared-root lookup を伴う bridge へ寄っている

## Files

- `shared/project_output_html_module_generator.php`
- `scripts/show_html_db_rewrite_map.php`
- `docs/internal/html-db-rewrite-map.md`
- `docs/internal/mtool-admin-roadmap.md`
- `docs/reports/2026/2026-0512-mtool-project1-output-parity-plan.md`

## Verified

```zsh
php -l shared/project_output_html_module_generator.php
docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish
php -l published/source-outputs/MTOOL/HTML-DB/project_source_output.php
php -l published/source-outputs/MTOOL/HTML-DB/da.php
php -l published/source-outputs/MTOOL/HTML-DB/da_source.php
php -l published/source-outputs/MTOOL/HTML-DB/compare_output_do.php
php -l published/source-outputs/MTOOL/HTML-DB/build_project.php
docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex
```

確認結果:

- `HTML-DB` publish artifact は `20260513-022210-5cc99ed4`
- published wrapper 5 ファイルはすべて lint 通過
- `check_mtool_project1_outputs.php` は `definition_count=36`, `success_count=36`, `failure_count=0`
- published wrapper では `_legacy` 参照を持たず、current list/run route へ直接 handoff することを確認した

## Next

1. shared-root lookup を伴う action bridge で、本当に `_legacy/` が必要な guard をさらに切り分ける
2. `html-authoring` cluster の current route / canonical schema 設計へ進む
