# 2026-05-13 End Of Day Handoff

> Latest restart baseline for the 2026-05-14 session. Use this handoff as the source of truth; `docs/reports/2026/2026-0513-resume-prompt.md` is a derived copy/paste helper; `docs/reports/2026/2026-0512-end-of-day-handoff.md` is superseded.

## Summary

- `shared/project_source_output_new_page.php` を追加済みの current `/projects/{project_key}/source-outputs/new` handoff 受け皿に対し、legacy add-flow の prefill quality を上げた
- current `/source-outputs/new` は `ClassType` / `ProxyBaseURL` / mapped target server source output を見て `artifact_strategy` / `target_binding_type` / runtime/template default を初期推定する
- さらに legacy dir / URL から safe に読める場合は tentative `source_output_key` / `name` も補うようにした
- tentative identity helper は `html`、proxy、`LanguageResource`、`DBAccess` を対象にし、既存 key と衝突する候補は自動投入しない
- `LanguageResource` だけは duplicate dir を許容するため、`-ALT` が未使用なら fallback key 候補として提案する
- `project_source_output_edit.php` / `project_source_output_change_order.php` の published wrapper から `_legacy/` fallback は既に消えており、source-output cluster の main path は current handoff ベースに乗っている
- docs / rewrite map も「safe な場合だけ tentative suggestion を出し、duplicate / ambiguous case は manual confirmation に残す」方針へ更新した

## Current Position

- `MTOOL` Project 1 parity の last full result は維持できている
- latest published `HTML-DB` artifact は `20260513-055055-ffd968a6`
- last full parity regression は `definition_count=36`, `success_count=36`, `failure_count=0`
- source-outputs cluster は `available-partial` のまま
- partial の主因は以下
  - `CustomFileExtention` など legacy-only source-output fields が current schema に未移植
  - duplicate / ambiguous な legacy add-flow では stable `source_output_key` / `name` を自動確定できない
  - safe でないケースは current `/new` page 上の manual confirmation に残している

## Files Touched In This Slice

- `shared/project_source_output_new_page.php`
- `docs/reports/2026/2026-0513-source-output-bridge.md`
- `docs/internal/html-db-rewrite-map.md`
- `docs/reports/2026/2026-0512-mtool-project1-output-parity-plan.md`
- `docs/internal/mtool-admin-roadmap.md`
- `scripts/show_html_db_rewrite_map.php`

## Verified

```zsh
php -l shared/project_source_output_new_page.php
php -l shared/project_output_html_module_generator.php
php -l scripts/show_html_db_rewrite_map.php
php scripts/show_html_db_rewrite_map.php | sed -n '145,149p'

docker compose exec -T web-admin php -r 'require "/var/www/shared/project_source_output_new_page.php"; $input = app_source_output_form_defaults(); $input["class_type"] = "html"; $input["source_output_dir"] = "/legacy/ftp/www/dev.matsuesoft.com/settings/dbuser"; $hints = ["legacy_target_server_source_output_key" => "", "legacy_source_output_dir" => $input["source_output_dir"], "legacy_source_template_dir" => ""]; var_export(app_project_source_output_new_bridge_html_identity("Mtool", $input, $hints));'
docker compose exec -T web-admin php -r 'require "/var/www/shared/project_source_output_new_page.php"; $input = app_source_output_form_defaults(); $input["class_type"] = "DBaaSProxyClient"; $input["program_language"] = "cs"; $input["proxy_base_url"] = "https://dev.matsuesoft.com/proxy_dbimport"; $hints = ["legacy_target_server_source_output_key" => "DBIMPORT-PROXY-SERVER", "legacy_source_output_dir" => "", "legacy_source_template_dir" => ""]; var_export(app_project_source_output_new_bridge_proxy_identity("Mtool", $input, $hints));'
docker compose exec -T web-admin php -r 'require "/var/www/shared/project_source_output_new_page.php"; $input = app_source_output_form_defaults(); $input["class_type"] = "LanguageResource"; $input["program_language"] = "php"; $input["source_output_dir"] = "/legacy/ftp/www/ja.matsuesoft.com/lib"; $hints = ["legacy_target_server_source_output_key" => "", "legacy_source_output_dir" => $input["source_output_dir"], "legacy_source_template_dir" => ""]; var_export(app_project_source_output_new_bridge_language_resource_identity("Mtool", $input, $hints, ["LANGRES-PHP-JA-WEB-LIB" => true]));'
```

