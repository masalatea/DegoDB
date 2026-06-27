# Current Plans / 現在の計画

English companion:
This page lists only unfinished or deferred plans. Completed work is kept in dated reports.

This page is the active plan index for DegoDB. / このページは、DegoDB の現在有効な計画索引です。

Use this page before searching historical reports. / 履歴 report を探す前に、まずこのページを見ます。

## Quick Plan List / 計画リスト

When someone asks for "the plan list", answer from this section first. / 「計画リスト」と聞かれたら、まずこの section から答えます。

| Order | Work unit / 作業の塊 | Commit unit / コミット単位 | Status |
| --- | --- | --- | --- |
| 1 | Physical/logical sample naming migration / physical・logical sample 命名移行 | `Complete representative sample naming migration` | `COMPLETED` |
| 2 | PostgreSQL Input / Output support / PostgreSQL Input・Output 対応 | Local PostgreSQL input/output completion gate / local PostgreSQL Input・Output 完了 gate | `COMPLETED` |
| 3 | PHP output namespace support / generated PHP output namespace 対応 | Project namespace setting plus DataClass / DBAccess generated output and mixed sample references | `COMPLETED` |
| 4 | Mtool implementation namespace cleanup / Mtool 実装 namespace cleanup | Separate cleanup commit after generated output namespace support settles | `LATER` |

## Priority Rationale / 優先理由

The OSS / consulting readiness docs package has been completed as the documentation-first step. / OSS・導入支援 readiness 資料 package は、ドキュメント先行の作業として完了しました。

AI context source output is implemented, verified across all tutorial samples, and available as an implicit default companion documentation output for current/new projects. Mtool self-output verification publishes AI context documentation for Mtool itself. / AI 文脈 Source Output は全 tutorial sample に展開済みで、現在・新規 project へ暗黙 default companion documentation output として提供します。Mtool 自身についても AI 文脈ドキュメントを self-output して検証しています。

The physical/logical sample naming migration, PostgreSQL Input / Output support, and generated PHP output namespace support are complete for their intended support boundaries. Generated PHP output namespace support adds an optional project-level namespace setting for DataClass / DBAccess output, keeps the default namespace-free, and verifies mixed sample coverage. Broader Mtool implementation namespace cleanup remains deferred. / physical / logical sample 命名移行、PostgreSQL Input / Output 対応、generated PHP output namespace 対応は、意図した support boundary では完了です。generated PHP output namespace 対応では、DataClass / DBAccess output 向けの任意 project-level namespace 設定を追加し、default は namespace なしのまま、sample coverage は混在で検証しました。Mtool 実装全体の namespace cleanup は後回しです。

## Rough Effort Notes / 作業量メモ

These are planning estimates, not deadlines. / これは計画用の目安であり、期限ではありません。

