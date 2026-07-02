# Current Plans / 現在の計画

English companion:
This page lists only unfinished or deferred plans. Completed work is kept in dated reports.

This page is the active plan index for DegoDB. / このページは、DegoDB の現在有効な計画索引です。

Use this page before searching historical reports. / 履歴 report を探す前に、まずこのページを見ます。

## Quick Plan List / 計画リスト

When someone asks for "the plan list", answer from this section first. / 「計画リスト」と聞かれたら、まずこの section から答えます。

| Order | Work unit / 作業の塊 | Commit unit / コミット単位 | Status |
| --- | --- | --- | --- |
| 1 | Post-runtime error/retry visibility no-code product goal replan / runtime error/retry visibility 後の product goal 再計画 | Choose the next small no-code product-facing implementation after runtime error/retry visibility | `ACTIVE_NEXT` |
| 2 | Mtool implementation namespace cleanup / Mtool 実装 namespace cleanup | Boundary inventory is recorded; no implementation is recommended until a specific helper cluster or maintenance goal is chosen | `PARKED_REPLAN` |

The first sync-backed no-code demonstration is complete as `sample30-no-code-app-local-sync-demo`, the narrow server-side sync processing follow-up first slice is complete inside sample30, reusable partial-update server merge policy is complete for the first slice, sync handoff visibility polish is complete for the first slice, the first operator/admin no-code workflow slice is complete as an inspection-only source-output summary, operator preview health/detail links are complete for the first slice, read-only source-output artifact detail is complete for the first slice, sync error-state visibility is complete for the first slice, operator failed-sync inspection is complete for the first slice, operator sync outbox detail is complete for the first slice, sync retry eligibility guard is complete for the first slice, operator sync retry action is complete for the first slice, retry processing smoke is complete for the first slice, operator retry feedback polish is complete for the first slice, and no-code runtime error/retry visibility is complete for the first slice. The next active step is a short post-runtime-error/retry-visibility product goal replan. / 最初の sync-backed no-code demonstration は `sample30-no-code-app-local-sync-demo` として完了し、narrow な server-side sync processing follow-up first slice も sample30 内で完了、reusable partial-update server merge policy も first slice として完了、sync handoff visibility polish も first slice として完了、operator/admin no-code workflow first slice も inspection-only な source-output summary として完了、operator preview health/detail links も first slice として完了、read-only source-output artifact detail も first slice として完了し、sync error-state visibility も first slice として完了、operator failed-sync inspection も first slice として完了、operator sync outbox detail も first slice として完了、sync retry eligibility guard も first slice として完了、operator sync retry action も first slice として完了、retry processing smoke も first slice として完了、operator retry feedback polish も first slice として完了、no-code runtime error/retry visibility も first slice として完了しました。次の active step は runtime error/retry visibility 後の短い product goal replan です。

## Priority Rationale / 優先理由

The OSS / consulting readiness docs package has been completed as the documentation-first step. / OSS・導入支援 readiness 資料 package は、ドキュメント先行の作業として完了しました。

AI context source output is implemented, verified across all tutorial samples, and available as an implicit default companion documentation output for current/new projects. Mtool self-output verification publishes AI context documentation for Mtool itself. / AI 文脈 Source Output は全 tutorial sample に展開済みで、現在・新規 project へ暗黙 default companion documentation output として提供します。Mtool 自身についても AI 文脈ドキュメントを self-output して検証しています。

The physical/logical sample naming migration, PostgreSQL Input / Output support, and generated PHP output namespace support are complete for their intended support boundaries. Generated PHP output namespace support adds an optional project-level namespace setting for DataClass / DBAccess output, keeps the default namespace-free, and verifies mixed sample coverage. Broader Mtool implementation namespace cleanup remains deferred. / physical / logical sample 命名移行、PostgreSQL Input / Output 対応、generated PHP output namespace 対応は、意図した support boundary では完了です。generated PHP output namespace 対応では、DataClass / DBAccess output 向けの任意 project-level namespace 設定を追加し、default は namespace なしのまま、sample coverage は混在で検証しました。Mtool 実装全体の namespace cleanup は後回しです。

