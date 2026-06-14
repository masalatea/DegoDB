# 2026-05-13 HTML Authoring Save Bridge

## Summary

- `shared/project_html_repository.php` を追加し、current HTML authoring route から live `html` / `htmlParameter` / `htmlTemplate` / `dataclass` / `da` row を直接 read/write できるようにした。
- `shared/project_htmls_page.php` / `shared/project_html_detail_page.php` / `shared/project_html_parameters_page.php` は current list/detail/parameter CRUD を受ける page として更新した。
- `shared/project_output_html_module_generator.php` の `html_edit.php` / `html_parameter_edit.php` wrapper は legacy create/update/delete POST を current route action へ bridge するようにした。
- copied legacy reference (`shared/reference/mtool-legacy-html-catalog.json`) は既存 `html_key` 保持と template-parameter audit metadata に限定した。
- `scripts/show_html_db_rewrite_map.php` 上の `html-authoring` cluster は `available` になった。

## Files

- `shared/project_html_repository.php`
- `shared/legacy_html_reference.php`
- `scripts/export_legacy_html_reference.php`
- `shared/project_html_route_common.php`
- `shared/project_htmls_page.php`
- `shared/project_html_detail_page.php`
- `shared/project_html_parameters_page.php`
- `shared/project_output_html_module_generator.php`
- `scripts/show_html_db_rewrite_map.php`
- `docs/internal/html-db-rewrite-map.md`
- `docs/reports/2026/2026-0512-mtool-project1-output-parity-plan.md`
- `docs/internal/mtool-admin-roadmap.md`

## Verification

```zsh
php -l scripts/show_html_db_rewrite_map.php
php -l shared/legacy_html_reference.php
php -l scripts/export_legacy_html_reference.php
php -l shared/project_html_repository.php
php -l shared/project_html_route_common.php
php -l shared/project_htmls_page.php
php -l shared/project_html_detail_page.php
php -l shared/project_html_parameters_page.php
php -l shared/project_output_html_module_generator.php
php scripts/show_html_db_rewrite_map.php

docker compose exec -T web-admin php /var/www/scripts/create_project_output.php --project-key=MTOOL --source-output-key=HTML-DB --requested-by=codex --publish

php -l published/source-outputs/MTOOL/HTML-DB/htmls.php
php -l published/source-outputs/MTOOL/HTML-DB/html_edit.php
php -l published/source-outputs/MTOOL/HTML-DB/html_parameters.php
php -l published/source-outputs/MTOOL/HTML-DB/html_parameter_edit.php
rg -n "_legacy" published/source-outputs/MTOOL/HTML-DB/htmls.php published/source-outputs/MTOOL/HTML-DB/html_edit.php published/source-outputs/MTOOL/HTML-DB/html_parameters.php published/source-outputs/MTOOL/HTML-DB/html_parameter_edit.php

docker compose exec -T web-admin php /var/www/scripts/check_mtool_project1_outputs.php --project-key=MTOOL --requested-by=codex
```

確認結果:

- rewrite map: `status[available]=14`, `status[available-partial]=116`, `status[planned]=30`
- published `HTML-DB`: `artifact_key=20260513-050418-1c473014`, `source_file_count=229`
- published html wrappers: `php -l` 全件成功、`rg "_legacy"` hit `0`
- `check_mtool_project1_outputs.php`: `definition_count=36`, `success_count=36`, `failure_count=0`

## Remaining Focus

- canonical item mapping / malformed guard の整理
- remaining non-currentizable fallback の縮小
- `LanguageResource` の optional module / code-native replacement roadmap