確認結果:

- `shared/project_source_output_new_page.php`: `php -l` 成功
- `scripts/show_html_db_rewrite_map.php`: `php -l` 成功、source-outputs cluster の next 文言が tentative suggestion 前提へ更新済み
- helper smoke:
  - html candidate: `HTML-SETTINGS-DBUSER` / `Mtool HTML Settings DBUser Module`
  - proxy candidate: `DBIMPORT-PROXY-CLIENT` / `Mtool DB Import Proxy Client`
  - language resource duplicate fallback: `LANGRES-PHP-JA-WEB-LIB-ALT` / `Mtool Language Resource PHP Ja Web Lib Alt`

補足:

- この slice は current admin page と docs の更新のみで、published wrapper / artifact を変更していない
- そのため publish / parity regression は再実行していない
- 最新の full result は `artifact_key=20260513-055055-ffd968a6` と `36/36 success` を参照する

## Tomorrow First Step

最初の着手は、source-output add-flow の duplicate / ambiguous case を current `/source-outputs/new` でどう扱うかを詰めること。

方針:

1. legacy add-flow で tentative suggestion をまだ安全に出せない case を列挙する
2. blank のまま manual input に戻すべき case と、warning 付き candidate 表示まで許容できる case を分ける
3. `shared/project_source_output_new_page.php` に warning / candidate policy を実装する
4. generator / wrapper まで触ったら `HTML-DB` を再 publish する
5. publish 変更があれば `check_mtool_project1_outputs.php` で `36/36` を再確認する

## Resume Commands

```zsh
cd <repo-root>

docker compose ps

php -l shared/project_source_output_new_page.php
php -l scripts/show_html_db_rewrite_map.php
php scripts/show_html_db_rewrite_map.php | sed -n '145,149p'

sed -n '140,760p' shared/project_source_output_new_page.php
sed -n '1,260p' 'original-codes/dev web/db/project_source_output_edit_include.php'
sed -n '1,220p' docker/mariadb/config-sample-seed/mtool-core/028_project1_html_source_output_seed.sql
sed -n '1,220p' docker/mariadb/config-sample-seed/mtool-core/030_project1_language_resource_source_output_seed.sql

sed -n '1,220p' docs/reports/2026/2026-0513-source-output-bridge.md
sed -n '198,210p' docs/reports/2026/2026-0512-mtool-project1-output-parity-plan.md
sed -n '60,66p' docs/internal/html-db-rewrite-map.md
sed -n '560,568p' docs/internal/mtool-admin-roadmap.md

docker compose exec -T web-admin php -r 'require "/var/www/shared/project_source_output_new_page.php"; $input = app_source_output_form_defaults(); $input["class_type"] = "html"; $input["source_output_dir"] = "/legacy/ftp/www/dev.matsuesoft.com/settings/dbuser"; $hints = ["legacy_target_server_source_output_key" => "", "legacy_source_output_dir" => $input["source_output_dir"], "legacy_source_template_dir" => ""]; var_export(app_project_source_output_new_bridge_html_identity("Mtool", $input, $hints));'
docker compose exec -T web-admin php -r 'require "/var/www/shared/project_source_output_new_page.php"; $input = app_source_output_form_defaults(); $input["class_type"] = "LanguageResource"; $input["program_language"] = "php"; $input["source_output_dir"] = "/legacy/ftp/www/ja.matsuesoft.com/lib"; $hints = ["legacy_target_server_source_output_key" => "", "legacy_source_output_dir" => $input["source_output_dir"], "legacy_source_template_dir" => ""]; var_export(app_project_source_output_new_bridge_language_resource_identity("Mtool", $input, $hints, ["LANGRES-PHP-JA-WEB-LIB" => true]));'
```