App local DB / sync / no-code app roadmap and feasibility study catalog are drafted as dated reports. During planning, auth foundation was identified as useful beyond that roadmap, so it was promoted out of the FS group into a normal first-slice feature/foundation plan. Mtool auth foundation first slice is complete. Gate 0 core feasibility studies are also complete: Shared Contract Manifest Spike, App Local SQLite Schema Spike, and DTO Save/Read Spike. The FS result supports the original design assumption that generated DataClass and shared contract should be separate artifacts: DataClass remains implementation-facing, while shared contract metadata carries persistence / sync / no-code semantics. Shared Contract Core Vocabulary and Shared DataClass contract foundation are complete: manifest v0 vocabulary, validator, explicit contract metadata tables, DataClass + table metadata manifest generation, DataClass shape compare, `shared-contract-json`, and first `shared-contract-typescript` DTO output are implemented. App-local persistence first demo and Source Output artifact slices are complete through sample27: server row -> DTO -> App-local SQLite save -> App-local SQLite read is verified, and `app-local-persistence-php` now emits schema / manifest / summary / PHP wrapper artifacts. Managed data operation layer first-slice spine is complete through sample07 coverage: canonical operation / operation-field tables, PDO repository, fail-closed policy evaluator, `managed-operation-docs-md` Source Output artifact, plan-only execution adapter, sync intent skeleton, sync outbox lifecycle, App-local executor / handler, server DBAccess executor / handler, project catalog binding, real generated DBAccess coverage, and sample07 managed operation coverage are in place. No-code screen definition and runtime MVP is complete for the minimal steps 1-8 path: `no-code-screen-definition-v0`, the first `no-code-runtime-v0` render/dispatch adapter, `no-code-runtime-json` artifact publishing, sample07 artifact generation/publish verification, persisted operation flow via generated sample07 DBAccess, minimal HTML preview rendering, basic UI smoke, and browser/headless update dispatch smoke are implemented. The first user-facing no-code app MVP sample, `sample28-no-code-data-app-mvp`, is also complete through generated list/detail/form smoke and pack verification. The post-sample28 product-goal replan chose generated runtime UX polish as the next small product-facing slice before broader domain, sync, or operator workflow expansion. That lane is now complete through readable generated titles/subtitles, empty-state copy, browser action feedback, runtime/screen/action state badges, working/success/error feedback states, and refreshed sample07/sample28 smokes. The second data-first no-code domain sample, `sample29-no-code-support-case-demo`, is complete as a first slice: it applies the same generated runtime path to a support-case domain with read-model context fields, no-code metadata, pack/runtime smoke, and browser UI smoke. The first sync-backed no-code demonstration, `sample30-no-code-app-local-sync-demo`, is also complete: generated no-code action intent becomes a managed operation sync intent, enters the sync outbox, and is processed by the App-local SQLite handler. The server-side sync processing follow-up first slice is also complete inside sample30: a second sync outbox item is processed by generated server DBAccess and updates a server SQLite row without adding transport or conflict resolution. / App 内 DB・同期・no-code app roadmap と feasibility study catalog は日付付き report として作成済みです。検討の中で auth 基盤はその roadmap に限定されず通常機能としても有用だと分かったため、FS 群から外して正式な first-slice 計画へ格上げしました。Mtool auth 基盤 first slice は完了済みです。Gate 0 core FS も Shared Contract Manifest Spike、App Local SQLite Schema Spike、DTO Save/Read Spike まで完了し、generated DataClass と shared contract は別 artifact として扱うべき、という元の見立てを支持しました。DataClass は implementation-facing、shared contract metadata は persistence / sync / no-code semantics の正本として扱います。Shared Contract Core Vocabulary と Shared DataClass contract 基盤は完了し、manifest v0 語彙、validator、明示 contract metadata table、DataClass + table metadata からの manifest 生成、DataClass shape compare、`shared-contract-json`、最初の `shared-contract-typescript` DTO output を実装済みです。App-local persistence first demo と Source Output artifact slice は sample27 まで完了し、server row -> DTO -> App-local SQLite save -> App-local SQLite read を検証済み、`app-local-persistence-php` は schema / manifest / summary / PHP wrapper artifact を出力します。Managed data operation layer first-slice spine は sample07 coverage まで完了し、canonical operation / operation-field table、PDO repository、fail-closed policy evaluator、`managed-operation-docs-md` Source Output artifact、plan-only execution adapter、sync intent skeleton、sync outbox lifecycle、App-local executor / handler、server DBAccess executor / handler、project catalog binding、real generated DBAccess coverage、sample07 managed operation coverage を追加済みです。No-code screen definition・runtime MVP は minimal steps 1-8 path として完了し、`no-code-screen-definition-v0`、最初の `no-code-runtime-v0` render/dispatch adapter、`no-code-runtime-json` artifact publishing、sample07 artifact 生成 / publish 検証、generated sample07 DBAccess 経由の persisted operation flow、最小 HTML preview rendering、basic UI smoke、browser/headless update dispatch smoke を実装済みです。最初の user-facing no-code app MVP sample である `sample28-no-code-data-app-mvp` も、generated list/detail/form smoke と pack verification まで完了しました。sample28 後の product-goal replan では、より広い domain / sync / operator workflow 拡張の前に、小さな product-facing slice として generated runtime UX polish を選びました。その lane は readable な generated title/subtitle、empty-state copy、browser action feedback、runtime / screen / action state badge、working / success / error feedback state、sample07/sample28 smoke 更新まで完了しました。2 つ目の data-first no-code domain sample である `sample29-no-code-support-case-demo` は first slice として完了し、read-model context field を持つ support-case domain、no-code metadata、pack/runtime smoke、browser UI smoke で同じ generated runtime path を検証しました。最初の sync-backed no-code demonstration である `sample30-no-code-app-local-sync-demo` も完了し、generated no-code action intent が managed operation sync intent になり、sync outbox に入り、App-local SQLite handler で処理されることを確認しました。server-side sync processing follow-up first slice も sample30 内で完了し、2 件目の sync outbox item を generated server DBAccess で処理して server SQLite row を更新しました。transport や conflict resolution は追加していません。

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
| 9 | Mtool auth foundation first slice / Mtool auth 基盤 first slice | Completed / 完了 | Added `mtool/app/auth_foundation.php` with old `ProjectUser` read/write bit inventory, role-based permission keys, normalized principal shape, and all-pass / fail-closed authorization evaluator. Covered by `AuthFoundationContractTest`. |
| 10 | Gate 0 feasibility studies / Gate 0 FS 群 | Completed / 完了 | Completed Shared Contract Manifest Spike, App Local SQLite Schema Spike, and DTO Save/Read Spike. Result: DataClass can describe generated DTO shape, but shared contract metadata must separately carry nullable / default / key / persistence / sync semantics. |
| 11 | Shared Contract Core Vocabulary / shared contract 最小語彙 | Completed / 完了 | Added shared contract manifest v0 vocabulary, validator, local metadata collision policy, and sample02/task fixture test. |
| 12 | Shared DataClass contract foundation / shared DataClass contract 基盤 | Completed / 完了 | Added explicit shared contract metadata tables/repository, language-neutral contract manifest builder from DataClass + table metadata, DataClass shape compare, `shared-contract-json` source output, and first `shared-contract-typescript` DTO output. |
| 13 | App-local persistence first demo / App-local persistence first demo | Completed / 完了 | Added App-local SQLite schema generator/apply, generic DTO save/read DBAccess helper, and sample27 `server read -> DTO -> app save -> app read` PDO harness. |
| 14 | App-local persistence source output artifacts / App-local persistence Source Output artifacts | Completed / 完了 | Added `app-local-persistence-php` Source Output artifact generation for schema / manifest / summary / PHP wrappers, plus sample27 source output seed and artifact verification. |
| 15 | Managed data operation layer / managed data operation layer | Completed for first-slice spine / first-slice spine 完了 | Operation / operation-field metadata, repository, policy evaluator, generated operation docs artifact, plan-only execution adapter, sync intent skeleton, sync outbox lifecycle, App-local executor / handler, server DBAccess executor / handler, server DBAccess binding discovery, candidate selection, project catalog wiring, real generated DBAccess coverage, and sample07 managed operation coverage are in place. |
| 16 | No-code screen definition and runtime MVP / no-code screen definition・runtime MVP | Completed for minimal steps 1-8 path / minimal steps 1-8 path 完了 | `no-code-screen-definition-v0`, first `no-code-runtime-v0` render/dispatch adapter, `no-code-runtime-json` artifact publishing with HTML preview, sample07 artifact generation/publish verification, a persisted operation flow, basic UI smoke, and browser/headless update dispatch smoke are in place. |
| 17 | No-code app sample / no-code app sample | Completed for first MVP / first MVP 完了 | `sample28-no-code-data-app-mvp` proves the first data-first no-code behavior path through generated list/detail/form smoke and pack verification. |
| 18 | Mtool implementation namespace cleanup / Mtool 実装 namespace cleanup | Replan only / 再計画のみ | Boundary inventory recorded 365 PHP files, about 3152 top-level functions, and about 1238 include lines across the implementation surface. Do not start repo-wide migration without a scoped helper cluster and compatibility shim policy. |
| 19 | Next no-code product goal replan / 次の no-code product goal 再計画 | Completed / 完了 | Chose generated runtime UX polish as the next product-facing no-code goal and promoted it into Quick Plan. Decision report: [2026-0630 Next No-Code Product Goal Replan](reports/2026/2026-0630-next-no-code-product-goal-replan.md). |
| 20 | Generated no-code runtime UX polish first slice / generated no-code runtime UX polish first slice | Completed / 完了 | Generated runtime preview now has readable titles/subtitles, deterministic empty-state copy, browser action feedback, and refreshed sample07/sample28 smoke coverage. Report: [2026-0630 Generated No-Code Runtime UX Polish First Slice](reports/2026/2026-0630-generated-no-code-runtime-ux-polish-first-slice.md). |
| 21 | Generated no-code runtime state polish follow-up / generated no-code runtime state polish follow-up | Completed / 完了 | Runtime preview now exposes ready/error preview state, ready/empty screen state, idle/working/success/error action feedback state, and smoke coverage for generated state attributes. Report: [2026-0630 Generated No-Code Runtime State Polish Follow-Up](reports/2026/2026-0630-generated-no-code-runtime-state-polish-follow-up.md). |
| 22 | Next no-code product goal replan after runtime polish / runtime polish 後の次 no-code product goal 再計画 | Completed / 完了 | Chose Data-first no-code domain sample 2 as the next product-facing implementation. Decision report: [2026-0630 Next No-Code Product Goal After Runtime Polish](reports/2026/2026-0630-next-no-code-product-goal-after-runtime-polish.md). |
| 23 | Data-first no-code domain sample 2 first slice / data-first no-code domain sample 2 first slice | Completed / 完了 | Added `sample29-no-code-support-case-demo` with support-case read-model context, no-code metadata, pack/runtime smoke, and browser UI smoke. Report: [2026-0630 Sample29 No-Code Support Case First Slice](reports/2026/2026-0630-sample29-no-code-support-case-first-slice.md). |
| 24 | Post-sample29 no-code product goal replan / sample29 後の no-code product goal 再計画 | Completed / 完了 | Chose App-local sync no-code demonstration as the next product-facing implementation. Decision report: [2026-0630 Post-Sample29 No-Code Product Goal Replan](reports/2026/2026-0630-post-sample29-no-code-product-goal-replan.md). |
| 25 | App-local sync no-code demonstration first slice / App-local sync no-code demonstration first slice | Completed / 完了 | Added `sample30-no-code-app-local-sync-demo` connecting generated no-code action intent to managed operation sync outbox and App-local SQLite handler. Report: [2026-0630 Sample30 No-Code App-local Sync First Slice](reports/2026/2026-0630-sample30-no-code-app-local-sync-first-slice.md). |
| 26 | Post-sample30 no-code product goal replan / sample30 後の no-code product goal 再計画 | Completed / 完了 | Chose Server-side sync processing follow-up as the next product-facing implementation. Decision report: [2026-0630 Post-Sample30 No-Code Product Goal Replan](reports/2026/2026-0630-post-sample30-no-code-product-goal-replan.md). |
| 27 | Server-side sync processing follow-up first slice / server-side sync processing follow-up first slice | Completed / 完了 | Extended sample30 with generated server DBAccess materialization, binding fallback from the generated method catalog, server outbox handler processing, and server SQLite row verification. Report: [2026-0630 Server-Side Sync Processing Follow-Up First Slice](reports/2026/2026-0630-server-side-sync-processing-follow-up-first-slice.md). |
| 28 | Post-server-side sync no-code product goal replan / server-side sync 後の no-code product goal 再計画 | Completed / 完了 | Chose Reusable partial-update server merge policy as the next product-facing implementation. Decision report: [2026-0630 Post-Server-Side Sync No-Code Product Goal Replan](reports/2026/2026-0630-post-server-side-sync-no-code-product-goal-replan.md). |
| 29 | Reusable partial-update server merge policy first slice / reusable partial-update server merge policy first slice | Completed / 完了 | Added generated server DBAccess partial update merge in the shared executor, removed sample30's sample-specific full-row payload completion, and verified sample30 plus direct server DBAccess coverage. Report: [2026-0630 Reusable Partial-Update Server Merge Policy First Slice](reports/2026/2026-0630-reusable-partial-update-server-merge-policy-first-slice.md). |
| 30 | Post-partial-update merge no-code product goal replan / partial-update merge 後の no-code product goal 再計画 | Completed / 完了 | Chose Sync handoff visibility polish as the next product-facing implementation. Decision report: [2026-0630 Post-Partial-Update Merge No-Code Product Goal Replan](reports/2026/2026-0630-post-partial-update-merge-no-code-product-goal-replan.md). |
| 31 | Sync handoff visibility polish first slice / sync handoff visibility polish first slice | Completed / 完了 | Added generated runtime sync-status hint badges, sample30 App-local/server handoff visibility summary, and focused/full verification. Report: [2026-0630 Sync Handoff Visibility Polish First Slice](reports/2026/2026-0630-sync-handoff-visibility-polish-first-slice.md). |
| 32 | Post-sync handoff visibility no-code product goal replan / sync handoff visibility 後の no-code product goal 再計画 | Completed / 完了 | Chose Operator/admin no-code workflow as the next product-facing implementation. Decision report: [2026-0630 Post-Sync Handoff Visibility No-Code Product Goal Replan](reports/2026/2026-0630-post-sync-handoff-visibility-no-code-product-goal-replan.md). |
| 33 | Operator/admin no-code workflow first slice / operator/admin no-code workflow first slice | Completed / 完了 | Added an inspection-only `NO-CODE-RUNTIME` summary to the existing Source Outputs admin page, backed by a reusable inspection helper and focused PHPUnit coverage. Report: [2026-0630 Operator/Admin No-Code Workflow First Slice](reports/2026/2026-0630-operator-admin-no-code-workflow-first-slice.md). |
| 34 | Post-operator/admin no-code product goal replan / operator/admin no-code 後の product goal 再計画 | Completed / 完了 | Chose Operator preview health/detail links as the next product-facing implementation. Decision report: [2026-0630 Post-Operator/Admin No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-admin-no-code-product-goal-replan.md). |
| 35 | Operator preview health/detail links first slice / operator preview health・detail link first slice | Completed / 完了 | Added health summary and direct definition/detail/download/preview path affordances around existing generated no-code runtime artifacts. Report: [2026-0630 Operator Preview Health Detail Links First Slice](reports/2026/2026-0630-operator-preview-health-detail-links-first-slice.md). |
| 36 | Post-operator preview health no-code product goal replan / operator preview health 後の product goal 再計画 | Completed / 完了 | Chose Operator source-output artifact detail as the next product-facing implementation. Decision report: [2026-0630 Post-Operator Preview Health No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-preview-health-no-code-product-goal-replan.md). |
| 37 | Operator source-output artifact detail first slice / operator source-output artifact detail first slice | Completed / 完了 | Added a read-only artifact detail route/page that summarizes manifest, archive, bundle, runtime source, and download affordance. Report: [2026-0630 Operator Source-Output Artifact Detail First Slice](reports/2026/2026-0630-operator-source-output-artifact-detail-first-slice.md). |
| 38 | Post-operator artifact detail no-code product goal replan / operator artifact detail 後の product goal 再計画 | Completed / 完了 | Chose Sync error-state visibility as the next product-facing implementation. Decision report: [2026-0630 Post-Operator Artifact Detail No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-artifact-detail-no-code-product-goal-replan.md). |
| 39 | Sync error-state visibility first slice / sync error-state visibility first slice | Completed / 完了 | Added one deterministic sample30 failed outbox path using existing status, attempts, and last_error fields. Report: [2026-0630 Sync Error-State Visibility First Slice](reports/2026/2026-0630-sync-error-state-visibility-first-slice.md). |
| 40 | Post-sync error-state visibility no-code product goal replan / sync error-state visibility 後の product goal 再計画 | Completed / 完了 | Chose Operator failed-sync inspection as the next product-facing implementation. Decision report: [2026-0630 Post-Sync Error-State Visibility No-Code Product Goal Replan](reports/2026/2026-0630-post-sync-error-state-visibility-no-code-product-goal-replan.md). |
| 41 | Operator failed-sync inspection first slice / operator failed-sync inspection first slice | Completed / 完了 | Added read-only failed sync outbox inspection to Source Outputs admin using existing status, attempts, and last_error fields. Report: [2026-0630 Operator Failed-Sync Inspection First Slice](reports/2026/2026-0630-operator-failed-sync-inspection-first-slice.md). |
| 42 | Post-operator failed-sync inspection no-code product goal replan / operator failed-sync inspection 後の product goal 再計画 | Completed / 完了 | Chose Operator sync outbox detail as the next product-facing implementation. Decision report: [2026-0630 Post-Operator Failed-Sync Inspection No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-failed-sync-inspection-no-code-product-goal-replan.md). |
| 43 | Operator sync outbox detail first slice / operator sync outbox detail first slice | Completed / 完了 | Added a read-only project-scoped sync outbox item detail page with intent payload and list links. Report: [2026-0630 Operator Sync Outbox Detail First Slice](reports/2026/2026-0630-operator-sync-outbox-detail-first-slice.md). |
| 44 | Post-operator sync outbox detail no-code product goal replan / operator sync outbox detail 後の product goal 再計画 | Completed / 完了 | Chose Sync retry eligibility guard as the next product-facing implementation. Decision report: [2026-0630 Post-Operator Sync Outbox Detail No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-sync-outbox-detail-no-code-product-goal-replan.md). |
| 45 | Sync retry eligibility guard first slice / sync retry eligibility guard first slice | Completed / 完了 | Added a fail-closed retry eligibility helper and exposed its read-only decision in operator detail. Report: [2026-0630 Sync Retry Eligibility Guard First Slice](reports/2026/2026-0630-sync-retry-eligibility-guard-first-slice.md). |
| 46 | Post-sync retry eligibility guard no-code product goal replan / sync retry eligibility guard 後の product goal 再計画 | Completed / 完了 | Chose Operator sync retry action as the next product-facing implementation. Decision report: [2026-0630 Post-Sync Retry Eligibility Guard No-Code Product Goal Replan](reports/2026/2026-0630-post-sync-retry-eligibility-guard-no-code-product-goal-replan.md). |
| 47 | Operator sync retry action first slice / operator sync retry action first slice | Completed / 完了 | Added a narrow operator POST action that requeues eligible failed sync outbox items to pending without processing them inline. Report: [2026-0630 Operator Sync Retry Action First Slice](reports/2026/2026-0630-operator-sync-retry-action-first-slice.md). |
| 48 | Post-operator sync retry action no-code product goal replan / operator sync retry action 後の product goal 再計画 | Completed / 完了 | Chose Retry processing smoke as the next product-facing confidence step. Decision report: [2026-0630 Post-Operator Sync Retry Action No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-sync-retry-action-no-code-product-goal-replan.md). |
| 49 | Retry processing smoke first slice / retry processing smoke first slice | Completed / 完了 | Proved a requeued pending item is picked up by the existing processor path without adding scheduler, transport, or conflict resolution. Report: [2026-0630 Retry Processing Smoke First Slice](reports/2026/2026-0630-retry-processing-smoke-first-slice.md). |
| 50 | Post-retry processing smoke no-code product goal replan / retry processing smoke 後の product goal 再計画 | Completed / 完了 | Chose Operator retry feedback polish as the next small product-facing implementation. Decision report: [2026-0630 Post-Retry Processing Smoke No-Code Product Goal Replan](reports/2026/2026-0630-post-retry-processing-smoke-no-code-product-goal-replan.md). |
| 51 | Operator retry feedback polish first slice / operator retry feedback polish first slice | Completed / 完了 | Made the post-requeue operator result and next processor step clearer without adding scheduler, transport, or audit tables. Report: [2026-0630 Operator Retry Feedback Polish First Slice](reports/2026/2026-0630-operator-retry-feedback-polish-first-slice.md). |
| 52 | Post-operator retry feedback polish no-code product goal replan / operator retry feedback polish 後の product goal 再計画 | Completed / 完了 | Chose No-code runtime error/retry visibility as the next product-facing implementation. Decision report: [2026-0630 Post-Operator Retry Feedback Polish No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-retry-feedback-polish-no-code-product-goal-replan.md). |
| 53 | No-code runtime error/retry visibility first slice / no-code runtime error/retry visibility first slice | Completed / 完了 | Surfaced failed/retryable sync state in generated runtime artifacts without adding retry mutation there. Report: [2026-0630 No-Code Runtime Error/Retry Visibility First Slice](reports/2026/2026-0630-no-code-runtime-error-retry-visibility-first-slice.md). |
| 54 | Post-runtime error/retry visibility no-code product goal replan / runtime error/retry visibility 後の product goal 再計画 | 0.5 day / 半日 | Active planning step. Choose the next small no-code product-facing implementation after runtime error/retry visibility. |

