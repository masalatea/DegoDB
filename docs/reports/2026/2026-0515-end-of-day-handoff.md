# 2026-05-15 End Of Day Handoff

> Latest restart baseline for the next session after 2026-05-15. Use this handoff as the source of truth; `docs/reports/2026/2026-0515-resume-prompt.md` is a derived copy/paste helper; `docs/reports/2026/2026-0513-end-of-day-handoff.md` is superseded as the restart baseline.

## Summary

- `LanguageResource` の最終方針を file-based source of truth へ固定した
- 正本は `mtool/resources/<PROJECT_KEY>/` 配下の JSON file tree であり、AI と人が同じ file を直接編集する前提にした
- current admin の `LanguageResource` は inspector-only とし、旧 Lang editor は再実装しない方針に確定した
- current app の auto-translate route / service は外し、generated `HTML-DB` wrapper の `lang_res_auto_translate_ajax.php` は file workflow を案内する legacy-compatible `NG` response に変えた
- `MTOOL` に加えて `SAMPLE2` / `SAMPLE4` / `SAMPLE6` の file-canonical tree を整備し、bulk export / bulk validate を主系コマンドとして回せる状態にした
- `make mtool-html-db-lang-res-wrapper-check` は最新 `HTML-DB` を build/publish してから published docroot を smoke する self-contained entrypoint に更新した
- DB bridge / retirement 用 script は `mtool/scripts/debug/language_resource/` 配下へ移し、旧 `mtool/scripts/*.php` は silent compatibility wrapper に縮退させた
- `MTOOL` の `LanguageResource` 用 DB table / data は local `config_app` では削除済みで、core initdb でも新規作成しない状態にした

## Current Position

- `LanguageResource` の source of truth は repo file に一本化済み
- `LanguageResource` の current read path は `file-canonical -> reference -> empty` で、runtime / generator / wrapper は file catalog loader を通る
- current admin の write path は retired 済みで、日常運用は file 編集と validate/export で回す前提になっている
- `MTOOL` / `SAMPLE2` / `SAMPLE4` / `SAMPLE6` の file tree は検証済み
- local `config_app` では `project_language_resource_*` table は absent で、tableless/data-less の状態を確認済み
- readiness check は unrestricted/local DB access がある環境では `ready=true` まで到達済み
- sandbox 下では DB access 失敗で `db_tables_absent_or_empty=skip` になりうるが、code path 側の isolation 判定は通る

## Main Docs

- `docs/internal/language-resource-separation.md`
- `docs/internal/mtool-admin-roadmap.md`
- `docs/reports/2026/2026-0515-progress-snapshot.md`
- `mtool/resources/README.md`
- `docs/reports/2026/2026-0515-language-resource-file-source-of-truth-plan.md`
- `docs/reports/2026/2026-0515-language-resource-debug-bridge-hardening.md`
- `docs/reports/2026/2026-0515-language-resource-debug-script-relocation.md`

## Verified

```zsh
cd <repo-root>

make mtool-lang-res-file-tree-export
make mtool-lang-res-file-tree-check
make mtool-html-db-lang-res-wrapper-check

php mtool/scripts/debug/language_resource/sync_project_language_resource_from_file_tree.php --help
php mtool/scripts/sync_project_language_resource_from_file_tree.php --help

APP_DB_HOST=127.0.0.1 APP_DB_PORT=33061 APP_DB_NAME=config_app APP_DB_USER=config_app APP_DB_PASSWORD=config_app_local_2026 \
APP_CONFIG_DB_HOST=127.0.0.1 APP_CONFIG_DB_PORT=33061 APP_CONFIG_DB_NAME=config_app APP_CONFIG_DB_USER=config_app APP_CONFIG_DB_PASSWORD=config_app_local_2026 \
php mtool/scripts/debug/language_resource/sync_project_language_resource_from_file_tree.php --project-key=MTOOL

APP_DB_HOST=127.0.0.1 APP_DB_PORT=33061 APP_DB_NAME=config_app APP_DB_USER=config_app APP_DB_PASSWORD=config_app_local_2026 \
APP_CONFIG_DB_HOST=127.0.0.1 APP_CONFIG_DB_PORT=33061 APP_CONFIG_DB_NAME=config_app APP_CONFIG_DB_USER=config_app APP_CONFIG_DB_PASSWORD=config_app_local_2026 \
php mtool/scripts/check_language_resource_db_retirement_readiness.php --project-key=MTOOL --legacy-project-pid=1 --docroot=<repo-root>/work/source-outputs/MTOOL/HTML-DB
```

確認結果:

- `make mtool-lang-res-file-tree-export`: 4 project success
- `make mtool-lang-res-file-tree-check`: 4 project success, warning 0
- `make mtool-html-db-lang-res-wrapper-check`: latest published `HTML-DB` を前提に wrapper smoke 成功
- debug sync CLI: `mode=debug-db-bridge` で help / wrapper / direct path ともに動作確認済み
- readiness check: unrestricted/local DB access がある実行では `ready=true`

補足:

- `export --clean` と validate を並列で回すと race で missing-file / invalid-json が出ることがある
- canonical file tree の問題ではなく、順次実行なら再現しない
- 今日の slice は主に LanguageResource の設計固定と文書整理であり、新しい feature 実装を足した段階ではない

## Next Week First Step

最初の着手は、LanguageResource の migration/debug 導線をさらに縮退させるか、同じ file-only/tableless パターンを次の pilot project に広げるかを決めること。

優先順:

1. `mtool/scripts/debug/language_resource/` 配下の compatibility wrapper をどこまで残すか整理する
2. 次の pilot project に同じ file tree export / validate / tableless retirement を適用する
3. unrestricted な DB access がある環境で readiness check を再実行し、`db_tables_absent_or_empty=pass` を再確認する
4. migration/debug 期間が終わったら DB bridge script 自体の削除範囲を詰める

## Resume Commands

```zsh
cd <repo-root>

sed -n '1,260p' docs/reports/2026/2026-0515-end-of-day-handoff.md
sed -n '1,240p' docs/reports/2026/2026-0515-progress-snapshot.md
sed -n '1,260p' docs/reports/2026/2026-0515-language-resource-file-source-of-truth-plan.md
sed -n '1,220p' docs/reports/2026/2026-0515-language-resource-debug-bridge-hardening.md
sed -n '1,220p' docs/reports/2026/2026-0515-language-resource-debug-script-relocation.md
sed -n '1,240p' mtool/resources/README.md

make mtool-lang-res-file-tree-export
make mtool-lang-res-file-tree-check
make mtool-html-db-lang-res-wrapper-check

php mtool/scripts/debug/language_resource/sync_project_language_resource_from_file_tree.php --help
php mtool/scripts/sync_project_language_resource_from_file_tree.php --help

APP_DB_HOST=127.0.0.1 APP_DB_PORT=33061 APP_DB_NAME=config_app APP_DB_USER=config_app APP_DB_PASSWORD=config_app_local_2026 \
APP_CONFIG_DB_HOST=127.0.0.1 APP_CONFIG_DB_PORT=33061 APP_CONFIG_DB_NAME=config_app APP_CONFIG_DB_USER=config_app APP_CONFIG_DB_PASSWORD=config_app_local_2026 \
php mtool/scripts/check_language_resource_db_retirement_readiness.php --project-key=MTOOL --legacy-project-pid=1 --docroot=<repo-root>/work/source-outputs/MTOOL/HTML-DB
```

## Restart Prompt

同内容のコピペ用 prompt は `docs/reports/2026/2026-0515-resume-prompt.md` にも置いた。

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
