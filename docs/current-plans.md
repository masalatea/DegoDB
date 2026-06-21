# Current Plans / 現在の計画

English companion:
This page lists only unfinished or deferred plans. Completed work is kept in dated reports.

This page is the active plan index for DegoDB. / このページは、DegoDB の現在有効な計画索引です。

Use this page before searching historical reports. / 履歴 report を探す前に、まずこのページを見ます。

## Quick Plan List / 計画リスト

When someone asks for "the plan list", answer from this section first. / 「計画リスト」と聞かれたら、まずこの section から答えます。

| Order | Work unit / 作業の塊 | Commit unit / コミット単位 | Status |
| --- | --- | --- | --- |
| 1 | AI context source output / AI 文脈 Source Output 化 | `Generate AI context source output` | `ACTIVE_NEXT` |
| 2 | Modernization audit MVP generator / 現代化診断 MVP generator | `Generate modernization audit output` | `TODO` |
| 3 | Goal-based help and wrapper CLI roadmap / 目的別 help と wrapper CLI roadmap | `Document goal-based help and wrapper CLI roadmap` | `TODO` |
| 4 | Generated name migration follow-up / generated name migration 後続 | `Continue generated name migration pipeline` | `LATER` |
| 5 | PostgreSQL follow-up / PostgreSQL 後続 | Conditional contract/runbook or compose-profile commit / 条件付き contract・runbook・compose profile commit | `CONDITIONAL` |
| 6 | Namespace migration / namespace migration | Separate cleanup commit after docs/examples settle / docs/examples が落ち着いた後の cleanup commit | `LATER` |

## Rough Effort Notes / 作業量メモ

These are planning estimates, not deadlines. / これは計画用の目安であり、期限ではありません。

| Order | Work unit / 作業の塊 | Rough effort / 目安 | Note |
| --- | --- | --- | --- |
| 1 | AI context source output / AI 文脈 Source Output 化 | Half-day+ / 半日以上 | Implement the `AI-CONTEXT-MD` source output before adding generated AI context examples. |
| 2 | Modernization audit MVP generator / 現代化診断 MVP generator | Half-day+ / 半日以上 | Implement an audit generator before adding generated audit examples. |
| 3 | Goal-based help and wrapper CLI roadmap / 目的別 help と wrapper CLI roadmap | 30-60 min | Design doc first: goal-based help groups and wrapper CLI command roadmap. |
| 4 | Generated name migration follow-up / generated name migration 後続 | 60-120 min for next representative after-snapshot slice; half-day+ if sample-wide migration starts / 次の代表 after snapshot なら 60-120 分。sample 全体移行なら半日以上 | Generate corresponding `after` snapshots with new naming rules, then compare with an explicit keyword map. Do not hand-patch generated artifacts. |
| 5 | PostgreSQL follow-up / PostgreSQL 後続 | 30-90 min for runbook/compose-profile decision; half-day+ for new behavior coverage / runbook・compose profile 判断は 30-90 分。新しい挙動 coverage は半日以上 | Add only when a new DBAccess behavior surface or local PostgreSQL runbook need is explicit. Do not treat this as Mtool config store PostgreSQL support. |
| 6 | Namespace migration / namespace migration | 30-90 min for first mechanical cleanup; half-day+ if runtime/autoload/tests broaden / 最初の機械的 cleanup は 30-90 分。runtime / autoload / tests まで広げるなら半日以上 | Start by classifying `rg` results and keep docs/comment/generated-reference wording separate from runtime class or autoload changes. |

## Status Meanings / 状態の意味

| Status | Meaning / 意味 |
| --- | --- |
| `ACTIVE_NEXT` | Recommended next work / 次に進める主線 |
| `TODO` | Planned but not started / 計画済み・未着手 |
| `CONDITIONAL` | Add only when the trigger condition becomes concrete / 条件が具体化した時だけ追加する |
| `LATER` | Useful later, not a current priority / 後で有用だが現在の優先ではない |
| `PARKED` | Intentionally deferred and not part of the quick plan list / 意図的に保留し、quick plan list には入れない |
| `PARKED_REPLAN` | Deferred until a fresh scope / value / risk decision is made / scope・価値・risk を再判断するまで保留 |