| Order | Work unit / 作業の塊 | Rough effort / 目安 | Note |
| --- | --- | --- | --- |
| 1 | AI context standard rollout / AI 文脈出力の標準展開 | Completed / 完了 | Added AI context output definitions across tutorial samples, regenerated affected references, and locked cross-sample publish coverage in `ZzzAiContextStandardOutputTest`. |
| 2 | AI context default-output transition / AI 文脈出力の default 化 | Completed / 完了 | `AI-CONTEXT-MD` remains the compatibility key, and missing project rows are supplied as an implicit default companion documentation output. |
| 3 | Mtool self-output verification / Mtool 自身の self-output 検証 | Completed / 完了 | Mtool outputs AI context documentation for Mtool itself; the generated documentation is reviewed by test contract as AI-reader context. |
| 4 | Modernization audit MVP generator / 現代化診断 MVP generator | Completed / 完了 | Added deterministic `modernization-audit-md` generation and sample17 `MODERNIZATION-AUDIT-MD` reference coverage. |
| 5 | Goal-based help and wrapper CLI roadmap / 目的別 help と wrapper CLI roadmap | Completed / 完了 | Added `goal-based-help-and-wrapper-cli-roadmap.md` as the design doc for goal help groups and future wrapper CLI command shape. |
| 6 | Physical/logical sample naming migration / physical・logical sample 命名移行 | Completed / 完了 | `sample01`-`sample10` and `sample12`-`sample26` tutorial samples are committed/applied with snake_case physical DB/source names while generated PHP/OpenAPI/proxy class/file names stay stable under `physical-logical-v1`. `sample14` covers the custom proxy step-source boundary; `sample11` has no DB physical-name target. `SamplePhysicalLogicalNamingContractTest` guards tutorial seed SQL schema identifiers, seed physical-name columns, checker physical-name constants, generated reference DBAccess SQL, reference JSON physical-name fields, generated reference and tutorial documentation physical-name text mentions, migrated PHPUnit / CLI check-script policy opt-ins, and sample-run entrypoint classification against regressions. Current tutorial/study docs distinguish physical `snake_case` names from generated surfaces. Do not hand-patch generated artifacts or legacy references. |
| 7 | PostgreSQL Input / Output support / PostgreSQL Input・Output 対応 | Completed / 完了 | PostgreSQL input is covered by live schema import, PostgreSQL output is covered by generated DBAccess / user DB contract comparison, and both are verified through `make postgresql-user-db-test-local`. Mtool config store PostgreSQL support is outside this scope. |
| 8 | PHP output namespace support / generated PHP output namespace 対応 | Completed / 完了 | Added optional project-level PHP namespace support for generated DataClass / DBAccess output. Default output remains namespace-free; sample04 and sample10 cover namespaced output; sample01, sample15, and sample26 cover namespace-free defaults / metadata bundles. Mtool self-output namespace application remains outside this completed lane. Tracking memo: [2026-0627 PHP output namespace support plan](reports/2026/2026-0627-php-output-namespace-plan.md). |
| 9 | Mtool implementation namespace cleanup / Mtool 実装 namespace cleanup | 30-90 min for first mechanical cleanup; half-day+ if runtime/autoload/tests broaden / 最初の機械的 cleanup は 30-90 分。runtime / autoload / tests まで広げるなら半日以上 | Deferred until generated output namespace support settles. Start by classifying `rg` results and keep docs/comment/generated-reference wording separate from runtime class or autoload changes. |

## AI Context Completion Contract / AI 文脈出力の完了条件

AI context output is not complete merely because one sample emits `AI-CONTEXT-MD`. / AI 文脈出力は、1 sample が `AI-CONTEXT-MD` を出せるだけでは完了扱いにしません。

Completion requires all of the following. / 完了には以下をすべて必須とします。

1. Sample-wide rollout / sample 全体展開
   - Every relevant tutorial sample has an AI context source output definition or an explicit documented exclusion. / 関連する tutorial sample すべてに AI 文脈 Source Output 定義を追加するか、除外理由を明記する。
   - The output is regenerated by Mtool code, not written by AI. / 出力は AI が書かず、Mtool のコードが再生成する。
   - Reference snapshots and tests verify the generated MD/JSON package. / 生成された MD/JSON package を reference snapshot と test で検証する。
2. Default-output transition / 標準出力への移行
   - `AI-CONTEXT-MD` starts as an explicit rollout key, but the target state is default companion documentation output. / `AI-CONTEXT-MD` は展開中の明示 key として始めるが、到達点は標準 companion documentation output とする。
   - The temporary flag/key behavior must be removed or reduced to compatibility naming after default generation is stable. / 標準生成が安定した後、一時的な flag/key 挙動は削除するか互換名に縮小する。
3. Mtool self-output verification / Mtool 自身の self-output 検証
   - Mtool must generate AI context documentation for Mtool itself. / Mtool は Mtool 自身について AI 文脈ドキュメントを生成する。
   - AI reviews the generated documentation as a reader to check whether it correctly explains Mtool's own schema/output model. / AI は reader として生成ドキュメントを確認し、Mtool 自身の schema / output model を正しく説明できているか検証する。
   - Any review findings are fixed in generator code or canonical metadata, not by hand-editing generated output. / 確認で見つかった問題は、生成物を手修正せず、generator code または canonical metadata 側で直す。

