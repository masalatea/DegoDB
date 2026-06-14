# 2026-05-13 Resume Prompt

最新版のコピペ用再開 prompt。これは `docs/reports/2026/2026-0513-end-of-day-handoff.md` から切り出した派生文書であり、背景と確認ログの正本は handoff 側にある。`docs/reports/2026/2026-0512-end-of-day-handoff.md` は再開基準として使わない。

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