## Current Boundaries / 現在の境界

- Official date-less docs should describe implemented features only. / 日付なしの正式文書は、実装済み機能だけを説明する。
- Future output ideas stay in dated reports until the generator exists. / 将来の出力案は、generator ができるまで日付付き report に置く。
- Do not store hand-written output under generated-looking `examples/*/reference/` or `examples/*/generated/`. / 手書き出力を generated に見える `examples/*/reference/` や `examples/*/generated/` に置かない。
- Current plan answers should list only unfinished or deferred plans. / 現在の計画回答では、未完了または後回しの計画だけを出す。
- Conditional plans stay listed only when the trigger is clear. / 条件付き計画は、発火条件が明確なものだけ載せる。

## Replan And Parked Items / 再計画・保留項目

These are known candidates, but they should not appear as the next implementation unit without a fresh priority decision. / これらは既知の候補ですが、新しい優先判断なしに次の実装単位として扱いません。

| Item / 項目 | Status | Reopen condition / 再開条件 |
| --- | --- | --- |
| Mtool admin/lab route authorization hardening / admin・lab route authorization 強化 | `PARKED_REPLAN` | Replan when a concrete deployment need or one route cluster is ready, with audit/test scope defined. |
| Mtool config store PostgreSQL support / Mtool config store PostgreSQL 対応 | `PARKED` | Reopen only as a config-store portability project, separate from user DB/generated output PostgreSQL support. |
| SQL Server / Oracle current support / SQL Server・Oracle 現行対応 | `PARKED` | Reopen only with explicit enterprise need and support-scope decision. |
| Japanese invoice / billing / compliance sample / 日本向け請求・インボイス sample | `PARKED` | Reopen only after domain review is available. |
| Approval workflow, rollback / revision history, local app packaging / 承認 workflow、rollback・revision、local app packaging | `PARKED` | Reopen as separate product/foundation plans after current generated-output and docs lanes settle. |

## History / 履歴

Completed work was moved out of this active list. / 完了済み作業は、この active list から履歴側へ移しました。

| Completed scope / 完了済み範囲 | Historical source / 履歴ソース |
| --- | --- |
| Documentation foundation, curated legacy references, Laravel baseline, PostgreSQL input/backing, SQLite input/backing | [2026-0621 plan inventory](reports/2026/2026-0621-plan-inventory.md) |
| Database-first sales assets and future output placement rules | [2026-0621 database-first sales assets plan](reports/2026/2026-0621-database-first-sales-assets-plan.md) |
| PostgreSQL user DB output representative set | [2026-0620 PostgreSQL user DB output first slice](reports/2026/2026-0620-postgresql-user-db-output-first-slice.md) |
| Generated name migration first slice | [2026-0620 generated name migration plan](reports/2026/2026-0620-generated-name-migration-plan.md) |
| Post-security priority decisions and parked authorization gate | [2026-0620 post-security feature priority plan](reports/2026/2026-0620-post-security-feature-priority-plan.md) |

## Finding Rules / 探し方のルール

- Start here when asking "what plans remain?" / 「残っている計画は何か」を見る時はここから始める。
- Use date-less docs for current commitments. / 現在有効な約束は日付なし文書を見る。
- Use dated reports for history, decisions, and implementation records. / 履歴、判断経緯、実装記録は日付付き report を見る。
- Promote a report item into this page when it becomes active or user-facing. / report 内の項目が active または user-facing になったら、このページへ昇格する。
- Move completed items back to dated reports and keep this list short. / 完了項目は日付付き report へ戻し、この一覧は短く保つ。
