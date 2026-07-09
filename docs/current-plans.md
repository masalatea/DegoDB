# Current Plans / 現在の計画

English companion:
This page is the active plan index for DegoDB. It should stay short. Completed work lives in dated reports under `docs/reports/`.

このページは DegoDB の現在有効な計画索引です。短く保ちます。完了済み作業は `docs/reports/` 配下の日付付き report に置きます。

Use this page before searching historical reports. / 履歴 report を探す前に、まずこのページを見ます。

## Quick Plan List / 計画リスト

When someone asks for "the plan list", answer from this section first. / 「計画リスト」と聞かれたら、まずこの section から答えます。

### Main Plan / 主計画

Current main status: #491 checkpoints the no-push local stack after guard-first hardening. The local stack is now 59 commits ahead of `origin/develop`; further local commits should wait for explicit user direction: cleanup, push, or a new named non-executable lane. Availability enablement remains parked, generated buttons remain disabled, and push has not been performed for #432-#491. / 現在の主計画ステータス: #491 で guard-first hardening 後の no-push local stack を checkpoint しました。local stack は `origin/develop` より 59 commits ahead です。これ以上の local commit は、cleanup、push、または新しい名前付き non-executable lane について user の明示指示を待ちます。availability enablement は parked、generated button は disabled のまま、#432-#491 は push していません。

