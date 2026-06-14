# 2026-05-15 Language Resource Debug Script Relocation

## 概要

- `LanguageResource` の DB bridge / retirement 実装本体を `mtool/scripts/debug/language_resource/` 配下へ移した。
- `mtool/scripts/` 直下の旧 path は silent compatibility wrapper としてだけ残し、通常参照先を debug subtree に固定した。
- `check_language_resource_db_retirement_readiness.php` の allowlist も新 path に合わせ、DB bridge reference の隔離判定を維持した。

## 変更

- moved
  - `mtool/scripts/debug/language_resource/sync_project_language_resource_from_file_tree.php`
  - `mtool/scripts/debug/language_resource/inspect_language_resource_db_residual_rows.php`
  - `mtool/scripts/debug/language_resource/retire_project_language_resource_db_rows.php`
  - `mtool/scripts/debug/language_resource/drop_project_language_resource_db_tables.php`
  - `mtool/scripts/debug/language_resource/lib/project_language_resource_db_bridge.php`
  - `mtool/scripts/debug/language_resource/lib/project_language_resource_sync_service.php`
- compatibility wrappers
  - 旧 `mtool/scripts/*.php` path には `require_once` だけの wrapper を残した。
- docs
  - `mtool/resources/README.md`
  - `docs/internal/language-resource-separation.md`
  - `docs/internal/mtool-admin-roadmap.md`
  - `docs/reports/2026/2026-0515-language-resource-file-source-of-truth-plan.md`
  - `docs/reports/2026/2026-0515-language-resource-debug-bridge-hardening.md`

## 検証

- `php -l mtool/scripts/check_language_resource_db_retirement_readiness.php`
- `php -l mtool/scripts/debug/language_resource/lib/project_language_resource_db_bridge.php`
- `php -l mtool/scripts/debug/language_resource/lib/project_language_resource_sync_service.php`
- `php -l mtool/scripts/debug/language_resource/sync_project_language_resource_from_file_tree.php`
- `php -l mtool/scripts/debug/language_resource/inspect_language_resource_db_residual_rows.php`
- `php -l mtool/scripts/debug/language_resource/retire_project_language_resource_db_rows.php`
- `php -l mtool/scripts/debug/language_resource/drop_project_language_resource_db_tables.php`
- `php mtool/scripts/debug/language_resource/sync_project_language_resource_from_file_tree.php --help`
- `php mtool/scripts/sync_project_language_resource_from_file_tree.php --help`
- `php mtool/scripts/debug/language_resource/sync_project_language_resource_from_file_tree.php --project-key=MTOOL`
  - local `APP_DB_*` / `APP_CONFIG_DB_*` を `127.0.0.1:33061 / config_app` に上書きした状態で実行した。
  - `ok = true`
  - `mode = debug-db-bridge`
  - `DB preview を取得できませんでした: SQLSTATE[HY000] [2002] Operation not permitted`
- `php mtool/scripts/sync_project_language_resource_from_file_tree.php --project-key=MTOOL`
  - 同じ local DB overrides 付きで実行した。
  - wrapper 経由でも同じ JSON を返す。
- `php mtool/scripts/check_language_resource_db_retirement_readiness.php --project-key=MTOOL --legacy-project-pid=1 --docroot=<repo-root>/work/source-outputs/MTOOL/HTML-DB`
  - 同じ local DB overrides 付きで実行した。
  - `db_bridge_references_isolated = pass`
  - `db_bridge_references` は `mtool/scripts/debug/language_resource/` 配下と readiness CLI 自身だけを返す。
  - DB 接続は sandbox 制約で `skip` だが、path relocation と allowlist 判定は通る。

## 含意

- `LanguageResource` の DB bridge は code path 上でも debug 専用 subtree に閉じた。
- 旧 path は壊さず残したため、既存メモや手元の操作手順は当面そのままでも動く。
- current docs では新 path を正本として案内し、compatibility wrapper は暫定扱いにできる。
