# Current Plans / 現在の計画

This page is the current plan index for DegoDB. / このページは、DegoDB の現在の計画索引です。

Use this page before searching historical reports. / 履歴 report を探す前に、まずこのページを見ます。

## Quick Plan List / 計画リスト

When someone asks for "the plan list", answer from this section first. / 「計画リスト」と聞かれたら、まずこの section から答えます。

| Order | Work unit / 作業の塊 | Commit unit / コミット単位 | Status |
| --- | --- | --- | --- |
| 1 | Documentation foundation / docs 第一波の固定 | `Add database-first documentation foundation` | `DONE` |
| 2 | Curated legacy mtool reference snapshots / 整理済み旧 mtool 参照 snapshot | `Add curated legacy mtool reference snapshots` | `DONE` |
| 3 | PostgreSQL existing schema input draft / PostgreSQL 既存スキーマ入力ドラフト | `Add PostgreSQL existing schema actual-output backed input` | `DONE` |
| 4 | SQLite API generation input draft / SQLite API 生成入力ドラフト | `Add SQLite API generation actual-output backed input` | `DONE` |
| 5 | PostgreSQL actual-output backing / PostgreSQL 実出力の裏付け | Same commit as 3 / 3 と同じ commit | `DONE_WITH_3` |
| 6 | SQLite actual-output backing / SQLite 実出力の裏付け | Same commit as 4 / 4 と同じ commit | `DONE_WITH_4` |
| 7 | AI context source output / AI 文脈 Source Output 化 | `Generate AI context source output` | `TODO` |
| 8 | Modernization audit MVP generator / 現代化診断 MVP generator | `Generate modernization audit output` | `TODO` |
| 9 | Goal-based help and wrapper CLI roadmap / 目的別 help と wrapper CLI roadmap | `Document goal-based help and wrapper CLI roadmap` | `TODO` |
| 10 | Namespace migration / namespace migration | Separate cleanup commit after docs/examples settle / docs/examples が落ち着いた後の cleanup commit | `LATER` |

## Rough Effort Notes / 作業量メモ

These are planning estimates, not deadlines. / これは計画用の目安であり、期限ではありません。

| Order | Work unit / 作業の塊 | Rough effort / 目安 | Note |
| --- | --- | --- | --- |
| 1 | Documentation foundation / docs 第一波の固定 | Completed as a large docs commit / 大きめの docs commit として完了 | Broad positioning, plan index, use cases, compatibility, examples index, and Laravel baseline were bundled as one meaning unit. Generated-looking placeholders were excluded. |
| 2 | Curated legacy mtool reference snapshots / 整理済み旧 mtool 参照 snapshot | Completed; roughly 30-60 min class / 完了。目安として 30-60 分級 | Mostly verification, safety scan, README guardrails, and snapshot commit. |
| 3 | PostgreSQL existing schema input draft / PostgreSQL 既存スキーマ入力ドラフト | Completed; 30-60 min class / 完了。30-60 分級 | Schema / seed / scenario, representative query, import notes, and generation plan. Paired with item 5 in the same commit. |
| 4 | SQLite API generation input draft / SQLite API 生成入力ドラフト | Completed; 45-90 min class / 完了。45-90 分級 | SQLite schema, seed data, CRUD smoke, import notes, and generation plan. Paired with item 6 in the same commit. |
| 5 | PostgreSQL actual-output backing / PostgreSQL 実出力の裏付け | Completed with item 3 / 3 と同時完了 | Links the PostgreSQL example to current actual-output and PostgreSQL import gates without copying unrelated output into `reference/`. |
| 6 | SQLite actual-output backing / SQLite 実出力の裏付け | Completed with item 4 / 4 と同時完了 | Links the SQLite example to current sample18 actual generated outputs without copying unrelated output into `reference/`. |
| 7 | AI context source output / AI 文脈 Source Output 化 | Half-day+ / 半日以上 | Implement the `AI-CONTEXT-MD` source output before adding generated AI context examples. |
| 8 | Modernization audit MVP generator / 現代化診断 MVP generator | Half-day+ / 半日以上 | Implement an audit generator before adding generated audit examples. |
| 9 | Goal-based help and wrapper CLI roadmap / 目的別 help と wrapper CLI roadmap | 30-60 min | Design doc first: goal-based help groups and wrapper CLI command roadmap. |
| 10 | Namespace migration / namespace migration | 30-90 min for first mechanical cleanup; half-day+ if runtime/autoload/tests broaden / 最初の機械的 cleanup は 30-90 分。runtime / autoload / tests まで広げるなら半日以上 | Start by classifying `rg` results and keep docs/comment/generated-reference wording separate from runtime class or autoload changes. |

### Commit 1 Scope / commit 1 の範囲

`Add database-first documentation foundation` includes the current documentation foundation as one meaning unit: README positioning, current plan index, use cases, compatibility support, examples index, Laravel legacy baseline, and the 2026-06-21 plan reports. It does not include generated-looking AI context or audit placeholders. / `Add database-first documentation foundation` は、README 位置づけ、current plan index、use cases、対応範囲、examples 索引、Laravel legacy baseline、2026-06-21 計画 report を、docs foundation として 1 つの意味単位にまとめます。生成物に見える AI 文脈や診断の placeholder は含めません。

