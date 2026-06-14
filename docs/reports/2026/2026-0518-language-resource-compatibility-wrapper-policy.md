# 2026-05-18 Language Resource Compatibility Wrapper Policy

## Summary

- `LanguageResource` の migration/debug CLI は `mtool/scripts/debug/language_resource/` 配下を canonical path に固定した。
- 旧 `mtool/scripts/*.php` 直下の wrapper は 4 本だけを暫定維持し、historical command 互換以外の責務を持たせない方針を明文化した。
- stable doc と source comment を更新し、wrapper の削除条件を次の cleanup 判断に使える状態にした。

## Inventory

- `mtool/scripts/sync_project_language_resource_from_file_tree.php` -> `mtool/scripts/debug/language_resource/sync_project_language_resource_from_file_tree.php`
- `mtool/scripts/inspect_language_resource_db_residual_rows.php` -> `mtool/scripts/debug/language_resource/inspect_language_resource_db_residual_rows.php`
- `mtool/scripts/retire_project_language_resource_db_rows.php` -> `mtool/scripts/debug/language_resource/retire_project_language_resource_db_rows.php`
- `mtool/scripts/drop_project_language_resource_db_tables.php` -> `mtool/scripts/debug/language_resource/drop_project_language_resource_db_tables.php`
- `mtool/scripts/check_language_resource_db_retirement_readiness.php` は current readiness entrypoint であり、wrapper inventory には含めない。

## Decision

- keep:
  - 上記 4 wrapper は historical handoff / local shell history / 手元 runbook 互換のためにだけ残す。
- do not add:
  - 新しい stable doc / Make target / automation に old top-level path を追加しない。
- keep canonical:
  - migration/debug CLI の正本は `mtool/scripts/debug/language_resource/*.php` とする。
- keep contract centralized:
  - stdout/stderr contract は canonical script 側に寄せ、wrapper は thin `require_once` に維持する。

## Exit Conditions

- stable doc / Make target / automation から old top-level path 参照が消える。
- handoff / resume prompt の copy-paste が canonical debug path を前提にする。
- readiness check と必要な smoke command が wrapper なしでも green を維持する。
- migration/debug 期間が終わり、DB bridge 自体の削除範囲を確定できる。

## Changed Stable Docs

- `mtool/scripts/debug/language_resource/README.md`
- `mtool/resources/README.md`
- `docs/internal/language-resource-separation.md`

## Verification

- `make mtool-lang-res-file-tree-export`
- `make mtool-lang-res-file-tree-check`
- `make mtool-html-db-lang-res-wrapper-check`
- `php mtool/scripts/debug/language_resource/sync_project_language_resource_from_file_tree.php --help`
- `php mtool/scripts/sync_project_language_resource_from_file_tree.php --help`
- `APP_DB_HOST=127.0.0.1 APP_DB_PORT=33061 APP_DB_NAME=config_app APP_DB_USER=config_app APP_DB_PASSWORD=config_app_local_2026 APP_CONFIG_DB_HOST=127.0.0.1 APP_CONFIG_DB_PORT=33061 APP_CONFIG_DB_NAME=config_app APP_CONFIG_DB_USER=config_app APP_CONFIG_DB_PASSWORD=config_app_local_2026 php mtool/scripts/check_language_resource_db_retirement_readiness.php --project-key=MTOOL --legacy-project-pid=1 --docroot=<repo-root>/work/source-outputs/MTOOL/HTML-DB`
- `php -l mtool/scripts/sync_project_language_resource_from_file_tree.php`
- `php -l mtool/scripts/inspect_language_resource_db_residual_rows.php`
- `php -l mtool/scripts/retire_project_language_resource_db_rows.php`
- `php -l mtool/scripts/drop_project_language_resource_db_tables.php`

## Result

- resume baseline の確認系は今回も通り、DB access ありの readiness check では `ready=true` を再確認した。
- wrapper inventory と removal conditions が stable doc / source の両方に残ったため、次回は「削るか残すか」を感覚ではなく条件で判断できる。