## Data-First No-Code Domain Sample 2 First Slice / data-first no-code domain sample 2 first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Sample29 No-Code Support Case First Slice](reports/2026/2026-0630-sample29-no-code-support-case-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Sample29 No-Code Support Case First Slice](reports/2026/2026-0630-sample29-no-code-support-case-first-slice.md)。

This implementation work was selected after runtime UX/state polish and is complete for the first slice. / これは runtime UX/state polish 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| D2-1 | Domain boundary / domain 境界 | `DONE` | 0.5 day / 半日 | Chose a support-case domain with read-model context fields (`case_number`, `customer_name`, `customer_tier`) and editable workflow fields. |
| D2-2 | Sample scaffold / sample scaffold | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added sample29 directory, catalog entry, compose/run files, README, and project/source-output seed skeleton. |
| D2-3 | Metadata wiring / metadata wiring | `DONE` | 1 - 2 days / 1 - 2 日 | Seeded shared contract, managed operation, and `NO-CODE-RUNTIME` metadata for `support_case` / `update_support_case`. |
| D2-4 | Runtime smoke / runtime smoke | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added sample29 pack/runtime test coverage and a browser UI smoke profile using the polished generated runtime. |
| D2-5 | Docs and verification / docs・verification | `DONE` | 0.5 day / 半日 | README, report, current-plan update, targeted sample29 verification, and full `make test` record. |

Boundary / 境界:

- In scope: one second data-first no-code sample, slightly richer domain shape, generated runtime artifact path, managed operation smoke, browser UI smoke. / 対象: 2 つ目の data-first no-code sample、少し豊かな domain shape、generated runtime artifact path、managed operation smoke、browser UI smoke。
- Out of scope: new visual builder, new metadata tables, broad relation engine, app-local sync product demo, operator/admin workflow, native/Flutter target. / 対象外: visual builder 追加、新 metadata table、広い relation engine、app-local sync product demo、operator/admin workflow、native / Flutter target。
- Verification: sample pack runtime test, no-code runtime UI smoke, focused PHPUnit, and `make test` if shared generator/runtime code changes. / 検証: sample pack runtime test、no-code runtime UI smoke、focused PHPUnit、shared generator/runtime code を触る場合は `make test`。

## Post-Sample29 No-Code Product Goal Replan / sample29 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Sample29 No-Code Product Goal Replan](reports/2026/2026-0630-post-sample29-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Sample29 No-Code Product Goal Replan](reports/2026/2026-0630-post-sample29-no-code-product-goal-replan.md)。

This planning item selected App-local sync no-code demonstration as the next active implementation item. / この planning item では App-local sync no-code demonstration を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| App-local sync demonstration | Connect no-code action intent more visibly to App-local persistence / sync concepts. | 2 - 5 days / 2 - 5 日 | Selected. sample29 completed the second Web/runtime domain proof, so the next product story can show the sync-backed data path. |
| Sample29 follow-up domain pressure | Use support-case results to add the smallest missing read-model or relation-shaped proof. | 1 - 3 days / 1 - 3 日 | Deferred. sample29 did not expose a concrete blocking runtime/metadata gap. |
| Operator/admin no-code workflow | Show how an operator chooses, publishes, or inspects no-code runtime artifacts. | 1 - 3 days / 1 - 3 日 | Deferred. Operator surface still needs clearer scope. |
| Targeted runtime polish from sample29 | Polish only presentation gaps found in the second domain sample. | 0.5 - 2 days / 半日 - 2 日 | Deferred until a concrete presentation gap is identified. |

## App-Local Sync No-Code Demonstration First Slice / App-local sync no-code demonstration first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Sample30 No-Code App-local Sync First Slice](reports/2026/2026-0630-sample30-no-code-app-local-sync-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Sample30 No-Code App-local Sync First Slice](reports/2026/2026-0630-sample30-no-code-app-local-sync-first-slice.md)。

This implementation work was selected after sample29 and is complete for the first slice. / これは sample29 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| SY1 | Boundary and target sample / 境界と対象 sample | `DONE` | 0.5 day / 半日 | Added a small sample30 rather than expanding sample29, and fixed one sync-backed no-code update action path. |
| SY2 | App-local fixture and artifact bridge / App-local fixture・artifact bridge | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Seeded shared contract / App-local persistence / no-code runtime Source Outputs for `sync_task`. |
| SY3 | No-code intent to sync handoff / no-code intent -> sync handoff | `DONE` | 1 - 2 days / 1 - 2 日 | Generated no-code action intent becomes a managed operation sync intent and is enqueued in the sync outbox. |
| SY4 | Runtime and smoke proof / runtime・smoke proof | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added focused PHPUnit / sample pack smoke proving App-local outbox handler updates SQLite DTO state. |
| SY5 | Docs and verification / docs・verification | `DONE` | 0.5 day / 半日 | README, report, current-plan update, targeted verification, and full `make test` record. |

Boundary / 境界:

- In scope: one small sync-backed no-code demonstration, existing shared contract / App-local persistence / managed operation foundations, sample-visible sync handoff, focused smoke. / 対象: 小さな sync-backed no-code demonstration 1 つ、既存 shared contract / App-local persistence / managed operation foundation、sample-visible な sync handoff、focused smoke。
- Out of scope: new visual builder, conflict resolution, full offline runtime, transport to a remote server, operator/admin publishing workflow, native/Flutter target. / 対象外: visual builder 追加、conflict resolution、完全 offline runtime、remote server transport、operator/admin publishing workflow、native / Flutter target。
- Verification: focused PHPUnit or sample pack runtime test first; add browser smoke only if the first slice exposes browser-visible state; run `make test` if shared runtime/foundation code changes. / 検証: まず focused PHPUnit または sample pack runtime test。browser-visible state を出す場合だけ browser smoke を追加し、shared runtime / foundation code を触る場合は `make test`。

## Post-Sample30 No-Code Product Goal Replan / sample30 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Sample30 No-Code Product Goal Replan](reports/2026/2026-0630-post-sample30-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Sample30 No-Code Product Goal Replan](reports/2026/2026-0630-post-sample30-no-code-product-goal-replan.md)。

This planning item selected Server-side sync processing follow-up as the next active implementation item. / この planning item では Server-side sync processing follow-up を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Server-side sync processing follow-up | Extend the sample-visible path from App-local outbox handling toward generated server DBAccess processing. | 1 - 3 days / 1 - 3 日 | Selected. This is the smallest continuation after sample30 and stays within the existing sync / DBAccess foundations. |
| Sync handoff visibility polish | Make the generated/runtime artifact show the sync handoff state more clearly. | 0.5 - 2 days / 半日 - 2 日 | Deferred. sample30 proved the handoff; presentation polish should follow a concrete visible gap. |
| Operator/admin no-code workflow | Show how an operator chooses, publishes, or inspects no-code runtime artifacts. | 1 - 3 days / 1 - 3 日 | Deferred. Operator surface still needs clearer scope. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

## Server-Side Sync Processing Follow-Up First Slice / server-side sync processing follow-up first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Server-Side Sync Processing Follow-Up First Slice](reports/2026/2026-0630-server-side-sync-processing-follow-up-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Server-Side Sync Processing Follow-Up First Slice](reports/2026/2026-0630-server-side-sync-processing-follow-up-first-slice.md)。

This implementation work was selected after sample30 and is complete for the first slice. / これは sample30 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| SV1 | Boundary and sample target / 境界と sample target | `DONE` | 0.5 day / 半日 | Extended sample30 rather than adding sample31, keeping transport and conflict resolution out of scope. |
| SV2 | Server binding setup / server binding setup | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Materialized generated server DBAccess for `sync_task` and used generated method catalog fallback for binding. |
| SV3 | Sync outbox server handler proof / sync outbox server handler proof | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Processed a second managed operation sync outbox item with the server DBAccess handler and verified the server row changed. |
| SV4 | Sample pack smoke / sample pack smoke | `DONE` | 0.5 day / 半日 | Extended `Sample30NoCodeAppLocalSyncDemoTest` and `make sample30-pack-runtime-test` coverage. |
| SV5 | Docs and verification / docs・verification | `DONE` | 0.5 day / 半日 | README/report/current-plan updates and targeted verification record. |

Boundary / 境界:

- In scope: one server-side processing proof for an existing managed operation sync intent, generated DBAccess binding, sample-visible server row update, focused smoke. / 対象: 既存 managed operation sync intent の server-side processing proof 1 つ、generated DBAccess binding、sample-visible な server row update、focused smoke。
- Out of scope: remote transport, conflict resolution, retry scheduling beyond existing outbox lifecycle, visual builder, native/Flutter target. / 対象外: remote transport、conflict resolution、既存 outbox lifecycle を超える retry scheduling、visual builder、native / Flutter target。
- Verification: focused PHPUnit / sample pack runtime test first; run `make test` if shared server DBAccess / sync foundation code changes. / 検証: まず focused PHPUnit / sample pack runtime test。shared server DBAccess / sync foundation code を触る場合は `make test`。

## Post-Server-Side Sync No-Code Product Goal Replan / server-side sync 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Server-Side Sync No-Code Product Goal Replan](reports/2026/2026-0630-post-server-side-sync-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Server-Side Sync No-Code Product Goal Replan](reports/2026/2026-0630-post-server-side-sync-no-code-product-goal-replan.md)。

This planning item selected Reusable partial-update server merge policy as the next active implementation item. / この planning item では Reusable partial-update server merge policy を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Reusable partial-update / server merge policy | Replace the sample-specific full-row payload completion with a reusable read/merge/write policy for generated server DBAccess updates. | 1 - 3 days / 1 - 3 日 | Selected. sample30 exposed this as the most concrete product-path gap after server-side processing. Keep conflict resolution out of scope. |
| Sync handoff visibility polish | Make generated/runtime artifacts show App-local/server processing state more clearly. | 0.5 - 2 days / 半日 - 2 日 | Deferred. The data behavior gap is more foundational than presentation polish. |
| Operator/admin no-code workflow | Show how an operator chooses, publishes, or inspects no-code runtime artifacts. | 1 - 3 days / 1 - 3 日 | Deferred. Operator surface still needs clearer scope. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

## Reusable Partial-Update Server Merge Policy First Slice / reusable partial-update server merge policy first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Reusable Partial-Update Server Merge Policy First Slice](reports/2026/2026-0630-reusable-partial-update-server-merge-policy-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Reusable Partial-Update Server Merge Policy First Slice](reports/2026/2026-0630-reusable-partial-update-server-merge-policy-first-slice.md)。

This implementation work was selected after the server-side sync processing follow-up and is complete for the first slice. / これは server-side sync processing follow-up 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| PU1 | Boundary and server read target / 境界と server read target | `DONE` | 0.5 day / 半日 | Scoped the reusable merge path to update intents that carry partial input and key fields. |
| PU2 | Existing row read adapter / existing row read adapter | `DONE` | 0.5 - 1 day / 半日 - 1 日 | The server DBAccess executor derives and calls the generated read method when a full DataClass payload is missing. |
| PU3 | Merge policy helper / merge policy helper | `DONE` | 0.5 day / 半日 | Existing row values are merged with key + partial input into a full generated DataClass payload without conflict resolution. |
| PU4 | Server handler integration / server handler integration | `DONE` | 0.5 - 1 day / 半日 - 1 日 | The reusable merge path runs inside `app_managed_operation_server_dbaccess_execute_intent` for update operations. |
| PU5 | Sample30 smoke and docs / sample30 smoke・docs | `DONE` | 0.5 day / 半日 | Removed sample30's sample-specific payload completion, verified sample30 server update, and updated report/current plan. |