## Why This Page Exists / このページの目的

Plans often start as dated reports under `docs/reports/`, but active work must be findable from date-less documentation. / 計画は `docs/reports/` 配下の日付付き report から始まることがありますが、現在有効な作業は日付なしの恒久文書から辿れる必要があります。

This page keeps the active status list short, searchable, and linked to the detailed source documents. / このページは、現在のステータス一覧を短く、検索しやすく、詳細文書へリンクできる状態に保ちます。

## Status Meanings / 状態の意味

| Status | Meaning / 意味 |
| --- | --- |
| `DONE` | Completed for the current scope / 現在の scope では完了 |
| `DONE_WITH_3` | Completed in the same commit as item 3 / 3 と同じ commit で完了 |
| `DONE_WITH_4` | Completed in the same commit as item 4 / 4 と同じ commit で完了 |
| `ACTIVE_NEXT` | Recommended next work / 次に進める主線 |
| `TODO` | Planned but not started / 計画済み・未着手 |
| `PARKED` | Intentionally deferred / 意図的に保留 |
| `LATER` | Useful later, not a current priority / 後で有用だが現在の優先ではない |

## Documentation And Sales Asset Plans / 文書・営業資産系

| Status | Plan | Main artifacts / 主な成果物 |
| --- | --- | --- |
| `DONE` | README entry positioning / README 入口整理 | [README](../README.md) |
| `DONE` | Database-first sales asset roadmap / データベース起点の営業資産ロードマップ | [2026-0621 database-first sales assets plan](reports/2026/2026-0621-database-first-sales-assets-plan.md) |
| `DONE` | Use case guide / ユースケースガイド | [Use Cases](use-cases.md) |
| `DONE` | Scenario example index / シナリオ型 example 索引 | [Examples](../examples/README.md) |
| `TODO` | AI context source output / AI 文脈 Source Output 化 | Future `AI-CONTEXT-MD` source output |
| `TODO` | Modernization audit MVP generator / 現代化診断 MVP generator | Future audit generator |
| `DONE` | Compatibility and output support message / 対応範囲と出力サポートの整理 | [Compatibility And Output Support](compatibility-and-output-support.md) |
| `DONE` | Curated legacy mtool reference snapshots / 整理済み旧 mtool 参照 snapshot | `mtool/reference/legacy-mtool-build/`, `mtool/reference/legacy-mtool-templates/` |
| `DONE` | Laravel + MySQL legacy baseline / Laravel + MySQL レガシー baseline | [Laravel + MySQL legacy example](../examples/laravel-mysql-legacy/README.md) |
| `DONE` | Laravel + MySQL mtool handoff / mtool 引き継ぎ | [Import notes](../examples/laravel-mysql-legacy/mtool/import-notes.md), [Generation plan](../examples/laravel-mysql-legacy/mtool/generation-plan.md) |
| `DONE` | PostgreSQL existing schema actual-output backed input / PostgreSQL 既存スキーマ実出力裏付け付き入力 | `examples/postgresql-existing-schema/` |
| `DONE` | SQLite API generation actual-output backed input / SQLite API 生成実出力裏付け付き入力 | `examples/sqlite-api-generation/` |
| `PARKED` | Japanese invoice / billing / compliance example / 日本向け請求・インボイス example | Parked until domain review is available. |

## Detailed Work And Commit Units / 詳細な作業単位とコミット単位

This repeats the quick list near the detailed plan tables for maintenance. / 詳細な計画表の近くでも保守しやすいように、quick list と同じ単位を再掲します。

| Order | Work unit / 作業単位 | Commit unit / コミット単位 | Status |
| --- | --- | --- | --- |
| 1 | Documentation foundation / docs 第一波の固定 | `Add database-first documentation foundation` | `DONE` |
| 2 | Curated legacy mtool reference snapshots / 整理済み旧 mtool 参照 snapshot | `Add curated legacy mtool reference snapshots` | `DONE` |
| 3 | PostgreSQL existing schema input draft / PostgreSQL 既存スキーマ入力ドラフト | `Add PostgreSQL existing schema actual-output backed input` | `DONE` |
| 4 | SQLite API generation input draft / SQLite API 生成入力ドラフト | `Add SQLite API generation actual-output backed input` | `DONE` |
| 5 | PostgreSQL actual-output backing / PostgreSQL 実出力の裏付け | Same commit as 3 / 3 と同じ commit | `DONE_WITH_3` |
| 6 | SQLite actual-output backing / SQLite 実出力の裏付け | Same commit as 4 / 4 と同じ commit | `DONE_WITH_4` |
| 7 | AI context source output / AI 文脈 Source Output 化 | `Generate AI context source output` | `TODO` |
| 8 | Modernization audit MVP generator / 現代化診断 MVP generator | `Generate modernization audit output` | `TODO` |
| 9 | Goal-based help and wrapper CLI roadmap / 目的別 help と wrapper CLI roadmap | `Document goal-based help and wrapper CLI roadmap` | `TODO` |
| 10 | Namespace migration / namespace migration | Separate cleanup commit after docs/examples settle / docs/examples が落ち着いた後の cleanup commit | `LATER` |

