# LanguageResource DB Bridge Debug Scripts

- `mtool/scripts/debug/language_resource/` には `LanguageResource` の DB bridge / retirement 用 CLI を置く。
- これらは current runtime/admin の主系では使わない migration/debug 専用 script である。
- 通常運用の確認は `MTOOL -> mtool/resources/`、sample project -> `sample/<category>/<pack>/resources/` の file tree に対して `export` / `validate` を使う。
- `mtool/scripts/check_language_resource_db_retirement_readiness.php` は readiness 確認用の current entrypoint であり、この subtree の lib を読むが compatibility wrapper ではない。

## Canonical entrypoints

- `mtool/scripts/debug/language_resource/sync_project_language_resource_from_file_tree.php`
- `mtool/scripts/debug/language_resource/inspect_language_resource_db_residual_rows.php`
- `mtool/scripts/debug/language_resource/retire_project_language_resource_db_rows.php`
- `mtool/scripts/debug/language_resource/drop_project_language_resource_db_tables.php`

## Compatibility wrappers kept temporarily

- `mtool/scripts/sync_project_language_resource_from_file_tree.php` -> `mtool/scripts/debug/language_resource/sync_project_language_resource_from_file_tree.php`
- `mtool/scripts/inspect_language_resource_db_residual_rows.php` -> `mtool/scripts/debug/language_resource/inspect_language_resource_db_residual_rows.php`
- `mtool/scripts/retire_project_language_resource_db_rows.php` -> `mtool/scripts/debug/language_resource/retire_project_language_resource_db_rows.php`
- `mtool/scripts/drop_project_language_resource_db_tables.php` -> `mtool/scripts/debug/language_resource/drop_project_language_resource_db_tables.php`
- 上記 4 本は historical handoff / local shell history / 手元 runbook 互換のためにだけ残す thin `require_once` wrapper である。
- stdout/stderr の contract は canonical script 本体に寄せ、wrapper 側に独自処理は足さない。
- 新しい stable doc / Make target / automation では old top-level path を増やさない。

## Wrapper removal conditions

- stable doc / Make target / runbook / automation から old top-level path 参照が消えている。
- handoff / resume prompt の copy-paste が canonical debug path を前提にしている。
- readiness check と必要な smoke command が wrapper なしでも green を維持する。
- migration/debug 期間が終わり、DB bridge 自体の削除範囲を確定できる。