Boundary / 境界:

- In scope: update operation only, existing generated server DBAccess read/update methods, one row keyed by the sync intent, deterministic merge of partial input over existing row, sample30 smoke. / 対象: update operation のみ、既存 generated server DBAccess read/update method、sync intent の key による 1 row、partial input を existing row に deterministic merge、sample30 smoke。
- Out of scope: conflict resolution, remote transport, retry scheduling, multi-row merge, delete/create semantics, visual builder, native/Flutter target. / 対象外: conflict resolution、remote transport、retry scheduling、multi-row merge、delete / create semantics、visual builder、native / Flutter target。
- Verification: focused PHPUnit / `make sample30-pack-runtime-test` first; run `make test` because shared server DBAccess executor behavior is likely to change. / 検証: まず focused PHPUnit / `make sample30-pack-runtime-test`。shared server DBAccess executor behavior を触る可能性が高いため `make test` も実行する。

## Post-Partial-Update Merge No-Code Product Goal Replan / partial-update merge 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Partial-Update Merge No-Code Product Goal Replan](reports/2026/2026-0630-post-partial-update-merge-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Partial-Update Merge No-Code Product Goal Replan](reports/2026/2026-0630-post-partial-update-merge-no-code-product-goal-replan.md)。

This planning item selected Sync handoff visibility polish as the next active implementation item. / この planning item では Sync handoff visibility polish を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Sync handoff visibility polish | Make generated/runtime artifacts show App-local/server processing state more clearly now that both processing paths work. | 0.5 - 2 days / 半日 - 2 日 | Selected. This is the smallest product-facing continuation after both App-local and server-side sync processing paths work. |
| Operator/admin no-code workflow | Show how an operator chooses, publishes, or inspects no-code runtime artifacts. | 1 - 3 days / 1 - 3 日 | Deferred. Operator surface still needs clearer scope. |
| Additional sync behavior pressure | Add a small retry/error-state or merge edge proof after partial update merge. | 1 - 3 days / 1 - 3 日 | Deferred. Data behavior is good enough for now; make the existing handoff visible first. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

## Sync Handoff Visibility Polish First Slice / sync handoff visibility polish first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Sync Handoff Visibility Polish First Slice](reports/2026/2026-0630-sync-handoff-visibility-polish-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Sync Handoff Visibility Polish First Slice](reports/2026/2026-0630-sync-handoff-visibility-polish-first-slice.md)。

This implementation work was selected after reusable partial-update server merge policy and is complete for the first slice. / これは reusable partial-update server merge policy 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| SH1 | Visibility boundary / visibility 境界 | `DONE` | 0.5 day / 半日 | Chose generated sync-status hint badges plus sample30 checker-visible App-local/server handoff summary. |
| SH2 | Runtime/artifact state model / runtime・artifact state model | `DONE` | 0.5 day / 半日 | `local-copy` contracts now expose sync status hints, and generated runtime HTML renders a sync status badge for list/detail screens. |
| SH3 | Generated preview/checker polish / generated preview・checker polish | `DONE` | 0.5 - 1 day / 半日 - 1 日 | sample30 checker now reports App-local processed and server processed handoff states. |
| SH4 | Smoke and docs / smoke・docs | `DONE` | 0.5 day / 半日 | Verified sample30/full test and updated README/report/current plan. |

Boundary / 境界:

- In scope: sample30-visible sync handoff status, existing managed operation outbox lifecycle, existing App-local and server-side handlers, generated/runtime artifact presentation or checker-visible state, focused smoke. / 対象: sample30 で見える sync handoff status、既存 managed operation outbox lifecycle、既存 App-local / server-side handler、generated/runtime artifact presentation または checker-visible state、focused smoke。
- Out of scope: remote transport, conflict resolution, retry scheduling changes, new operator/admin workflow, visual builder, native/Flutter target. / 対象外: remote transport、conflict resolution、retry scheduling 変更、新 operator/admin workflow、visual builder、native / Flutter target。
- Verification: focused sample30 smoke first; run `make test` if shared runtime/foundation behavior changes. / 検証: まず focused sample30 smoke。shared runtime / foundation behavior を触る場合は `make test`。

## Post-Sync Handoff Visibility No-Code Product Goal Replan / sync handoff visibility 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Sync Handoff Visibility No-Code Product Goal Replan](reports/2026/2026-0630-post-sync-handoff-visibility-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Sync Handoff Visibility No-Code Product Goal Replan](reports/2026/2026-0630-post-sync-handoff-visibility-no-code-product-goal-replan.md)。

This planning item selected Operator/admin no-code workflow as the next active implementation item. / この planning item では Operator/admin no-code workflow を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Operator/admin no-code workflow | Show how an operator chooses, publishes, or inspects generated no-code runtime artifacts. | 1 - 3 days / 1 - 3 日 | Selected. The data path and handoff state are now visible enough to expose an operator-facing inspection workflow. |
| Additional sync behavior pressure | Add a small retry/error-state or merge edge proof after handoff visibility. | 1 - 3 days / 1 - 3 日 | Deferred. Useful, but less product-facing than an operator/admin inspection path. |
| Another product-facing no-code sample/polish slice | Add another sample or polish only if sample30 visibility exposes a concrete gap. | 0.5 - 3 days / 半日 - 3 日 | Deferred. No concrete new domain/presentation blocker is identified. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

## Operator/Admin No-Code Workflow First Slice / operator/admin no-code workflow first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Operator/Admin No-Code Workflow First Slice](reports/2026/2026-0630-operator-admin-no-code-workflow-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Operator/Admin No-Code Workflow First Slice](reports/2026/2026-0630-operator-admin-no-code-workflow-first-slice.md)。

This implementation work was selected after sync handoff visibility polish and is complete for the first slice. / これは sync handoff visibility polish 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| OA1 | Operator surface boundary / operator surface 境界 | `DONE` | 0.5 day / 半日 | Chose the existing Source Outputs admin page as the smallest operator/admin inspection surface. |
| OA2 | No-code artifact inspection model / no-code artifact inspection model | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added a reusable inspection helper that summarizes `NO-CODE-RUNTIME`, latest artifact, preview paths, screen/action counts, and sync hints. |
| OA3 | Admin/operator view integration / admin/operator view integration | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added an inspection-only summary section to `/projects/{project}/source-outputs` without editing metadata or expanding publish workflow. |
| OA4 | Sample coverage and docs / sample coverage・docs | `DONE` | 0.5 day / 半日 | Added focused PHPUnit coverage and updated report/current plan. |

Boundary / 境界:

- In scope: inspection-only operator/admin surface, existing `NO-CODE-RUNTIME` Source Output artifacts, latest artifact/published preview metadata, generated screen/action summary, sync hint visibility. / 対象: inspection-only の operator/admin surface、既存 `NO-CODE-RUNTIME` Source Output artifact、latest artifact / published preview metadata、generated screen/action summary、sync hint visibility。
- Out of scope: visual builder, metadata editing workflow, publish approval workflow, remote transport, conflict resolution, native/Flutter target. / 対象外: visual builder、metadata 編集 workflow、publish approval workflow、remote transport、conflict resolution、native / Flutter target。
- Verification: focused PHPUnit for the inspection model first; run `make test` if shared source-output behavior changes. / 検証: まず inspection model の focused PHPUnit。shared source-output behavior を触る場合は `make test`。

## Post-Operator/Admin No-Code Product Goal Replan / operator/admin no-code 後の product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Operator/Admin No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-admin-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Operator/Admin No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-admin-no-code-product-goal-replan.md)。

This planning item selected Operator preview health/detail links as the next active implementation item. / この planning item では Operator preview health/detail links を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Operator inspection follow-up | Add the smallest missing operator affordance discovered from the first inspection surface, such as artifact detail linking or preview health. | 0.5 - 2 days / 半日 - 2 日 | Selected. The first surface shows counts, but operators still need a compact health signal and direct routes into the generated artifact. |
| No-code runtime product polish | Improve generated runtime behavior only if the operator inspection makes a concrete preview gap visible. | 0.5 - 2 days / 半日 - 2 日 | Deferred. No new generated runtime behavior gap is confirmed yet. |
| Sync/error-state pressure | Add a narrow retry/error/conflict visibility proof after the handoff and inspection path. | 1 - 3 days / 1 - 3 日 | Deferred. Useful, but the operator workflow should first make current artifact health clearer. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose the next small no-code product-facing implementation after generated artifact inspection became visible. / 対象: generated artifact inspection が見えるようになった後の次の小さな no-code product-facing implementation を選ぶ。
- Out of scope: broad visual builder, metadata editing workflow, publish approval workflow, native/Flutter target. / 対象外: 広い visual builder、metadata 編集 workflow、publish approval workflow、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Operator Preview Health/Detail Links First Slice / operator preview health・detail link first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Operator Preview Health Detail Links First Slice](reports/2026/2026-0630-operator-preview-health-detail-links-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Operator Preview Health Detail Links First Slice](reports/2026/2026-0630-operator-preview-health-detail-links-first-slice.md)。

This implementation work was selected after the post-operator/admin no-code product-goal replan and is complete for the first slice. / これは operator/admin 後の no-code product-goal replan で選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| OH1 | Health model boundary / health model 境界 | `DONE` | 0.5 day / 半日 | Added `ready` / `warning` / `missing` health states from existing `NO-CODE-RUNTIME` definition/artifact/preview metadata. |
| OH2 | Detail-link affordances / detail link 導線 | `DONE` | 0.5 day / 半日 | Surfaced definition detail, latest artifact download when archive is available, and preview file paths without adding publish workflow. |
| OH3 | Operator page integration / operator page integration | `DONE` | 0.5 day / 半日 | Added compact health/detail affordances to the existing Source Outputs admin page. |
| OH4 | Focused coverage and docs / focused coverage・docs | `DONE` | 0.5 day / 半日 | Added focused PHPUnit coverage and updated report/current plan. |

Boundary / 境界:

- In scope: existing Source Outputs admin page, existing `NO-CODE-RUNTIME` Source Output artifacts, health derived from available definition/latest artifact/preview JSON/HTML, direct detail/download/path affordances. / 対象: 既存 Source Outputs admin page、既存 `NO-CODE-RUNTIME` Source Output artifact、definition / latest artifact / preview JSON / HTML から導く health、direct detail / download / path affordance。
- Out of scope: visual builder, metadata editing workflow, publish approval workflow, remote transport, conflict resolution, new generated runtime behavior. / 対象外: visual builder、metadata 編集 workflow、publish approval workflow、remote transport、conflict resolution、新しい generated runtime behavior。
- Verification: focused PHPUnit for health model first; run `make test` if shared source-output/operator page behavior changes. / 検証: まず health model の focused PHPUnit。shared source-output / operator page behavior を触る場合は `make test`。

## Post-Operator Preview Health No-Code Product Goal Replan / operator preview health 後の product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Operator Preview Health No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-preview-health-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Operator Preview Health No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-preview-health-no-code-product-goal-replan.md)。

This planning item selected Operator source-output artifact detail as the next active implementation item. / この planning item では Operator source-output artifact detail を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Operator artifact detail follow-up | Add the smallest artifact detail or preview accessibility improvement if health/linking exposes a concrete operator gap. | 0.5 - 2 days / 半日 - 2 日 | Selected. Health/detail links now point at artifact identity, but there is no read-only artifact detail page between list summary and archive download. |
| No-code runtime product polish | Improve generated runtime behavior only if operator health highlights a concrete preview/runtime issue. | 0.5 - 2 days / 半日 - 2 日 | Deferred. No generated runtime behavior gap is confirmed by the health surface. |
| Sync/error-state pressure | Add a narrow retry/error/conflict visibility proof after the handoff and operator inspection path. | 1 - 3 days / 1 - 3 日 | Deferred. Useful, but less directly tied to the current operator artifact inspection gap. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose the next small no-code product-facing implementation after operator artifact health became visible. / 対象: operator artifact health が見えるようになった後の次の小さな no-code product-facing implementation を選ぶ。
- Out of scope: broad visual builder, metadata editing workflow, publish approval workflow, native/Flutter target. / 対象外: 広い visual builder、metadata 編集 workflow、publish approval workflow、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Operator Source-Output Artifact Detail First Slice / operator source-output artifact detail first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Operator Source-Output Artifact Detail First Slice](reports/2026/2026-0630-operator-source-output-artifact-detail-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Operator Source-Output Artifact Detail First Slice](reports/2026/2026-0630-operator-source-output-artifact-detail-first-slice.md)。