Current status / 現在の状態:

- Sample-wide rollout is complete for tutorial samples. / tutorial sample 全体展開は完了。
- Implicit default generation is complete for current/new projects through the canonical source output catalog. / canonical source output catalog により、現在・新規 project への暗黙 default 生成は完了。
- Mtool self-output verification is covered by `ZzzAiContextStandardOutputTest`. / Mtool self-output 検証は `ZzzAiContextStandardOutputTest` で coverage 済み。
- `AI-CONTEXT-MD` remains as the standard compatibility key for generated AI context output. / `AI-CONTEXT-MD` は生成 AI 文脈 output の標準互換 key として残します。

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
| AI context source output rollout, default-output transition, and self-output / AI 文脈 Source Output 展開・default 化・self-output | `AI-CONTEXT-MD` generator, all tutorial sample seed definitions, implicit default companion output for current/new projects, `sample17-multi-output-project` reference output, Mtool self-output verification, [Proof Matrix](proof-matrix.md). |
| Modernization audit MVP generator / 現代化診断 MVP generator | `modernization-audit-md` deterministic read-only audit generator, `sample17-multi-output-project` `MODERNIZATION-AUDIT-MD` seed/reference output, `Sample17MultiOutputProjectTest` coverage. |
| Goal-based help and wrapper CLI roadmap / 目的別 help と wrapper CLI roadmap | [Goal-Based Help And Wrapper CLI Roadmap](goal-based-help-and-wrapper-cli-roadmap.md) defining goal help groups, current command mapping, wrapper CLI command shape, and phased implementation order. |
| OSS / consulting readiness docs package | [Adoption Guide](adoption-guide.md), [Consulting Intake](consulting-intake.md), [Deliverables](deliverables.md), [Proof Matrix](proof-matrix.md), [Security And Data Handling](security-and-data-handling.md), [2026-0621 OSS / consulting readiness inventory](reports/2026/2026-0621-oss-consulting-readiness-inventory.md) |
| OSS / consulting readiness inventory | [2026-0621 OSS / consulting readiness inventory](reports/2026/2026-0621-oss-consulting-readiness-inventory.md) |
| Documentation foundation, curated legacy references, Laravel baseline, PostgreSQL input/backing, SQLite input/backing | [2026-0621 plan inventory](reports/2026/2026-0621-plan-inventory.md) |
| Database-first sales assets and future output placement rules | [2026-0621 database-first sales assets plan](reports/2026/2026-0621-database-first-sales-assets-plan.md) |
| PostgreSQL user DB output representative set | [2026-0620 PostgreSQL user DB output first slice](reports/2026/2026-0620-postgresql-user-db-output-first-slice.md) |
| PostgreSQL Input / Output support completion | [2026-0627 PostgreSQL input/output support completion](reports/2026/2026-0627-postgresql-user-db-lane-completion.md) |
| Generated PHP output namespace support | [2026-0627 PHP output namespace support plan](reports/2026/2026-0627-php-output-namespace-plan.md) |
| Generated DataClass naming wording slice | [2026-0626 generated name migration sample follow-up](reports/2026/2026-0626-generated-name-migration-sample-follow-up.md) |
| Generated name migration first slice | [2026-0620 generated name migration plan](reports/2026/2026-0620-generated-name-migration-plan.md) |
| Post-security priority decisions and parked authorization gate | [2026-0620 post-security feature priority plan](reports/2026/2026-0620-post-security-feature-priority-plan.md) |

## Finding Rules / 探し方のルール

- Start here when asking "what plans remain?" / 「残っている計画は何か」を見る時はここから始める。
- Use date-less docs for current commitments. / 現在有効な約束は日付なし文書を見る。
- Use dated reports for history, decisions, and implementation records. / 履歴、判断経緯、実装記録は日付付き report を見る。
- Promote a report item into this page when it becomes active or user-facing. / report 内の項目が active または user-facing になったら、このページへ昇格する。
- Move completed items back to dated reports and keep this list short. / 完了項目は日付付き report へ戻し、この一覧は短く保つ。
