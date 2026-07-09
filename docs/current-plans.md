# Current Plans / 現在の計画

English companion:
This page is the active plan index for DegoDB. It should stay short. Completed work lives in dated reports under `docs/reports/`.

このページは DegoDB の現在有効な計画索引です。短く保ちます。完了済み作業は `docs/reports/` 配下の日付付き report に置きます。

Use this page before searching historical reports. / 履歴 report を探す前に、まずこのページを見ます。

## Quick Plan List / 計画リスト

When someone asks for "the plan list", answer from this section first. / 「計画リスト」と聞かれたら、まずこの section から答えます。

### Main Plan / 主計画

Current main status: #464 closes the custom operation route-boundary metadata lane. Review artifact and request publish operations now have inventory docs, route-boundary metadata, runtime/React bridge carry-through, disabled UI wording, and integration coverage. Execution remains separate; the next lane is a shared dispatch preflight before any concrete POST route is enabled. No execution route implementation, build, publish, review-request, approval, mutation, or custom component execution has been added. Push has not been performed for #432-#464. / 現在の主計画ステータス: #464 で custom operation route-boundary metadata lane を closure しました。review artifact と request publish operations は inventory docs、route-boundary metadata、runtime/React bridge carry-through、disabled UI wording、integration coverage を持ちます。execution は別 lane のままで、次は concrete POST route を有効化する前の shared dispatch preflight です。execution route implementation、build、publish、review-request、approval、mutation、custom component execution は追加していません。#432-#464 は push していません。

| Order | Work unit / 作業の塊 | Commit unit / コミット単位 | Status | Rough effort / 目安 |
| --- | --- | --- | --- | --- |
| 459 | Review artifact route boundary metadata carry-through / review artifact route boundary metadata carry-through | Carry `review_source_output_artifact` route boundary into screen/runtime metadata, React bridge handoffs, and dogfooding inspection without execution | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 460 | Current plan history archive split / current plan history archive split | Move completed `current-plans.md` history to a dated archive report and keep this file as active index only | `DONE` | 0.25 day / 0.25 日 |
| 461 | Custom operation disabled UI route-boundary wording / custom operation disabled UI route-boundary wording | Clarify disabled custom operation UI wording now that route-boundary metadata exists, without enabling execution | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 462 | Request publish route boundary inventory / request publish route boundary inventory | Define policy/auth/CSRF/audit/idempotency boundary for `request_source_output_publish` before execution | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 463 | Request publish route boundary metadata carry-through / request publish route boundary metadata carry-through | Carry `request_source_output_publish` route boundary into screen/runtime metadata, React bridge handoffs, and disabled UI wording without execution | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 464 | Custom operation route-boundary lane closure / custom operation route-boundary lane closure | Close the review/publish route-boundary metadata lane, record accepted capability, and decide whether execution routes stay parked or a concrete route cluster is promoted | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 465 | Custom operation execution dispatch preflight / custom operation execution dispatch preflight | Define the shared dispatch boundary and first narrow POST route candidate before enabling any custom operation execution | `ACTIVE_NEXT` | 0.5 - 1 day / 半日 - 1 日 |

### Current Boundary / 現在の境界

- Custom operation metadata can describe identity, availability, unavailable reason, adapter handoff, policy, CSRF, audit, and route-boundary expectations. / custom operation metadata は identity、availability、unavailable reason、adapter handoff、policy、CSRF、audit、route-boundary expectations を記述できます。
- Generated HTML and React bridge handoffs remain metadata-only. / generated HTML と React bridge handoff は metadata-only のままです。
- Generated operator action buttons remain disabled until a separate implementation lane explicitly enables execution. / generated operator action button は、別の implementation lane が明示的に execution を有効化するまで disabled のままです。
- No build, publish, review-request, approval, rollback, mutation, custom component execution, or custom operation dispatch route is currently enabled through this lane. / この lane では build、publish、review-request、approval、rollback、mutation、custom component execution、custom operation dispatch route はまだ有効化していません。
- Push is not performed unless the user explicitly requests it. / user が明示するまで push は行いません。

### Recent Verification / 直近検証