This implementation work was selected after the post-operator preview health no-code product-goal replan and is complete for the first slice. / これは operator preview health 後の no-code product-goal replan で選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| OD1 | Route and auth boundary / route・auth 境界 | `DONE` | 0.5 day / 半日 | Added a read-only artifact detail route using the existing project/source-output artifact authorization boundary. |
| OD2 | Artifact detail page / artifact detail page | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Rendered manifest, archive, bundle, runtime source, file counts, source output identity, and download affordance. |
| OD3 | Operator links / operator link | `DONE` | 0.5 day / 半日 | Linked latest/listed artifacts to the detail page from existing Source Outputs surfaces. |
| OD4 | Focused coverage and docs / focused coverage・docs | `DONE` | 0.5 day / 半日 | Added route/auth-focused coverage and updated report/current plan. |

Boundary / 境界:

- In scope: read-only artifact detail route/page, existing source-output artifact manifests, archive/download affordance, route/auth contract, existing Source Outputs surfaces. / 対象: read-only artifact detail route/page、既存 source-output artifact manifest、archive/download affordance、route/auth contract、既存 Source Outputs surface。
- Out of scope: artifact editing, publish approval workflow, visual builder, generated runtime behavior changes, remote transport, conflict resolution. / 対象外: artifact 編集、publish approval workflow、visual builder、generated runtime behavior 変更、remote transport、conflict resolution。
- Verification: route/auth focused PHPUnit first; run `make test` because routing/shared admin surface changes. / 検証: まず route/auth focused PHPUnit。routing / shared admin surface を触るため `make test` を実行。

## Post-Operator Artifact Detail No-Code Product Goal Replan / operator artifact detail 後の product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Operator Artifact Detail No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-artifact-detail-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Operator Artifact Detail No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-artifact-detail-no-code-product-goal-replan.md)。

This planning item selected Sync error-state visibility as the next active implementation item. / この planning item では Sync error-state visibility を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Operator artifact detail follow-up | Improve artifact detail only if this first page exposes a concrete missing inspection field. | 0.5 - 2 days / 半日 - 2 日 | Deferred. The first detail page closes the inspection gap without exposing an immediate missing field. |
| No-code runtime product polish | Improve generated runtime behavior only if artifact detail highlights a concrete preview/runtime issue. | 0.5 - 2 days / 半日 - 2 日 | Deferred. No runtime behavior gap is confirmed by artifact detail inspection. |
| Sync/error-state pressure | Add a narrow retry/error/conflict visibility proof after the handoff and operator inspection path. | 1 - 3 days / 1 - 3 日 | Selected. Success paths are now visible; the next product-facing gap is showing a failed sync/outbox state without adding transport or conflict resolution. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose the next small no-code product-facing implementation after artifact detail inspection became available. / 対象: artifact detail inspection が使えるようになった後の次の小さな no-code product-facing implementation を選ぶ。
- Out of scope: broad visual builder, metadata editing workflow, publish approval workflow, native/Flutter target. / 対象外: 広い visual builder、metadata 編集 workflow、publish approval workflow、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Sync Error-State Visibility First Slice / sync error-state visibility first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Sync Error-State Visibility First Slice](reports/2026/2026-0630-sync-error-state-visibility-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Sync Error-State Visibility First Slice](reports/2026/2026-0630-sync-error-state-visibility-first-slice.md)。

This implementation work was selected after the post-operator artifact detail no-code product-goal replan and is complete for the first slice. / これは operator artifact detail 後の no-code product-goal replan で選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| SE1 | Error-state boundary / error-state 境界 | `DONE` | 0.5 day / 半日 | Chose a minimal failed sync/outbox state using existing outbox `failed` / `last_error` fields. |
| SE2 | Sample-visible failed state / sample-visible failed state | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Extended sample30 checker/result with one deterministic failed outbox processing path. |
| SE3 | Assertion coverage / assertion coverage | `DONE` | 0.5 day / 半日 | Asserted failed status, attempts, and last_error without changing success-path behavior. |
| SE4 | Docs and verification / docs・verification | `DONE` | 0.5 day / 半日 | Updated README/report/current plan and verified with sample30 plus full test. |

Boundary / 境界:

- In scope: sample30-visible failed sync/outbox status, existing outbox lifecycle fields, deterministic local/server handler failure, focused assertions. / 対象: sample30 で見える failed sync/outbox status、既存 outbox lifecycle field、deterministic な local/server handler failure、focused assertion。
- Out of scope: retry scheduler, remote transport, conflict resolution, broad operator dashboard, generated runtime behavior changes. / 対象外: retry scheduler、remote transport、conflict resolution、広い operator dashboard、generated runtime behavior 変更。
- Verification: sample30 pack runtime test first; run `make test` because sample checker behavior changes. / 検証: まず sample30 pack runtime test。sample checker behavior を触るため `make test` を実行。

## Post-Sync Error-State Visibility No-Code Product Goal Replan / sync error-state visibility 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Sync Error-State Visibility No-Code Product Goal Replan](reports/2026/2026-0630-post-sync-error-state-visibility-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Sync Error-State Visibility No-Code Product Goal Replan](reports/2026/2026-0630-post-sync-error-state-visibility-no-code-product-goal-replan.md)。

This planning item chose Operator failed-sync inspection as the next active implementation item. / この planning item では Operator failed-sync inspection を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Sync retry visibility | Add a narrow retry/requeue proof on top of the existing failed outbox state. | 1 - 3 days / 1 - 3 日 | Deferred. First make the failed state visible to an operator before adding behavior for retrying it. |
| Operator failed-sync inspection | Surface failed outbox state in an operator/admin page using existing status and last_error fields. | 1 - 3 days / 1 - 3 日 | Selected. This is the smallest product-facing continuation after sample30 made failed outbox state deterministic. |
| No-code runtime error feedback | Improve generated runtime feedback only if the failed outbox path exposes a concrete user-facing runtime gap. | 0.5 - 2 days / 半日 - 2 日 | Deferred. The confirmed gap is operator inspection, not generated runtime behavior. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose the next small no-code product-facing implementation after failed outbox visibility. / 対象: failed outbox visibility 後の次の小さな no-code product-facing implementation を選ぶ。
- Out of scope: broad visual builder, remote transport, full retry scheduler, conflict resolution, native/Flutter target. / 対象外: 広い visual builder、remote transport、full retry scheduler、conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Operator Failed-Sync Inspection First Slice / operator failed-sync inspection first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Operator Failed-Sync Inspection First Slice](reports/2026/2026-0630-operator-failed-sync-inspection-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Operator Failed-Sync Inspection First Slice](reports/2026/2026-0630-operator-failed-sync-inspection-first-slice.md)。

This implementation work was selected after failed sync/outbox state became sample-visible and is complete for the first slice. / これは failed sync/outbox state が sample-visible になった後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| OF1 | Inspection boundary / inspection 境界 | `DONE` | 0.5 day / 半日 | Chose the existing Source Outputs admin page as the smallest read-only operator/admin surface. |
| OF2 | Outbox summary helper / outbox summary helper | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Reused existing outbox repository/status/last_error fields to produce a failed-item summary. |
| OF3 | Admin/operator view integration / admin/operator view integration | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Showed failed sync items without adding retry/edit behavior. |
| OF4 | Assertion coverage / assertion coverage | `DONE` | 0.5 day / 半日 | Added focused helper tests for failed item visibility and empty-state behavior. |
| OF5 | Docs and verification / docs・verification | `DONE` | 0.5 day / 半日 | Updated report/current plan and verified with PHP lint plus full `make test`. |

Boundary / 境界:

- In scope: read-only operator/admin failed sync inspection, existing outbox status / attempts / last_error fields, focused tests. / 対象: read-only の operator/admin failed sync inspection、既存 outbox status / attempts / last_error field、focused test。
- Out of scope: retry/requeue action, remote transport, conflict resolution, generated runtime behavior changes, broad dashboard. / 対象外: retry / requeue action、remote transport、conflict resolution、generated runtime behavior 変更、広い dashboard。
- Verification: focused PHP/PHPUnit first; run `make test` because the operator/admin surface changes. / 検証: まず focused PHP / PHPUnit。operator/admin surface を触るため `make test` を実行。

## Post-Operator Failed-Sync Inspection No-Code Product Goal Replan / operator failed-sync inspection 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Operator Failed-Sync Inspection No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-failed-sync-inspection-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Operator Failed-Sync Inspection No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-failed-sync-inspection-no-code-product-goal-replan.md)。

This planning item chose Operator sync outbox detail as the next active implementation item. / この planning item では Operator sync outbox detail を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Sync retry/requeue action | Add a narrow operator action for retrying failed outbox items. | 1 - 3 days / 1 - 3 日 | Deferred. Retry needs a safe read-only item detail surface first. |
| Operator sync outbox detail page | Add a read-only detail page for one outbox item before adding retry behavior. | 0.5 - 2 days / 半日 - 2 日 | Selected. This is the smallest operator-facing continuation after list-level failed sync inspection. |
| No-code runtime error feedback | Improve generated runtime feedback only if operator inspection exposes a runtime-facing gap. | 0.5 - 2 days / 半日 - 2 日 | Deferred. The confirmed gap remains operator diagnosis, not runtime behavior. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose the next small no-code product-facing implementation after read-only failed sync inspection. / 対象: read-only failed sync inspection 後の次の小さな no-code product-facing implementation を選ぶ。
- Out of scope: broad visual builder, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Operator Sync Outbox Detail First Slice / operator sync outbox detail first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Operator Sync Outbox Detail First Slice](reports/2026/2026-0630-operator-sync-outbox-detail-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Operator Sync Outbox Detail First Slice](reports/2026/2026-0630-operator-sync-outbox-detail-first-slice.md)。

This implementation work was selected after read-only failed sync list inspection and is complete for the first slice. / これは read-only failed sync list inspection 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| OD1 | Detail boundary / detail 境界 | `DONE` | 0.5 day / 半日 | Added read-only detail before retry/requeue actions. |
| OD2 | Route and lookup / route・lookup | `DONE` | 0.5 day / 半日 | Added a project-scoped outbox detail route using existing dedupe_key lookup. |
| OD3 | Detail page / detail page | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Shows status, attempts, last_error, endpoints, operation metadata, dedupe key, timestamps, and intent payload. |
| OD4 | Admin list link / admin list link | `DONE` | 0.5 day / 半日 | Linked failed items from Source Outputs sync inspection to the detail page. |
| OD5 | Tests and docs / tests・docs | `DONE` | 0.5 day / 半日 | Added route/auth coverage, updated report/current plan, and verified with full `make test`. |

Boundary / 境界:

- In scope: read-only operator/admin outbox item detail, existing outbox fields, project-scoped routing, focused tests. / 対象: read-only operator/admin outbox item detail、既存 outbox field、project-scoped route、focused test。
- Out of scope: retry/requeue action, status mutation, remote transport, conflict resolution, broad dashboard. / 対象外: retry / requeue action、status mutation、remote transport、conflict resolution、広い dashboard。
- Verification: focused PHP/PHPUnit first; run `make test` if route/auth or shared surface changes. / 検証: まず focused PHP / PHPUnit。route / auth または shared surface を触る場合は `make test` を実行。

## Post-Operator Sync Outbox Detail No-Code Product Goal Replan / operator sync outbox detail 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Operator Sync Outbox Detail No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-sync-outbox-detail-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Operator Sync Outbox Detail No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-sync-outbox-detail-no-code-product-goal-replan.md)。