| Order | Work unit / 作業の塊 | Commit unit / コミット単位 | Status | Rough effort / 目安 |
| --- | --- | --- | --- | --- |
| 459 | Review artifact route boundary metadata carry-through / review artifact route boundary metadata carry-through | Carry `review_source_output_artifact` route boundary into screen/runtime metadata, React bridge handoffs, and dogfooding inspection without execution | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 460 | Current plan history archive split / current plan history archive split | Move completed `current-plans.md` history to a dated archive report and keep this file as active index only | `DONE` | 0.25 day / 0.25 日 |
| 461 | Custom operation disabled UI route-boundary wording / custom operation disabled UI route-boundary wording | Clarify disabled custom operation UI wording now that route-boundary metadata exists, without enabling execution | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 462 | Request publish route boundary inventory / request publish route boundary inventory | Define policy/auth/CSRF/audit/idempotency boundary for `request_source_output_publish` before execution | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 463 | Request publish route boundary metadata carry-through / request publish route boundary metadata carry-through | Carry `request_source_output_publish` route boundary into screen/runtime metadata, React bridge handoffs, and disabled UI wording without execution | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 464 | Custom operation route-boundary lane closure / custom operation route-boundary lane closure | Close the review/publish route-boundary metadata lane, record accepted capability, and decide whether execution routes stay parked or a concrete route cluster is promoted | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 465 | Custom operation execution dispatch preflight / custom operation execution dispatch preflight | Define the shared dispatch boundary and first narrow POST route candidate before enabling any custom operation execution | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 466 | Review artifact plan-only dispatch guard first slice / review artifact plan-only dispatch guard first slice | Add a code-backed dispatch helper and guard tests for `review_source_output_artifact` without enabling mutation or generated button execution | `DONE` | 1 - 1.5 days / 1 - 1.5 日 |
| 467 | Review artifact HTTP route guard wrapper / review artifact HTTP route guard wrapper | Add a narrow POST route/controller wrapper that calls the dispatch helper and returns blocked/plan-only responses without mutation or generated button execution | `DONE` | 1 - 1.5 days / 1 - 1.5 日 |
| 468 | Review artifact HTTP guard smoke coverage / review artifact HTTP guard smoke coverage | Add focused HTTP-level guard coverage for CSRF/deferred result rendering and decide whether blocked audit append belongs in the wrapper or a later persistence slice | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 469 | Custom operation blocked audit append first slice / custom operation blocked audit append first slice | Append audit records for blocked/deferred/unauthorized/stale route guard outcomes without enabling mutation | `DONE` | 1 - 1.5 days / 1 - 1.5 日 |
| 470 | Custom operation audit append failure handling / custom operation audit append failure handling | Add focused failure/response behavior coverage for operation audit append before route guard lane closure | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 471 | Review artifact route guard lane closure / review artifact route guard lane closure | Close the review-artifact route guard lane, record accepted capability, and decide whether availability enablement remains parked | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 472 | Local commit stack review after review route guard / review route guard 後の local commit stack review | Review the unpushed local commit stack and decide whether to keep, squash, or replan before any push | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 473 | Post route guard replan / route guard 後の replan | Decide whether to keep execution parked, promote review workflow persistence, improve disabled UI explanation, or prepare a push decision | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 474 | Review workflow persistence inventory / review workflow persistence inventory | Define storage/idempotency/stale-artifact/audit boundary for review workflow persistence before enabling availability or mutation | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 475 | Review workflow persistence repository first slice / review workflow persistence repository first slice | Add repository-first persistence storage/tests for review workflow requests without enabling route mutation or generated buttons | `DONE` | 1 - 1.5 days / 1 - 1.5 日 |
| 476 | Review workflow persistence route integration preflight / review workflow persistence route integration preflight | Decide and test how the existing review route guard will call repository persistence while keeping generated buttons disabled | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 477 | Review workflow route persistence helper first slice / review workflow route persistence helper first slice | Add a route-local helper and focused coverage for accepted-plan persistence, while default dogfooding availability stays deferred | `DONE` | 0.5 - 1 day / 半日 - 1 日 |
| 478 | Review workflow route persistence helper lane closure / review workflow route persistence helper lane closure | Close the route-local persistence helper lane and decide whether availability enablement should remain parked or be promoted | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 479 | Local stack review after review workflow persistence helper / review workflow persistence helper 後の local stack review | Review the unpushed local commit stack and decide whether to keep, squash, push, or replan before availability enablement | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 480 | Review workflow availability enablement replan / review workflow availability enablement replan | Decide whether to keep availability parked, prepare a push decision, or promote a narrowly tested executable review workflow slice | `DONE` | 0.25 - 0.5 day / 0.25 - 0.5 日 |
| 481 | Explicit push decision for no-code dogfooding stack / no-code dogfooding stack の明示 push 判断 | Decide whether to push the current develop stack as-is, hold it locally, or request a focused cleanup before push | `DONE` | 0.25 day / 0.25 日 |
| 482 | Continue locally without push / PUSH なしで local 継続 | Record the no-push continuation decision and promote a small non-executable follow-up lane | `DONE` | 0.25 day / 0.25 日 |
| 483 | Review workflow persistence failure visibility coverage / review workflow persistence failure visibility coverage | Add focused coverage for route-local persistence failure rendering/audit behavior without enabling availability or generated buttons | `DONE` | 0.5 day / 半日 |
| 484 | Review workflow persistence failure visibility lane closure / review workflow persistence failure visibility lane closure | Close the failure visibility slice and decide whether any further non-executable hardening is useful before availability remains parked | `DONE` | 0.25 day / 0.25 日 |
| 485 | Review workflow non-executable hardening replan / review workflow non-executable hardening replan | Decide whether to add more non-executable hardening, pause for push/cleanup, or keep availability parked with no further local commits | `DONE` | 0.25 day / 0.25 日 |
| 486 | Review workflow persistence audit append coverage / review workflow persistence audit append coverage | Add focused coverage that accepted and duplicate persisted review requests append audit records with review request metadata | `DONE` | 0.5 day / 半日 |
| 487 | Review workflow persistence audit append lane closure / review workflow persistence audit append lane closure | Close audit append coverage and decide whether to pause local commits or add another non-executable hardening slice | `DONE` | 0.25 day / 0.25 日 |
| 488 | No-push local work checkpoint / PUSH なし local 作業 checkpoint | Decide the next explicit no-push direction after the review workflow non-executable hardening stack | `DONE` | 0.25 day / 0.25 日 |
| 489 | Review workflow guard-first persistence skip matrix / review workflow guard-first persistence skip matrix | Add focused coverage that stale, unauthorized, missing-CSRF, and other non-allowed guard results never persist review requests | `DONE` | 0.5 day / 半日 |
| 490 | Review workflow guard-first skip matrix lane closure / review workflow guard-first skip matrix lane closure | Close the guard-first skip matrix slice and decide whether to pause or continue non-executable hardening | `DONE` | 0.25 day / 0.25 日 |
| 491 | No-push stack checkpoint after guard-first hardening / guard-first hardening 後の no-push stack checkpoint | Decide whether to pause local commits, request cleanup, or continue only with explicitly selected non-executable work | `DONE` | 0.25 day / 0.25 日 |
| 492 | Await explicit next instruction / 次の明示指示待ち | Wait for the user to choose cleanup, push, or a named non-executable follow-up lane | `ACTIVE_NEXT` | 0.25 day / 0.25 日 |