## Restart Prompt

同内容のコピペ用 prompt は `docs/reports/2026/2026-0513-resume-prompt.md` にも置いた。

```text
<repo-root> の MTOOL rewrite 作業を再開してください。

今日の到達点:
- source-output の blank add-flow は current `/projects/{project_key}/source-outputs/new` handoff 化済み
- current `/source-outputs/new` は `ClassType` / `ProxyBaseURL` / mapped target server source output を見て `artifact_strategy` / `target_binding_type` / runtime/template default を初期推定する
- legacy dir / URL から safe に読める場合は tentative `source_output_key` / `name` も prefill する
- tentative identity helper は `html`、proxy、`LanguageResource`、`DBAccess` を対象にし、既存 key と衝突する候補は自動投入しない
- `LanguageResource` だけは duplicate dir を許容するため、`-ALT` が未使用なら fallback key 候補として提案する
- source-output の published wrapper から `_legacy` fallback は消えている
- HTML-DB 最新 publish artifact は `20260513-055055-ffd968a6`
- last full parity regression は `check_mtool_project1_outputs.php` で `36/36 success`
- 直近の tentative key/name slice は current admin page と docs だけを更新しており、publish / parity regression は再実行していない

前提:
- original-codes/ は runtime や generator の直接入力には使わない
- copied snapshot / placeholder / tmp bridge は暫定対応としてのみ許容
- published/source-outputs/ が Mtool output の昇格先
- custom/source-outputs/ は companion layer
- PSR-4 対応 namespace / directory layout は最終段の TODO とし、当面は parity 優先で pragmatic な file placement を許容する
- source-outputs cluster は still `available-partial` で、主因は legacy-only fields と duplicate / ambiguous な add-flow candidate 確定である

次のタスク:
1. legacy add-flow で tentative suggestion をまだ安全に出せない duplicate / ambiguous case を列挙する
2. current `/source-outputs/new` で blank のまま manual input に戻すべき case と、warning 付き candidate 表示まで許容できる case を分ける
3. `shared/project_source_output_new_page.php` に warning / candidate policy を実装する
4. generator / wrapper まで触ったら HTML-DB を再 publish する
5. publish 変更があれば `check_mtool_project1_outputs.php` で `36/36 success` を再確認する

最初に以下を実行してください:
- docker compose ps
- php -l shared/project_source_output_new_page.php
- php -l scripts/show_html_db_rewrite_map.php
- php scripts/show_html_db_rewrite_map.php | sed -n '145,149p'
- sed -n '140,760p' shared/project_source_output_new_page.php
- sed -n '1,260p' 'original-codes/dev web/db/project_source_output_edit_include.php'
- sed -n '1,220p' docker/mariadb/config-sample-seed/mtool-core/028_project1_html_source_output_seed.sql
- sed -n '1,220p' docker/mariadb/config-sample-seed/mtool-core/030_project1_language_resource_source_output_seed.sql
- sed -n '1,220p' docs/reports/2026/2026-0513-source-output-bridge.md
- sed -n '198,210p' docs/reports/2026/2026-0512-mtool-project1-output-parity-plan.md
- sed -n '60,66p' docs/internal/html-db-rewrite-map.md
- sed -n '560,568p' docs/internal/mtool-admin-roadmap.md

参照ドキュメント:
- docs/reports/2026/2026-0513-end-of-day-handoff.md
- docs/reports/2026/2026-0513-source-output-bridge.md
- docs/reports/2026/2026-0512-mtool-project1-output-parity-plan.md
- docs/internal/html-db-rewrite-map.md
- docs/internal/mtool-admin-roadmap.md
```