This planning item chose Sync retry eligibility guard as the next active implementation item. / この planning item では Sync retry eligibility guard を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Sync retry/requeue action | Add a narrow operator action for retrying failed outbox items. | 1 - 3 days / 1 - 3 日 | Deferred. Add a fail-closed eligibility decision first, then wire an action. |
| Retry eligibility guard | Add a small fail-closed eligibility helper before exposing an action button. | 0.5 - 2 days / 半日 - 2 日 | Selected. This is the smallest safe continuation before mutating failed outbox state. |
| No-code runtime error feedback | Improve generated runtime feedback only if operator detail exposes a runtime-facing gap. | 0.5 - 2 days / 半日 - 2 日 | Deferred. The confirmed gap remains operator retry readiness, not runtime behavior. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose the next small no-code product-facing implementation after read-only sync outbox detail. / 対象: read-only sync outbox detail 後の次の小さな no-code product-facing implementation を選ぶ。
- Out of scope: broad visual builder, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Sync Retry Eligibility Guard First Slice / sync retry eligibility guard first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Sync Retry Eligibility Guard First Slice](reports/2026/2026-0630-sync-retry-eligibility-guard-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Sync Retry Eligibility Guard First Slice](reports/2026/2026-0630-sync-retry-eligibility-guard-first-slice.md)。

This implementation work was selected before adding retry/requeue actions and is complete for the first slice. / これは retry / requeue action を追加する前に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RG1 | Eligibility boundary / eligibility 境界 | `DONE` | 0.5 day / 半日 | Defined a fail-closed retry eligibility decision for sync outbox items. |
| RG2 | Helper contract / helper contract | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added a pure helper that returns allowed, reasons, and action label without mutating state. |
| RG3 | Operator detail visibility / operator detail visibility | `DONE` | 0.5 day / 半日 | Shows the read-only eligibility decision on the sync outbox detail page. |
| RG4 | Tests and docs / tests・docs | `DONE` | 0.5 day / 半日 | Added focused eligibility tests, updated report/current plan, and verified with full `make test`. |

Boundary / 境界:

- In scope: pure retry eligibility decision, failed outbox items, existing status / attempts / last_error fields, read-only operator visibility. / 対象: pure な retry eligibility decision、failed outbox item、既存 status / attempts / last_error field、read-only operator visibility。
- Out of scope: retry/requeue mutation, background scheduler, remote transport, conflict resolution, broad dashboard. / 対象外: retry / requeue mutation、background scheduler、remote transport、conflict resolution、広い dashboard。
- Verification: focused PHP/PHPUnit first; run `make test` if shared route/operator detail behavior changes. / 検証: まず focused PHP / PHPUnit。shared route / operator detail behavior を触る場合は `make test`。

## Post-Sync Retry Eligibility Guard No-Code Product Goal Replan / sync retry eligibility guard 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Sync Retry Eligibility Guard No-Code Product Goal Replan](reports/2026/2026-0630-post-sync-retry-eligibility-guard-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Sync Retry Eligibility Guard No-Code Product Goal Replan](reports/2026/2026-0630-post-sync-retry-eligibility-guard-no-code-product-goal-replan.md)。

This planning item chose Operator sync retry action as the next active implementation item. / この planning item では Operator sync retry action を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Sync retry/requeue action | Add a narrow operator action for retrying eligible failed outbox items. | 1 - 3 days / 1 - 3 日 | Selected. Eligibility guard is now in place, so the smallest product-facing mutation is requeue-to-pending. |
| Retry audit trail | Add a small audit note for retry attempts before or with mutation. | 0.5 - 2 days / 半日 - 2 日 | Deferred. Existing permission audit and updated_at are sufficient for this first mutation slice. |
| No-code runtime error feedback | Improve generated runtime feedback only if retry readiness exposes a runtime-facing gap. | 0.5 - 2 days / 半日 - 2 日 | Deferred. The confirmed gap remains operator retry action, not runtime behavior. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose the next small no-code product-facing implementation after retry eligibility guard. / 対象: retry eligibility guard 後の次の小さな no-code product-facing implementation を選ぶ。
- Out of scope: broad visual builder, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Operator Sync Retry Action First Slice / operator sync retry action first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Operator Sync Retry Action First Slice](reports/2026/2026-0630-operator-sync-retry-action-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Operator Sync Retry Action First Slice](reports/2026/2026-0630-operator-sync-retry-action-first-slice.md)。

This implementation work was selected after the retry eligibility guard and is complete for the first slice. / これは retry eligibility guard 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RA1 | Mutation boundary / mutation 境界 | `DONE` | 0.5 day / 半日 | Requeues eligible failed items to pending and does not process inline. |
| RA2 | Repository wrapper / repository wrapper | `DONE` | 0.5 day / 半日 | Added a small retry/requeue wrapper around existing status update behavior. |
| RA3 | Operator action / operator action | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added CSRF-protected POST action on sync outbox detail. |
| RA4 | Tests and docs / tests・docs | `DONE` | 0.5 day / 半日 | Added focused repository/operator contract tests, updated report/current plan, and verified. |

Boundary / 境界:

- In scope: eligible failed item -> pending, clear last_error, keep attempts unchanged until processor claims it, project-scoped POST action, CSRF, focused tests. / 対象: eligible failed item -> pending、last_error clear、processor が claim するまでは attempts は増やさない、project-scoped POST action、CSRF、focused test。
- Out of scope: immediate processing, background scheduler, remote transport, conflict resolution, broad dashboard, retry audit table. / 対象外: immediate processing、background scheduler、remote transport、conflict resolution、広い dashboard、retry audit table。
- Verification: focused PHP/PHPUnit first; run `make test` because repository/operator mutation behavior changes. / 検証: まず focused PHP / PHPUnit。repository / operator mutation behavior を触るため `make test`。

## Post-Operator Sync Retry Action No-Code Product Goal Replan / operator sync retry action 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Operator Sync Retry Action No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-sync-retry-action-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Operator Sync Retry Action No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-sync-retry-action-no-code-product-goal-replan.md)。

This planning item selected Retry processing smoke as the next active implementation item. / この planning item では Retry processing smoke を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Operator retry feedback polish | Make the existing operator retry flow easier to understand after requeue. | 0.5 - 2 days / 半日 - 2 日 | Deferred. The operator action already has a narrow success message; product confidence needs processor proof first. |
| Retry processing smoke / retry processing smoke | Prove requeued items are picked up by the existing processor path in a focused sample or repository flow. | 0.5 - 2 days / 半日 - 2 日 | Selected. This closes the behavior loop after requeue without broadening retry UI or scheduling. |
| No-code runtime error/retry visibility | Surface retry-related state in generated/runtime artifacts. | 1 - 3 days / 1 - 3 日 | Deferred. Runtime-facing retry visibility should follow processor confidence. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose one next small product-facing implementation after retry action, based on visible gap and risk. / 対象: retry action 後の visible gap と risk に基づいて、次の小さな product-facing implementation を 1 つ選ぶ。
- Out of scope: broad visual builder, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Retry Processing Smoke First Slice / retry processing smoke first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Retry Processing Smoke First Slice](reports/2026/2026-0630-retry-processing-smoke-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Retry Processing Smoke First Slice](reports/2026/2026-0630-retry-processing-smoke-first-slice.md)。

This implementation work was selected after operator sync retry action and is complete for the first slice. / これは operator sync retry action 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RS1 | Smoke boundary / smoke 境界 | `DONE` | 0.5 day / 半日 | Proved requeued `pending` item processing only; did not add scheduler, transport, or conflict resolution. |
| RS2 | Focused processor fixture / focused processor fixture | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Extended a focused repository/processor flow from failed -> requeued pending -> existing processor claim/handler. |
| RS3 | Assertions and docs / assertions・docs | `DONE` | 0.5 day / 半日 | Asserted final status, attempts behavior, cleared error, and existing processor result; updated report/current plan. |

Boundary / 境界:

- In scope: one deterministic retry processing smoke, existing outbox processor, existing handlers, existing requeue action semantics. / 対象: deterministic な retry processing smoke 1 つ、既存 outbox processor、既存 handler、既存 requeue action semantics。
- Out of scope: background scheduler, new retry UI, remote transport, conflict resolution, retry audit table, broad dashboard. / 対象外: background scheduler、新 retry UI、remote transport、conflict resolution、retry audit table、広い dashboard。
- Verification: focused PHPUnit/sample smoke first; run `make test` if shared processor or sample behavior changes. / 検証: まず focused PHPUnit / sample smoke。shared processor または sample behavior を触る場合は `make test`。

## Post-Retry Processing Smoke No-Code Product Goal Replan / retry processing smoke 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Retry Processing Smoke No-Code Product Goal Replan](reports/2026/2026-0630-post-retry-processing-smoke-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Retry Processing Smoke No-Code Product Goal Replan](reports/2026/2026-0630-post-retry-processing-smoke-no-code-product-goal-replan.md)。

This planning item selected Operator retry feedback polish as the next active implementation item. / この planning item では Operator retry feedback polish を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Operator retry feedback polish | Make the retry flow easier for operators to understand after the behavior loop is proven. | 0.5 - 2 days / 半日 - 2 日 | Selected. The behavior loop is proven, so the next smallest product gap is clarity after requeue. |
| No-code runtime error/retry visibility | Surface failed/retryable/requeued state in generated/runtime artifacts. | 1 - 3 days / 1 - 3 日 | Deferred. Runtime-facing state should wait until the operator flow is understandable. |
| Retry audit trail | Add a narrow audit note for operator retry mutation. | 0.5 - 2 days / 半日 - 2 日 | Deferred. Existing updated_at/status/attempts plus clear operator feedback are enough for the next slice. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose one next small product-facing implementation after retry processing smoke. / 対象: retry processing smoke 後の次の小さな product-facing implementation を 1 つ選ぶ。
- Out of scope: broad visual builder, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Operator Retry Feedback Polish First Slice / operator retry feedback polish first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 Operator Retry Feedback Polish First Slice](reports/2026/2026-0630-operator-retry-feedback-polish-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 Operator Retry Feedback Polish First Slice](reports/2026/2026-0630-operator-retry-feedback-polish-first-slice.md)。

This implementation work was selected after retry processing smoke and is complete for the first slice. / これは retry processing smoke 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RF1 | Feedback boundary / feedback 境界 | `DONE` | 0.5 day / 半日 | Improved post-requeue operator clarity only; did not add scheduler, transport, conflict resolution, or audit tables. |
| RF2 | Detail page result copy/state / detail page result copy・state | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Success state explains that the item is pending and can be picked up by the existing processor. |
| RF3 | Contract and docs / contract・docs | `DONE` | 0.5 day / 半日 | Added focused page/source contract coverage, updated report/current plan, and verified. |

Boundary / 境界:

- In scope: operator detail feedback after retry, current status/attempts/last_error clarity, existing processor next-step wording. / 対象: retry 後の operator detail feedback、現在 status / attempts / last_error の分かりやすさ、既存 processor next-step wording。
- Out of scope: scheduler, transport, conflict resolution, retry audit table, broad dashboard, generated runtime UI. / 対象外: scheduler、transport、conflict resolution、retry audit table、広い dashboard、generated runtime UI。
- Verification: focused PHP/source contract first; run `make test` if shared route/operator behavior changes. / 検証: まず focused PHP / source contract。shared route / operator behavior を触る場合は `make test`。

## Post-Operator Retry Feedback Polish No-Code Product Goal Replan / operator retry feedback polish 後の no-code product goal 再計画

Status: `DONE`. Decision report: [2026-0630 Post-Operator Retry Feedback Polish No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-retry-feedback-polish-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Post-Operator Retry Feedback Polish No-Code Product Goal Replan](reports/2026/2026-0630-post-operator-retry-feedback-polish-no-code-product-goal-replan.md)。

This planning item selected No-code runtime error/retry visibility as the next active implementation item. / この planning item では No-code runtime error/retry visibility を次の active implementation item として選びました。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| No-code runtime error/retry visibility | Surface failed/retryable/requeued state in generated/runtime artifacts. | 1 - 3 days / 1 - 3 日 | Selected. Operator retry is understandable now; runtime-visible error/retry state is the next product gap. |
| Retry audit trail | Add a narrow audit note for operator retry mutation. | 0.5 - 2 days / 半日 - 2 日 | Deferred. Accountability is less visible to the product path than runtime error/retry state. |
| Another operator workflow polish slice | Polish the Source Outputs / sync outbox navigation only if review exposes a concrete operator workflow gap. | 0.5 - 2 days / 半日 - 2 日 | Deferred. No concrete new operator navigation gap is identified. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose one next small product-facing implementation after operator retry feedback polish. / 対象: operator retry feedback polish 後の次の小さな product-facing implementation を 1 つ選ぶ。
- Out of scope: broad visual builder, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## No-Code Runtime Error/Retry Visibility First Slice / no-code runtime error/retry visibility first slice

