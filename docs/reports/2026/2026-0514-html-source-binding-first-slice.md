# 2026-05-14 HTML Source Binding First Slice

## Summary

- `project_html_source_bindings` を追加し、legacy `ProjectSourceOutputPID` ごとの current `source_output_key` / `module_source_ref` / `refresh_policy` / `source_of_truth` を current DB に保持できるようにした。
- `mtool/docker/mariadb/config-seed/033_project1_html_source_binding_seed.sql` を追加し、`MTOOL` の html bucket 21 件を bootstrap binding として初期化できるようにした。
- `project_htmls_page.php` に binding editor と binding 一覧を追加し、bootstrap candidate と persist 済み canonical binding を区別して表示するようにした。
- `project_html_detail_page.php` でも effective source ref と resolved source root を表示し、該当 bucket の binding editor へ戻れるようにした。

## Files

- `docker/mariadb/config-initdb/013_project_html_source_binding_metadata.sql`
- `mtool/docker/mariadb/config-seed/033_project1_html_source_binding_seed.sql`
- `mtool/app/project_html_source_binding_repository.php`
- `mtool/app/project_html_source_binding_repository_pdo.php`
- `mtool/app/project_html_source_binding_service.php`
- `mtool/app/project_htmls_page.php`
- `mtool/app/project_html_detail_page.php`
- `docs/internal/data-model.md`
- `docs/internal/legacy-new-db-mapping.md`
- `docs/internal/mtool-admin-roadmap.md`
- `docs/reports/2026/2026-0514-progress-snapshot.md`
- `docs/reports/2026/README.md`

## Verification

```zsh
php -l mtool/app/project_html_source_binding_repository_pdo.php
php -l mtool/app/project_html_source_binding_repository.php
php -l mtool/app/project_html_source_binding_service.php
php -l mtool/app/project_htmls_page.php
php -l mtool/app/project_html_detail_page.php

docker compose exec -T db-config sh -lc 'mariadb -uroot -pconfig_root_local_2026 config_app' < docker/mariadb/config-initdb/013_project_html_source_binding_metadata.sql
docker compose exec -T db-config sh -lc 'mariadb -uroot -pconfig_root_local_2026 config_app' < mtool/docker/mariadb/config-seed/033_project1_html_source_binding_seed.sql
docker compose exec -T db-config sh -lc 'mariadb -uroot -pconfig_root_local_2026 config_app -N -e "SHOW TABLES LIKE \"project_html_source_bindings\";"'
docker compose exec -T db-config sh -lc 'mariadb -uroot -pconfig_root_local_2026 config_app -N -e "SELECT COUNT(DISTINCT legacy_project_source_output_pid) FROM project_html_source_bindings WHERE project_id = (SELECT id FROM projects WHERE project_key = \"MTOOL\");"'
```

確認結果:

- 新規 repository / service / page 変更はすべて `php -l` を通過した。
- `project_html_source_bindings` table は `config_app` に作成され、`MTOOL` 向け bootstrap binding は `21` bucket 分入った。
- binding table 追加により、HTML list/detail は `project_source_outputs.notes` に埋めた bootstrap mapping を fallback にしつつ、persist 済み current binding を優先表示できる状態になった。
- `web-admin:/projects/MTOOL/html` は `HTML Source Bindings` / `Binding Editor` を返し、binding UI 自体は render した。
- 一方で `web-admin:/projects/MTOOL/html/{html_key}` の full verification は、現環境の `config_app` に legacy `html` / `htmlParameter` table が無いため未了である。これは今回の binding 追加とは別の既存 blocker である。

## Remaining Focus

- `project_html_source_bindings` を generator / remaining route の first lookup に寄せ、bootstrap metadata fallback を縮小する。
- `html` / `htmlParameter` row 自体の live legacy table 依存をどう current canonical へ移すかを次段で詰める。
- その後 `LanguageResource` 境界の固定へ進む。