Latest code verification from #459:

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `php -l tests/Integration/NoCodeScreenDefinitionTest.php`
- Focused PHPUnit: `OK (8 tests, 155 assertions)`
- `make sample28-no-code-react-bridge-build-smoke`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 345, Assertions: 11309, Skipped: 1.`
- `git diff --check`

For #460, docs-only verification is `git diff --check`.

Latest code verification from #461:

- PHP syntax checks for changed runtime/screen-definition/dogfooding/test files
- Focused PHPUnit: `OK (8 tests, 160 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 345, Assertions: 11314, Skipped: 1.`
- `git diff --check`

For #462, docs-only verification is `git diff --check`.

Latest code verification from #463:

- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- `php -l tests/Integration/NoCodeScreenDefinitionTest.php`
- Focused PHPUnit: `OK (8 tests, 170 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 345, Assertions: 11324, Skipped: 1.`
- `git diff --check`

For #464, docs-only verification is `git diff --check`.

## Auxiliary Later Review / 補助・後日検討

These are useful candidates, but they are not part of the main plan unless a fresh priority decision promotes them. / これらは有用な候補ですが、新しい優先判断で昇格するまでは主計画には含めません。

| Item / 項目 | Status | Reopen condition / 再開条件 |
| --- | --- | --- |
| Mtool self no-code replacement / Mtool 自身の no-code 置き換え | `PARKED_AFTER_FIRST_PROBE` | Reopen as staged dogfooding probes, not as one broad rewrite. |
| Custom operation execution routes / custom operation execution routes | `PARKED_REPLAN` | Reopen only after policy/auth/CSRF/audit/stale-artifact checks are explicit and testable. |
| Mtool admin/lab route authorization hardening / admin・lab route authorization 強化 | `PARKED_REPLAN` | Replan when a concrete deployment need or one route cluster is ready, with audit/test scope defined. |
| Mtool config store PostgreSQL support / Mtool config store PostgreSQL 対応 | `PARKED` | Reopen only as a config-store portability project, separate from user DB/generated output PostgreSQL support. |
| SQL Server / Oracle current support / SQL Server・Oracle 現行対応 | `PARKED` | Reopen only with explicit enterprise need and support-scope decision. |
| Japanese invoice / billing / compliance sample / 日本向け請求・インボイス sample | `PARKED` | Reopen only after domain review is available. |

## History / 履歴

Completed detailed history was moved out of this active list. / 完了済みの詳細履歴は、この active list から移動しました。

| Completed scope / 完了済み範囲 | Historical source / 履歴ソース |
| --- | --- |
| Custom operation route-boundary lane closure / custom operation route-boundary lane closure | [2026-0708 Custom Operation Route Boundary Lane Closure](reports/2026/2026-0708-custom-operation-route-boundary-lane-closure.md) |
| Request publish route boundary metadata carry-through / request publish route boundary metadata carry-through | [2026-0708 Request Publish Route Boundary Metadata Carry-Through](reports/2026/2026-0708-request-publish-route-boundary-metadata-carry-through.md) |
| Request publish route boundary inventory / request publish route boundary inventory | [2026-0708 Request Publish Route Boundary Inventory](reports/2026/2026-0708-request-publish-route-boundary-inventory.md) |
| Full `current-plans.md` history through #459 / #459 までの `current-plans.md` 全履歴 | [2026-0708 Current Plan History Through #459](reports/2026/2026-0708-current-plan-history-through-459.md) |
| Custom operation disabled UI route-boundary wording / custom operation disabled UI route-boundary wording | [2026-0708 Custom Operation Disabled Route Boundary Wording](reports/2026/2026-0708-custom-operation-disabled-route-boundary-wording.md) |
| Review artifact route boundary metadata carry-through / review artifact route boundary metadata carry-through | [2026-0708 Review Artifact Route Boundary Metadata Carry-Through](reports/2026/2026-0708-review-artifact-route-boundary-metadata-carry-through.md) |
| Review artifact route boundary inventory / review artifact route boundary inventory | [2026-0708 Review Artifact Route Boundary Inventory](reports/2026/2026-0708-review-artifact-route-boundary-inventory.md) |
| Custom operation metadata / adapter handoff lane closure / custom operation metadata・adapter handoff lane closure | [2026-0708 Custom Operation Metadata Adapter Handoff Lane Closure](reports/2026/2026-0708-custom-operation-metadata-adapter-handoff-lane-closure.md) |
| Mtool no-code dogfooding and custom extension lane / Mtool no-code dogfooding・custom extension lane | [2026-0708 Mtool No-Code Dogfooding Probe Inventory](reports/2026/2026-0708-mtool-no-code-dogfooding-probe-inventory.md), [2026-0708 Local Stack Review After Custom Operation Adapter Handoff](reports/2026/2026-0708-local-stack-review-after-custom-operation-adapter-handoff.md) |
| Earlier no-code runtime, public delivery, runtime-data, and packaging work / 以前の no-code runtime・public delivery・runtime-data・packaging 作業 | See `docs/reports/2026/README.md` and the archived through-#459 plan history. |

## Status Meanings / 状態の意味

| Status | Meaning / 意味 |
| --- | --- |
| `ACTIVE_NEXT` | Recommended next work / 次に進める主線 |
| `TODO_AFTER_REPLAN` | Planned placeholder whose concrete scope and estimate are decided by the preceding replan / 直前の replan で具体 scope と見積もりを決める placeholder |
| `DONE` | Completed and retained here only when it anchors the current next work / 完了済み。現在の次作業の基準として必要な場合だけここに残す |
| `PARKED` | Intentionally deferred and not part of the quick plan list / 意図的に保留し、quick plan list には入れない |
| `PARKED_REPLAN` | Deferred until a fresh scope / value / risk decision is made / scope・価値・risk を再判断するまで保留 |

## Finding Rules / 探し方のルール

- Start here when asking "what plans remain?" / 「残っている計画は何か」を見る時はここから始める。
- Use date-less docs for current commitments. / 現在有効な約束は日付なし文書を見る。
- Use dated reports for history, decisions, and implementation records. / 履歴、判断経緯、実装記録は日付付き report を見る。
- Promote a report item into this page only when it becomes active or user-facing. / report 内の項目が active または user-facing になった時だけ、このページへ昇格する。
- Move completed items back to dated reports and keep this list short. / 完了項目は日付付き report へ戻し、この一覧は短く保つ。