Status: `FIRST_SLICE_DONE`. Report: [2026-0630 No-Code Runtime Error/Retry Visibility First Slice](reports/2026/2026-0630-no-code-runtime-error-retry-visibility-first-slice.md). / Status: `FIRST_SLICE_DONE`。Report: [2026-0630 No-Code Runtime Error/Retry Visibility First Slice](reports/2026/2026-0630-no-code-runtime-error-retry-visibility-first-slice.md)。

This implementation work was selected after operator retry feedback polish and is complete for the first slice. / これは operator retry feedback polish 後に選んだ implementation work で、first slice は完了です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| RV1 | Runtime visibility boundary / runtime visibility 境界 | `DONE` | 0.5 day / 半日 | Surfaced failed/retryable sync state only; retry mutation remains in operator/admin pages. |
| RV2 | Generated runtime state model / generated runtime state model | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added deterministic runtime artifact data and HTML hints for failed/retryable state. |
| RV3 | Sample smoke and docs / sample smoke・docs | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Updated focused runtime/sample smoke, report/current plan, and verification record. |

Boundary / 境界:

- In scope: generated/runtime-visible sync error or retryable state, read-only hints, existing sample/runtime smoke. / 対象: generated / runtime-visible な sync error または retryable state、read-only hint、既存 sample / runtime smoke。
- Out of scope: retry mutation in generated runtime, scheduler, transport, conflict resolution, retry audit table, broad dashboard. / 対象外: generated runtime 内の retry mutation、scheduler、transport、conflict resolution、retry audit table、広い dashboard。
- Verification: focused runtime/sample smoke first; run `make test` if shared generator/runtime behavior changes. / 検証: まず focused runtime / sample smoke。shared generator / runtime behavior を触る場合は `make test`。

## Post-Runtime Error/Retry Visibility No-Code Product Goal Replan / runtime error/retry visibility 後の no-code product goal 再計画

Status: `ACTIVE_NEXT`. / Status: `ACTIVE_NEXT`。

This planning item should choose the next small no-code product-facing implementation after runtime error/retry visibility. / この planning item では runtime error/retry visibility 後の次の小さな no-code product-facing implementation を選びます。

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- | --- |
| Retry audit trail | Add a narrow audit note for operator retry mutation now that visibility exists in both operator and runtime surfaces. | 0.5 - 2 days / 半日 - 2 日 | Candidate. Choose only if accountability is the next concrete product gap. |
| Another operator/runtime workflow polish slice | Polish navigation between runtime hints and operator sync outbox only if review exposes a concrete gap. | 0.5 - 2 days / 半日 - 2 日 | Candidate. Keep evidence-driven. |
| New no-code product sample or domain pressure | Add another sample only if the current sync/operator path needs broader domain pressure. | 1 - 4 days / 1 - 4 日 | Candidate. Defer unless there is a clear product story. |
| Mtool implementation namespace cleanup | Revisit the parked namespace cleanup with a concrete helper cluster. | 1 - 3 days / 1 - 3 日 | Remains parked until a narrow helper cluster is selected. |

Boundary / 境界:

- In scope: choose one next small product-facing implementation after runtime error/retry visibility. / 対象: runtime error/retry visibility 後の次の小さな product-facing implementation を 1 つ選ぶ。
- Out of scope: broad visual builder, remote transport, full conflict resolution, native/Flutter target. / 対象外: 広い visual builder、remote transport、full conflict resolution、native / Flutter target。
- Verification: planning/report update only unless a concrete implementation is selected. / 検証: 具体実装を選ぶまでは planning/report 更新のみ。

## Next No-Code Product Goal After Runtime Polish Decision / runtime polish 後の次 no-code product goal decision

Status: `DONE`. Decision report: [2026-0630 Next No-Code Product Goal After Runtime Polish](reports/2026/2026-0630-next-no-code-product-goal-after-runtime-polish.md). / Status: `DONE`。判断 report: [2026-0630 Next No-Code Product Goal After Runtime Polish](reports/2026/2026-0630-next-no-code-product-goal-after-runtime-polish.md)。

Candidate product goals considered / 検討した候補:

| Candidate / 候補 | First slice estimate / first slice 目安 | Decision |
| --- | --- | --- |
| Data-first no-code domain sample 2 | 2 - 5 days / 2 - 5 日 | Selected. The polished runtime should now be tested against a slightly richer product-facing domain. |
| App-local sync demonstration | 2 - 5 days / 2 - 5 日 | Deferred. Still useful, but should follow one more generated Web/runtime domain proof. |
| Operator/admin no-code workflow | 1 - 3 days / 1 - 3 日 | Deferred. Needs a clearer operator surface and may distract from data-first runtime proof. |
| Additional runtime polish slice | 0.5 - 2 days / 半日 - 2 日 | Deferred unless the second domain sample exposes a concrete runtime presentation gap. |

## Generated No-Code Runtime UX Polish First Slice / generated no-code runtime UX polish first slice

This is the active implementation work selected by the post-sample28 product-goal replan. / これは sample28 後の product-goal replan で選んだ active implementation work です。

| Step | Work / 作業 | Status | Rough effort / 目安 | Output / 成果物 |
| --- | --- | --- | --- | --- |
| UX1 | Runtime copy and label polish / runtime 文言・label polish | `DONE` | 0.5 day / 半日 | Generated list/detail/form titles, subtitles, button text, and empty-state copy are readable without hand-editing sample output. |
| UX2 | Runtime state polish / runtime state polish | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Generated preview exposes ready/error preview state, empty/ready screen state, disabled/ready action state, and idle/working/success/error feedback state where deterministic. |
| UX3 | Action feedback polish / action feedback polish | `DONE` | 0.5 day / 半日 | Generated browser dispatch helper shows simple success / failure feedback for the existing authorized update intent smoke. |
| UX4 | Screenshot and smoke refresh / screenshot・smoke 更新 | `DONE` | 0.5 day / 半日 | sample07 and sample28 smoke coverage captures the polished runtime surface and verifies no regression in list/detail/form/update intent behavior. |

First-slice boundary / first slice 境界:

- In scope: generated `runtime-preview.html` / preview data presentation, deterministic browser-visible copy/state/action feedback, sample07 and sample28 smoke expectations, and docs/report updates. / 対象: generated `runtime-preview.html` / preview data presentation、deterministic な browser-visible copy/state/action feedback、sample07 と sample28 の smoke 期待値、docs/report 更新。
- Out of scope: new visual builder, new metadata tables, app-local sync expansion, new sample domain, native/Flutter targets, and broad redesign of generated runtime architecture. / 対象外: visual builder 追加、新 metadata table、app-local sync 拡張、新 sample domain、native / Flutter target、generated runtime architecture の大きな再設計。
- Verification: run the focused no-code runtime tests/smokes first, then choose whether `make test` is needed based on touched generator surface. / 検証: まず no-code runtime の対象 test / smoke を実行し、触った generator surface に応じて `make test` が必要か判断する。

## Next No-Code Product Goal Replan Decision / 次の no-code product goal 再計画 decision

Status: `DONE`. Decision report: [2026-0630 Next No-Code Product Goal Replan](reports/2026/2026-0630-next-no-code-product-goal-replan.md). / Status: `DONE`。判断 report: [2026-0630 Next No-Code Product Goal Replan](reports/2026/2026-0630-next-no-code-product-goal-replan.md)。

Candidate product goals considered / 検討した候補:

| Candidate / 候補 | Why / 目的 | First slice estimate / first slice 目安 | Notes |
| --- | --- | --- | --- |
| Generated runtime UX polish | Make the generated no-code preview more presentable after sample28. | 0.5 - 2 days / 半日 - 2 日 | Selected. Lowest risk product-facing improvement; builds directly on existing sample07/sample28 smokes. |
| Data-first no-code domain sample 2 | Prove the same path against a slightly richer relation/domain. | 2 - 5 days / 2 - 5 日 | Could introduce relation/read-model pressure; needs careful sample scope. |
| App-local sync demonstration | Connect no-code action intent more visibly to app-local persistence/sync concepts. | 2 - 5 days / 2 - 5 日 | Useful product story, but may touch more foundations. |
| Operator/admin no-code workflow | Show how an operator chooses or publishes no-code runtime artifacts. | 1 - 3 days / 1 - 3 日 | UI/admin surface needs clearer scope before implementation. |

## Completed No-Code Summary / 完了済み no-code summary

This section keeps the completed no-code path visible for context. It is not an active implementation list. / この section は完了済み no-code path を文脈として残します。active な実装リストではありません。

Current completed base / 現在完了済みの土台:

- Shared contract, App-local persistence, managed operation layer, `no-code-screen-definition-v0`, and first `no-code-runtime-v0` render / dispatch adapter are in place. / shared contract、App-local persistence、managed operation layer、`no-code-screen-definition-v0`、最初の `no-code-runtime-v0` render / dispatch adapter は実装済み。
- Remaining no-code work should focus on generated artifacts, sample execution, persisted operation flow, and minimal UI smoke. / 残りの no-code 作業は、生成 artifact、sample 実行、persisted operation flow、最小 UI smoke に集中する。

No-code target boundary / no-code 対象範囲:

| Target / 対象 | Current plan scope / 現在の計画範囲 | Note / 補足 |
| --- | --- | --- |
| Web app no-code / Web app no-code | In scope / 対象 | Primary target. Generate data-first list/detail/form behavior and browser-renderable runtime artifacts from shared contract and managed operation metadata. |
| HTML runtime preview / HTML runtime preview | In scope / 対象 | Current MVP surface. Generated `runtime-preview.html` is the first concrete UI artifact and is the target for basic UI smoke. |
| App-local DB / sync-backed data behavior / App-local DB・同期前提の data behavior | Foundation scope / 基盤対象 | Persistence, sync intent, and App-local execution are treated as foundations for generated data behavior, not as a separate native-app UI target. |
| iOS / Android native app no-code / iOS・Android native app no-code | Out of current scope / 現計画の対象外 | Possible future output family, but not part of the current runtime MVP or `sample28` target. |
| Flutter app no-code / Flutter app no-code | Out of current scope / 現計画の対象外 | Possible future output target only after the Web / HTML data-first runtime path is proven. |
| Visual builder / Visual builder | Out of current scope / 現計画の対象外 | Current plan generates screen definitions and runtime artifacts from canonical metadata; it does not introduce a drag-and-drop app builder. |

| Order | Step / ステップ | Status | Rough effort / 目安 | Commit guidance / コミット方針 |
| --- | --- | --- | --- | --- |
| 1 | `no-code-runtime-json` Source Output artifact / `no-code-runtime-json` Source Output artifact 化 | `DONE` | 30 min - 1.5 hours / 30 分 - 1.5 時間 | Strategy, generator, `screen-definition.json`, `runtime-preview.json`, and artifact generation test are in place. |
| 2 | Artifact publish path verification / artifact publish 経路検証 | `DONE` | 30 min - 1 hour / 30 分 - 1 時間 | Artifact create/publish path is covered by integration test. |
| 3 | Sample connection for no-code runtime artifact / no-code runtime artifact の sample 接続 | `DONE` | 1 - 3 hours / 1 - 3 時間 | sample07 now seeds `NO-CODE-RUNTIME` plus no-code shared contract metadata. |
| 4 | Generated screen/runtime sample check / 生成 screen/runtime の sample run 検証 | `DONE` | 1 - 2 hours / 1 - 2 時間 | sample07 pack check generates/publishes the artifact and verifies generated files, screen definition, and runtime preview. |
| 5 | One persisted operation flow / 1 操作の persisted flow | `DONE` | 0.5 - 1 day / 半日 - 1 日 | no-code runtime action dispatch now bridges to managed operation sync intent and updates a sample07 SQLite row through generated server DBAccess. |
| 6 | Minimal HTML/runtime renderer / 最小 HTML/runtime renderer | `DONE` | 1 - 3 days / 1 - 3 日 | Generated list/detail/form render to `runtime-preview.html` without introducing a visual builder or custom component framework. |
| 7 | Basic UI smoke for list/detail/form / list/detail/form basic UI smoke | `DONE` | 1 - 2 days / 1 - 2 日 | `sample07-no-code-runtime-ui-smoke` opens generated `runtime-preview.html` in headless Chromium and checks list/detail/form DOM plus screenshot capture. |
| 8 | Create/update browser or headless smoke / create/update browser または headless smoke | `DONE` | 1 - 3 days / 1 - 3 日 | Generated HTML now carries action metadata and a browser-side dispatch helper; the headless smoke verifies fail-closed disabled dispatch and an authorized update intent probe through generated key/input fields. |
| 9 | `sample28-no-code-data-app-mvp` / `sample28-no-code-data-app-mvp` | `DONE` | 3 days - 1.5 weeks / 3 日 - 1.5 週間 | First user-facing data-first no-code app MVP is complete through scaffold/catalog, no-code runtime artifact connection, generated flow smoke, docs, and pack verification. |
| 10 | No-code docs, reports, and plan cleanup / no-code docs・reports・plan cleanup | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Completed as part of sample28 MVP polish; future no-code work should be replanned from the next product goal. |