The first commit includes the current documentation foundation as one meaning unit: README positioning, current plan index, use cases, compatibility support, examples index, Laravel legacy baseline, and the 2026-06-21 plan reports. It excludes generated-looking placeholders for features that do not yet emit real output. / 最初の commit は、README 位置づけ、current plan index、use cases、対応範囲、examples 索引、Laravel legacy baseline、2026-06-21 計画 report を、docs foundation として 1 つの意味単位にまとめています。まだ実出力を出せない機能の generated 風 placeholder は含めません。

The curated legacy mtool reference snapshot commit comes immediately after the documentation foundation commit and before new scenario examples. / 整理済み旧 mtool 参照 snapshot の commit は、documentation foundation commit の直後、新しい scenario example の前に置いています。

## Product And Implementation Plans / 機能・実装系

| Status | Plan | Source / 参照 |
| --- | --- | --- |
| `DONE` | Security / auth / SSO current baseline / security・auth・SSO 現行 baseline | [2026-0619 plan inventory](reports/2026/2026-0619-plan-inventory.md), [generated runtime security plan](internal/generated-runtime-security-plan.md) |
| `DONE` | Custom proxy metadata bundle coverage / custom proxy metadata bundle coverage | [2026-0620 custom proxy metadata bundle coverage](reports/2026/2026-0620-custom-proxy-metadata-bundle-coverage.md) |
| `DONE` | Generated OIDC JWT runtime verification / generated OIDC JWT runtime verification | [2026-0620 generated OIDC JWT runtime verification](reports/2026/2026-0620-generated-oidc-jwt-runtime-verification.md) |
| `DONE` | PostgreSQL user DB output representative set / PostgreSQL user DB output 代表 coverage | [2026-0620 PostgreSQL user DB output first slice](reports/2026/2026-0620-postgresql-user-db-output-first-slice.md) |
| `DONE` | Curated legacy mtool reference snapshots / 整理済み旧 mtool 参照 snapshot | Separate reference commit before additional scenario examples |
| `LATER` | Generated name migration follow-up / generated name migration 後続 | [2026-0620 generated name migration plan](reports/2026/2026-0620-generated-name-migration-plan.md) |
| `TODO` | AI context source output / AI 文脈 Source Output 化 | Future `AI-CONTEXT-MD` source output, tracked in [2026-0621 database-first sales assets plan](reports/2026/2026-0621-database-first-sales-assets-plan.md) |
| `TODO` | Modernization audit MVP generator / 現代化診断 MVP generator | Future audit generator, tracked in [2026-0621 database-first sales assets plan](reports/2026/2026-0621-database-first-sales-assets-plan.md) |
| `TODO` | Goal-based help targets / 目的別 help | Future `make help-*` targets |
| `TODO` | Wrapper CLI roadmap / wrapper CLI ロードマップ | Future docs for `mtool init`, `mtool inspect`, `mtool generate`, `mtool audit`, `mtool doctor` |
| `LATER` | Mtool namespace migration / Mtool namespace migration | Later cleanup, not needed before documentation/examples |
| `PARKED` | Mtool admin/lab broad route authorization enforcement / admin/lab route authorization broad enforcement | [Authorization hardening plan](internal/authorization-hardening-plan.md) |
| `PARKED` | Mtool config store PostgreSQL support / Mtool config store PostgreSQL support | Not part of the current PostgreSQL user DB output lane |
| `PARKED` | SQL Server / Oracle current support / SQL Server・Oracle 現行対応 | Future enterprise compatibility candidates only |

## Finding Rules / 探し方のルール

- Start here when asking "what plans remain?" / 「残っている計画は何か」を見る時はここから始める。
- Use date-less docs for current commitments. / 現在有効な約束は日付なし文書を見る。
- Use dated reports for history, decisions, and implementation records. / 履歴、判断経緯、実装記録は日付付き report を見る。
- Promote a report item into this page when it becomes active or user-facing. / report 内の項目が active または user-facing になったら、このページへ昇格する。
- Do not leave the only copy of an active plan inside a dated report. / active plan の唯一の所在を日付付き report の中だけにしない。

## Current Detailed Sources / 現在の詳細ソース

- Latest inventory: [2026-0621 plan inventory](reports/2026/2026-0621-plan-inventory.md)
- Sales asset plan: [2026-0621 database-first sales assets plan](reports/2026/2026-0621-database-first-sales-assets-plan.md)
- Post-security priority plan: [2026-0620 post-security feature priority plan](reports/2026/2026-0620-post-security-feature-priority-plan.md)
- PostgreSQL user DB output: [2026-0620 PostgreSQL user DB output first slice](reports/2026/2026-0620-postgresql-user-db-output-first-slice.md)
- Generated name migration: [2026-0620 generated name migration plan](reports/2026/2026-0620-generated-name-migration-plan.md)
