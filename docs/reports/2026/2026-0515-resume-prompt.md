# 2026-05-15 Resume Prompt

最新版のコピペ用再開 prompt。これは `docs/reports/2026/2026-0515-end-of-day-handoff.md` から切り出した派生文書であり、背景と確認ログの正本は handoff 側にある。`docs/reports/2026/2026-0513-end-of-day-handoff.md` は再開基準として使わない。

```text
<repo-root> の MTOOL rewrite 作業を再開してください。

今日の到達点:
- broad scope の最新読みは `80-82%`、`Project 1 = MTOOL` parity / bridge scope は `92-93%`
- LanguageResource の最終 source of truth は `mtool/resources/<PROJECT_KEY>/` 配下の JSON file tree
- current admin の LanguageResource は inspector-only で、旧 Lang editor は再実装しない
- LanguageResource 編集は AI / 人が file を直接編集する前提
- current auto-translate route / service は削除済み
- generated `HTML-DB` wrapper の `lang_res_auto_translate_ajax.php` は file workflow を案内する legacy-compatible `NG` response
- `MTOOL` / `SAMPLE2` / `SAMPLE4` / `SAMPLE6` の file tree は export / validate 済み
- `make mtool-lang-res-file-tree-export` と `make mtool-lang-res-file-tree-check` が主系コマンド
- `make mtool-html-db-lang-res-wrapper-check` は最新 `HTML-DB` を build/publish してから smoke する self-contained entrypoint
- DB bridge / retirement 用 script 本体は `mtool/scripts/debug/language_resource/` 配下へ移動済み
- local `config_app` では `project_language_resource_*` table / data は削除済み

前提:
- `LanguageResource` の source of truth は DB ではなく repo file
- original-codes/ は runtime や generator の直接入力には使わない
- old Lang editor を current tool に残す前提はない
- user project ごとの多言語対応も file 編集前提でよい
- debug sync CLI は migration/debug 専用で、日常運用は validate/export を使う

次のタスク:
1. `mtool/scripts/debug/language_resource/` 配下の compatibility wrapper をどこまで残すか整理する
2. 次の pilot project に同じ file-only/tableless パターンを適用する
3. unrestricted な DB access がある環境で readiness check を再実行し、`db_tables_absent_or_empty=pass` を確認する
4. migration/debug 期間が終わったら DB bridge script 自体の削除範囲を詰める

最初に以下を実行してください:
- sed -n '1,260p' docs/reports/2026/2026-0515-end-of-day-handoff.md
- sed -n '1,240p' docs/reports/2026/2026-0515-progress-snapshot.md
- sed -n '1,260p' docs/reports/2026/2026-0515-language-resource-file-source-of-truth-plan.md
- sed -n '1,220p' docs/reports/2026/2026-0515-language-resource-debug-bridge-hardening.md
- sed -n '1,220p' docs/reports/2026/2026-0515-language-resource-debug-script-relocation.md
- sed -n '1,240p' mtool/resources/README.md
- make mtool-lang-res-file-tree-export
- make mtool-lang-res-file-tree-check
- make mtool-html-db-lang-res-wrapper-check
- php mtool/scripts/debug/language_resource/sync_project_language_resource_from_file_tree.php --help
- php mtool/scripts/sync_project_language_resource_from_file_tree.php --help
- APP_DB_HOST=127.0.0.1 APP_DB_PORT=33061 APP_DB_NAME=config_app APP_DB_USER=config_app APP_DB_PASSWORD=config_app_local_2026 APP_CONFIG_DB_HOST=127.0.0.1 APP_CONFIG_DB_PORT=33061 APP_CONFIG_DB_NAME=config_app APP_CONFIG_DB_USER=config_app APP_CONFIG_DB_PASSWORD=config_app_local_2026 php mtool/scripts/check_language_resource_db_retirement_readiness.php --project-key=MTOOL --legacy-project-pid=1 --docroot=<repo-root>/work/source-outputs/MTOOL/HTML-DB

参照ドキュメント:
- docs/reports/2026/2026-0515-end-of-day-handoff.md
- docs/reports/2026/2026-0515-progress-snapshot.md
- docs/reports/2026/2026-0515-language-resource-file-source-of-truth-plan.md
- docs/reports/2026/2026-0515-language-resource-debug-bridge-hardening.md
- docs/reports/2026/2026-0515-language-resource-debug-script-relocation.md
- docs/internal/language-resource-separation.md
- docs/internal/mtool-admin-roadmap.md
- mtool/resources/README.md
```