Sample28 first-slice breakdown / sample28 first slice 分解:

| Step | Work / 作業 | Status | Rough effort / 目安 | Commit guidance / コミット方針 |
| --- | --- | --- | --- | --- |
| 9a | sample28 scaffold and catalog entry / sample28 scaffold・catalog 登録 | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Added tutorial directory, README, compose/run entrypoint, sample catalog entry, and minimal seed shell. |
| 9b | sample28 no-code runtime artifact connection / sample28 no-code runtime artifact 接続 | `DONE` | 0.5 - 1 day / 半日 - 1 日 | Seeded shared contract / managed operation / Source Output metadata so sample28 emits `NO-CODE-RUNTIME` artifacts through the existing generator. |
| 9c | sample28 data-first generated flow smoke / sample28 data-first 生成 flow smoke | `DONE` | 1 - 2 days / 1 - 2 日 | Added `sample28-no-code-runtime-ui-smoke`, reusing the generated preview smoke with a sample28 profile to verify list/detail/form and operation dispatch intent. |
| 9d | sample28 MVP polish, docs, and pack verification / sample28 MVP polish・docs・pack 検証 | `DONE` | 1 - 3 days / 1 - 3 日 | README / report / current-plan status are updated, and sample28 pack compose/runtime/pack/UI/full-test verification is recorded. |

Rough total from the current state / 現在地からの合計目安:

- Minimal no-code runtime MVP means steps 1-8 and is now complete for the first path. / 最小 no-code runtime MVP は step 1-8 を指し、first path は完了済み。
- User-facing no-code app MVP including sample28 means steps 1-9: 2 - 4 weeks. / sample28 を含む見せられる no-code app MVP は step 1-9 を指し、2 - 4 週間。
- Conservative range with integration, smoke-test buffer, and cleanup means steps 1-10: 3 - 5 weeks. / 統合・smoke test の余裕と cleanup 込みの現実的レンジは step 1-10 を指し、3 - 5 週間。

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
| `ACTIVE_NEXT_FIRST_SLICE_DONE` | Recommended next work remains active, but a committed first slice is complete / 次に進める主線のままだが、first slice は完了済み |
| `DONE` | Completed within a detailed breakdown; keep only when it clarifies the path to remaining work / 詳細 breakdown 内で完了済み。残作業への道筋を明確にする場合だけ残す |
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
| Mtool auth foundation first slice / Mtool auth 基盤 first slice | [2026-0629 Mtool auth foundation first slice](reports/2026/2026-0629-mtool-auth-foundation-first-slice.md) |
| Gate 0 App-local DB / sync feasibility studies / Gate 0 App 内 DB・同期 FS | [2026-0629 Shared Contract Manifest Spike](reports/2026/2026-0629-shared-contract-manifest-spike.md), [2026-0629 App Local SQLite Schema Spike](reports/2026/2026-0629-app-local-sqlite-schema-spike.md), [2026-0629 DTO Save/Read Spike](reports/2026/2026-0629-dto-save-read-spike.md). |
| Shared Contract Core Vocabulary / shared contract 最小語彙 | [2026-0629 Shared Contract Core Vocabulary](reports/2026/2026-0629-shared-contract-core-vocabulary.md) |
| Shared DataClass contract foundation / shared DataClass contract 基盤 | [2026-0629 Shared DataClass Contract Foundation First Slice](reports/2026/2026-0629-shared-dataclass-contract-foundation-first-slice.md) |
| App-local persistence first demo first slice / App-local persistence first demo first slice | [2026-0629 App-local Persistence First Slice](reports/2026/2026-0629-app-local-persistence-first-slice.md) |
| App-local DBAccess first slice / App-local DBAccess first slice | [2026-0629 App-local DBAccess First Slice](reports/2026/2026-0629-app-local-dbaccess-first-slice.md) |
| App-local persistence sample27 demo / App-local persistence sample27 demo | [2026-0629 App-local Persistence Sample27 Demo](reports/2026/2026-0629-app-local-persistence-sample27-demo.md) |
| App-local persistence Source Output artifacts / App-local persistence Source Output artifacts | [2026-0629 App-local Persistence Source Output Artifacts](reports/2026/2026-0629-app-local-persistence-source-output-artifacts.md) |
| Managed operation metadata first slice / managed operation metadata first slice | [2026-0629 Managed Operation Metadata First Slice](reports/2026/2026-0629-managed-operation-metadata-first-slice.md) |
| Managed operation docs Source Output / managed operation docs Source Output | [2026-0629 Managed Operation Docs Source Output](reports/2026/2026-0629-managed-operation-docs-source-output.md) |
| Managed operation execution plan adapter / managed operation execution plan adapter | [2026-0629 Managed Operation Execution Plan Adapter](reports/2026/2026-0629-managed-operation-execution-plan-adapter.md) |
| Managed operation sync intent skeleton / managed operation sync intent skeleton | [2026-0629 Managed Operation Sync Intent Skeleton](reports/2026/2026-0629-managed-operation-sync-intent-skeleton.md) |
| Managed operation sync outbox first slice / managed operation sync outbox first slice | [2026-0629 Managed Operation Sync Outbox First Slice](reports/2026/2026-0629-managed-operation-sync-outbox-first-slice.md) |
| Managed operation outbox status transitions / managed operation outbox status transitions | [2026-0629 Managed Operation Outbox Status Transitions](reports/2026/2026-0629-managed-operation-outbox-status-transitions.md) |
| Managed operation next pending outbox selection / managed operation next pending outbox selection | [2026-0629 Managed Operation Next Pending Outbox Selection](reports/2026/2026-0629-managed-operation-next-pending-outbox-selection.md) |
| Managed operation outbox claim contract / managed operation outbox claim contract | [2026-0629 Managed Operation Outbox Claim Contract](reports/2026/2026-0629-managed-operation-outbox-claim-contract.md) |
| Managed operation outbox processor contract / managed operation outbox processor contract | [2026-0629 Managed Operation Outbox Processor Contract](reports/2026/2026-0629-managed-operation-outbox-processor-contract.md) |
| Managed operation App-local executor first slice / managed operation App-local executor first slice | [2026-0629 Managed Operation App-local Executor First Slice](reports/2026/2026-0629-managed-operation-app-local-executor-first-slice.md) |
| Managed operation App-local outbox handler / managed operation App-local outbox handler | [2026-0629 Managed Operation App-local Outbox Handler](reports/2026/2026-0629-managed-operation-app-local-outbox-handler.md) |
| Managed operation server DBAccess executor first slice / managed operation server DBAccess executor first slice | [2026-0629 Managed Operation Server DBAccess Executor First Slice](reports/2026/2026-0629-managed-operation-server-dbaccess-executor-first-slice.md) |
| Managed operation server DBAccess outbox handler / managed operation server DBAccess outbox handler | [2026-0629 Managed Operation Server DBAccess Outbox Handler](reports/2026/2026-0629-managed-operation-server-dbaccess-outbox-handler.md) |
| Managed operation server DBAccess binding discovery / managed operation server DBAccess binding discovery | [2026-0629 Managed Operation Server DBAccess Binding Discovery](reports/2026/2026-0629-managed-operation-server-dbaccess-binding-discovery.md) |
| Managed operation server DBAccess candidate selection / managed operation server DBAccess candidate selection | [2026-0629 Managed Operation Server DBAccess Candidate Selection](reports/2026/2026-0629-managed-operation-server-dbaccess-candidate-selection.md) |
| Managed operation server DBAccess project catalog wiring / managed operation server DBAccess project catalog wiring | [2026-0629 Managed Operation Server DBAccess Project Catalog Wiring](reports/2026/2026-0629-managed-operation-server-dbaccess-project-catalog-wiring.md) |
| Managed operation server DBAccess real coverage / managed operation server DBAccess real coverage | [2026-0629 Managed Operation Server DBAccess Real Coverage](reports/2026/2026-0629-managed-operation-server-dbaccess-real-coverage.md) |
| Managed operation sample07 coverage / managed operation sample07 coverage | [2026-0629 Managed Operation Sample07 Coverage](reports/2026/2026-0629-managed-operation-sample07-coverage.md) |
| No-code runtime sample07 artifact connection / no-code runtime sample07 artifact 接続 | [2026-0629 No-Code Runtime Sample07 Artifact Connection](reports/2026/2026-0629-no-code-runtime-sample07-artifact.md) |
| No-code runtime persisted operation flow / no-code runtime persisted operation flow | [2026-0629 No-Code Runtime Persisted Operation Flow](reports/2026/2026-0629-no-code-runtime-persisted-operation-flow.md) |
| No-code runtime HTML renderer / no-code runtime HTML renderer | [2026-0629 No-Code Runtime HTML Renderer](reports/2026/2026-0629-no-code-runtime-html-renderer.md) |
| No-code runtime UI smoke / no-code runtime UI smoke | [2026-0630 No-Code Runtime UI Smoke](reports/2026/2026-0630-no-code-runtime-ui-smoke.md) |
| No-code runtime browser dispatch smoke / no-code runtime browser dispatch smoke | [2026-0630 No-Code Runtime Browser Dispatch Smoke](reports/2026/2026-0630-no-code-runtime-browser-dispatch-smoke.md) |
| Sample28 no-code data app first slice / sample28 no-code data app first slice | [2026-0630 Sample28 No-Code Data App First Slice](reports/2026/2026-0630-sample28-no-code-data-app-first-slice.md) |
| Sample28 no-code runtime UI smoke / sample28 no-code runtime UI smoke | [2026-0630 Sample28 No-Code Runtime UI Smoke](reports/2026/2026-0630-sample28-no-code-runtime-ui-smoke.md) |
| Sample28 no-code data app MVP polish / sample28 no-code data app MVP polish | [2026-0630 Sample28 No-Code Data App MVP Polish](reports/2026/2026-0630-sample28-no-code-data-app-mvp-polish.md) |
| Sample29 no-code support case first slice / sample29 no-code support case first slice | [2026-0630 Sample29 No-Code Support Case First Slice](reports/2026/2026-0630-sample29-no-code-support-case-first-slice.md) |
| Sample30 no-code App-local sync first slice / sample30 no-code App-local sync first slice | [2026-0630 Sample30 No-Code App-local Sync First Slice](reports/2026/2026-0630-sample30-no-code-app-local-sync-first-slice.md) |
| Mtool implementation namespace cleanup boundary / Mtool 実装 namespace cleanup boundary | [2026-0630 Mtool Implementation Namespace Cleanup Boundary](reports/2026/2026-0630-mtool-implementation-namespace-cleanup-boundary.md) |
| App local DB / sync roadmap and feasibility catalog / App 内 DB・同期 roadmap と FS catalog | [2026-0628 App Local DB And Sync Roadmap](reports/2026/2026-0628-app-local-db-and-sync-roadmap.md), [2026-0628 App Local DB Feasibility Studies](reports/2026/2026-0628-app-local-db-feasibility-studies.md). Roadmap / FS catalog drafting is done; Gate 0 core FS results are recorded separately. |
| Generated DataClass naming wording slice | [2026-0626 generated name migration sample follow-up](reports/2026/2026-0626-generated-name-migration-sample-follow-up.md) |
| Generated name migration first slice | [2026-0620 generated name migration plan](reports/2026/2026-0620-generated-name-migration-plan.md) |
| Post-security priority decisions and parked authorization gate | [2026-0620 post-security feature priority plan](reports/2026/2026-0620-post-security-feature-priority-plan.md) |

## Finding Rules / 探し方のルール

- Start here when asking "what plans remain?" / 「残っている計画は何か」を見る時はここから始める。
- Use date-less docs for current commitments. / 現在有効な約束は日付なし文書を見る。
- Use dated reports for history, decisions, and implementation records. / 履歴、判断経緯、実装記録は日付付き report を見る。
- Promote a report item into this page when it becomes active or user-facing. / report 内の項目が active または user-facing になったら、このページへ昇格する。
- Move completed items back to dated reports and keep this list short. / 完了項目は日付付き report へ戻し、この一覧は短く保つ。
