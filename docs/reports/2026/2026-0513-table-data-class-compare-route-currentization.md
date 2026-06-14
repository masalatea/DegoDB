# 2026-05-13 Table/DataClass/Compare Route Currentization

## Summary

- `shared/project_output_html_module_generator.php` の table / data-class / compare-output wrapper を更新し、GET-only list/detail route で残っていた `_legacy` fallback を current list/base route へ縮退させた。
- `dbtables.php`, `dbtable_columns.php`, `dataclasses.php`, `dataclass_fields.php` は unsupported verb / project mismatch / unknown legacy PID でも nearest current list/detail route へ redirect する。
- `dbtable_edit.php`, `dbtable_column_edit.php`, `dataclass_edit.php`, `dataclass_field_edit.php`, `compare_output_edit.php`, `compare_output_additional_path_edit.php` は legacy POST/save semantics を維持しつつ、invalid GET deep link だけ current list/base route へ寄せる。
- `compare_output.php` と `compare_output_additional_path.php` は project mismatch / unknown `CompareOutputPID` / unsupported verb でも current settings list へ縮退するようにした。

## Files

- `shared/project_output_html_module_generator.php`
- `docs/internal/html-db-rewrite-map.md`
- `docs/reports/2026/2026-0512-mtool-project1-output-parity-plan.md`
- `docs/internal/mtool-admin-roadmap.md`

## Verified

```zsh
php -l shared/project_output_html_module_generator.php
docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish
php -l published/source-outputs/MTOOL/HTML-DB/dbtables.php
php -l published/source-outputs/MTOOL/HTML-DB/dbtable_columns.php
php -l published/source-outputs/MTOOL/HTML-DB/dbtable_edit.php
php -l published/source-outputs/MTOOL/HTML-DB/dbtable_column_edit.php
php -l published/source-outputs/MTOOL/HTML-DB/dataclasses.php
php -l published/source-outputs/MTOOL/HTML-DB/dataclass_fields.php
php -l published/source-outputs/MTOOL/HTML-DB/dataclass_edit.php
php -l published/source-outputs/MTOOL/HTML-DB/dataclass_field_edit.php
php -l published/source-outputs/MTOOL/HTML-DB/compare_output.php
php -l published/source-outputs/MTOOL/HTML-DB/compare_output_edit.php
php -l published/source-outputs/MTOOL/HTML-DB/compare_output_additional_path.php
php -l published/source-outputs/MTOOL/HTML-DB/compare_output_additional_path_edit.php
docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex
```

確認結果:

- `HTML-DB` publish artifact: `20260513-031508-eb204c9b`
- published wrapper 12 ファイルはすべて lint 通過
- `check_mtool_project1_outputs.php` は `definition_count=36`, `success_count=36`, `failure_count=0`

## Remaining `_legacy`

- table / data-class / compare-output の edit/save POST semantics
- blank add flow のまま current route key を決められない deep link
- shared-root lookup failure などの non-currentizable guard fallback
- `html-authoring` cluster