### Current Boundary / 現在の境界

- Custom operation metadata can describe identity, availability, unavailable reason, adapter handoff, policy, CSRF, audit, and route-boundary expectations. / custom operation metadata は identity、availability、unavailable reason、adapter handoff、policy、CSRF、audit、route-boundary expectations を記述できます。
- Review workflow request storage now exists as repository-first config DB persistence, including duplicate reuse for open requests. / review workflow request storage は repository-first config DB persistence として存在し、open request の duplicate reuse も含みます。
- The route integration rule is guard-first: repository persistence is reachable only from an allowed `accepted_plan`, not from deferred/blocked guard results. / route integration rule は guard-first です。repository persistence に到達できるのは allowed な `accepted_plan` からだけで、deferred / blocked guard result からは到達しません。
- A route-local helper now persists or reuses review requests for accepted-plan results, and exposes `recorded` / `duplicate` / `failed` / `skipped` status to the result page. / route-local helper は accepted-plan result の review request を persist または reuse し、result page に `recorded` / `duplicate` / `failed` / `skipped` status を公開します。
- Generated HTML and React bridge handoffs remain metadata-only. / generated HTML と React bridge handoff は metadata-only のままです。
- Generated operator action buttons remain disabled until a separate implementation lane explicitly enables execution. / generated operator action button は、別の implementation lane が明示的に execution を有効化するまで disabled のままです。
- Availability enablement is parked while the current 59-commit local stack remains unpushed. / 現在の 59 commit local stack が unpushed の間、availability enablement は parked です。
- The current push decision is to hold locally; no push is performed without a new explicit user request. / 現在の push 判断は local hold です。新しい明示的な user request がない限り push は行いません。
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

For #465, docs-only verification is `git diff --check`.

Latest code verification from #466:

- `php -l mtool/app/no_code_custom_operation_dispatch.php`
- `php -l mtool/app/project_permission.php`
- `php -l tests/Integration/NoCodeCustomOperationDispatchTest.php`
- Focused PHPUnit: `OK (6 tests, 54 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 351, Assertions: 11378, Skipped: 1.`
- `git diff --check`

Latest code verification from #467:

- `php -l mtool/app/project_source_output_operation_page.php`
- `php -l mtool/app/http.php`
- `php -l mtool/app/router.php`
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`
- Focused PHPUnit route contract: `OK (24 tests, 1908 assertions)`
- Focused PHPUnit dispatch helper: `OK (6 tests, 54 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 351, Assertions: 11384, Skipped: 1.`
- `git diff --check`

Latest code verification from #468:

- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`
- Focused PHPUnit route contract and guard smoke: `OK (25 tests, 1914 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 351, Assertions: 11384, Skipped: 1.`
- `git diff --check`

Latest code verification from #469:

- `php -l mtool/app/project_source_output_operation_page.php`
- `php -l tests/Integration/AuditLogRepositorySqliteTest.php`
- Focused PHPUnit audit append: `OK (2 tests, 18 assertions)`
- Focused PHPUnit route contract and guard smoke: `OK (25 tests, 1914 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 353, Assertions: 11397, Skipped: 1.`
- `git diff --check`

Latest code verification from #470:

- `php -l mtool/app/project_source_output_operation_page.php`
- `php -l tests/Integration/AuditLogRepositorySqliteTest.php`
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`
- Focused PHPUnit audit append: `OK (3 tests, 23 assertions)`
- Focused PHPUnit route contract and guard smoke: `OK (26 tests, 1918 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 355, Assertions: 11406, Skipped: 1.`
- `git diff --check`

For #471, docs-only verification is `git diff --check`.

For #472, docs-only verification is `git diff --check`.

For #473, docs-only verification is `git diff --check`.

For #474, docs-only verification is `git diff --check`.

Latest code verification from #475:

- `php -l mtool/app/no_code_review_workflow_repository.php`
- `php -l mtool/app/no_code_review_workflow_repository_pdo.php`
- `php -l mtool/app/config_db_bootstrap.php`
- `php -l tests/Integration/NoCodeReviewWorkflowRepositorySqliteTest.php`
- Focused PHPUnit review workflow repository: `OK (2 tests, 23 assertions)`
- Focused PHPUnit config DB bootstrap: `OK (1 test, 6 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 357, Assertions: 11429, Skipped: 1.`
- `git diff --check`

For #476, docs-only verification is `git diff --check`.

Latest code verification from #477:

- `php -l mtool/app/project_source_output_operation_page.php`
- `php -l tests/Integration/ProjectSourceOutputOperationPersistenceTest.php`
- Focused PHPUnit route persistence helper: `OK (2 tests, 16 assertions)`
- Focused PHPUnit source output route contract: `OK (26 tests, 1918 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 359, Assertions: 11445, Skipped: 1.`
- `git diff --check`

For #478, docs-only verification is `git diff --check`.

For #479, docs-only verification is `git diff --check`.

For #480, docs-only verification is `git diff --check`.

For #481, docs-only verification is `git diff --check`.

For #482, docs-only verification is `git diff --check`.

Latest code verification from #483:

- `php -l tests/Integration/ProjectSourceOutputOperationPersistenceTest.php`
- Focused PHPUnit route persistence helper failure visibility: `OK (4 tests, 28 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 361, Assertions: 11457, Skipped: 1.`
- `git diff --check`

For #484, docs-only verification is `git diff --check`.

For #485, docs-only verification is `git diff --check`.

Latest code verification from #486:

- `php -l tests/Integration/ProjectSourceOutputOperationPersistenceTest.php`
- Focused PHPUnit route persistence audit append coverage: `OK (5 tests, 35 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 362, Assertions: 11464, Skipped: 1.`
- `git diff --check`

For #487, docs-only verification is `git diff --check`.

For #488, docs-only verification is `git diff --check`.

Latest code verification from #489:

- `php -l tests/Integration/ProjectSourceOutputOperationPersistenceTest.php`
- Focused PHPUnit guard-first persistence skip matrix: `OK (6 tests, 50 assertions)`
- Full `make test`: attempted twice, then retried outside the sandbox; each run stalled while Docker was loading metadata for `docker.io/library/ubuntu:24.04` and was interrupted.
- `git diff --check`

For #490, docs-only verification is `git diff --check`.

For #491, docs-only verification is `git diff --check`.

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
| No-push stack checkpoint after guard-first hardening / guard-first hardening 後の no-push stack checkpoint | [2026-0709 No-Push Stack Checkpoint After Guard-First Hardening](reports/2026/2026-0709-no-push-stack-checkpoint-after-guard-first-hardening.md) |
| Review workflow guard-first skip matrix lane closure / review workflow guard-first skip matrix lane closure | [2026-0709 Review Workflow Guard-First Skip Matrix Lane Closure](reports/2026/2026-0709-review-workflow-guard-first-skip-matrix-lane-closure.md) |
| Review workflow guard-first persistence skip matrix / review workflow guard-first persistence skip matrix | [2026-0709 Review Workflow Guard-First Persistence Skip Matrix](reports/2026/2026-0709-review-workflow-guard-first-persistence-skip-matrix.md) |
| No-push local work checkpoint / PUSH なし local 作業 checkpoint | [2026-0709 No-Push Local Work Checkpoint](reports/2026/2026-0709-no-push-local-work-checkpoint.md) |
| Review workflow persistence audit append lane closure / review workflow persistence audit append lane closure | [2026-0709 Review Workflow Persistence Audit Append Lane Closure](reports/2026/2026-0709-review-workflow-persistence-audit-append-lane-closure.md) |
| Review workflow persistence audit append coverage / review workflow persistence audit append coverage | [2026-0709 Review Workflow Persistence Audit Append Coverage](reports/2026/2026-0709-review-workflow-persistence-audit-append-coverage.md) |
| Review workflow non-executable hardening replan / review workflow non-executable hardening replan | [2026-0709 Review Workflow Non-Executable Hardening Replan](reports/2026/2026-0709-review-workflow-non-executable-hardening-replan.md) |
| Review workflow persistence failure visibility lane closure / review workflow persistence failure visibility lane closure | [2026-0709 Review Workflow Persistence Failure Visibility Lane Closure](reports/2026/2026-0709-review-workflow-persistence-failure-visibility-lane-closure.md) |
| Review workflow persistence failure visibility coverage / review workflow persistence failure visibility coverage | [2026-0709 Review Workflow Persistence Failure Visibility Coverage](reports/2026/2026-0709-review-workflow-persistence-failure-visibility-coverage.md) |
| Continue locally without push / PUSH なしで local 継続 | [2026-0709 Continue Locally Without Push](reports/2026/2026-0709-continue-locally-without-push.md) |
| Explicit push decision for no-code dogfooding stack / no-code dogfooding stack の明示 push 判断 | [2026-0709 Explicit Push Decision For No-Code Dogfooding Stack](reports/2026/2026-0709-explicit-push-decision-for-no-code-dogfooding-stack.md) |
| Review workflow availability enablement replan / review workflow availability enablement replan | [2026-0708 Review Workflow Availability Enablement Replan](reports/2026/2026-0708-review-workflow-availability-enablement-replan.md) |
| Local stack review after review workflow persistence helper / review workflow persistence helper 後の local stack review | [2026-0708 Local Stack Review After Review Workflow Persistence Helper](reports/2026/2026-0708-local-stack-review-after-review-workflow-persistence-helper.md) |
| Review workflow route persistence helper lane closure / review workflow route persistence helper lane closure | [2026-0708 Review Workflow Route Persistence Helper Lane Closure](reports/2026/2026-0708-review-workflow-route-persistence-helper-lane-closure.md) |
| Review workflow route persistence helper first slice / review workflow route persistence helper first slice | [2026-0708 Review Workflow Route Persistence Helper First Slice](reports/2026/2026-0708-review-workflow-route-persistence-helper-first-slice.md) |
| Review workflow persistence route integration preflight / review workflow persistence route integration preflight | [2026-0708 Review Workflow Persistence Route Integration Preflight](reports/2026/2026-0708-review-workflow-persistence-route-integration-preflight.md) |
| Review workflow persistence repository first slice / review workflow persistence repository first slice | [2026-0708 Review Workflow Persistence Repository First Slice](reports/2026/2026-0708-review-workflow-persistence-repository-first-slice.md) |
| Review workflow persistence inventory / review workflow persistence inventory | [2026-0708 Review Workflow Persistence Inventory](reports/2026/2026-0708-review-workflow-persistence-inventory.md) |
| Post review route guard replan / review route guard 後の replan | [2026-0708 Post Review Route Guard Replan](reports/2026/2026-0708-post-review-route-guard-replan.md) |
| Local stack review after review artifact route guard / review artifact route guard 後の local stack review | [2026-0708 Local Stack Review After Review Artifact Route Guard](reports/2026/2026-0708-local-stack-review-after-review-artifact-route-guard.md) |
| Review artifact route guard lane closure / review artifact route guard lane closure | [2026-0708 Review Artifact Route Guard Lane Closure](reports/2026/2026-0708-review-artifact-route-guard-lane-closure.md) |
| Custom operation audit append failure handling / custom operation audit append failure handling | [2026-0708 Custom Operation Audit Append Failure Handling](reports/2026/2026-0708-custom-operation-audit-append-failure-handling.md) |
| Custom operation blocked audit append first slice / custom operation blocked audit append first slice | [2026-0708 Custom Operation Blocked Audit Append First Slice](reports/2026/2026-0708-custom-operation-blocked-audit-append-first-slice.md) |
| Review artifact HTTP guard smoke coverage / review artifact HTTP guard smoke coverage | [2026-0708 Review Artifact HTTP Guard Smoke Coverage](reports/2026/2026-0708-review-artifact-http-guard-smoke-coverage.md) |
| Review artifact HTTP route guard wrapper / review artifact HTTP route guard wrapper | [2026-0708 Review Artifact HTTP Route Guard Wrapper](reports/2026/2026-0708-review-artifact-http-route-guard-wrapper.md) |
| Review artifact plan-only dispatch guard first slice / review artifact plan-only dispatch guard first slice | [2026-0708 Review Artifact Plan-Only Dispatch Guard First Slice](reports/2026/2026-0708-review-artifact-plan-only-dispatch-guard-first-slice.md) |
| Custom operation execution dispatch preflight / custom operation execution dispatch preflight | [2026-0708 Custom Operation Execution Dispatch Preflight](reports/2026/2026-0708-custom-operation-execution-dispatch-preflight.md) |
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
