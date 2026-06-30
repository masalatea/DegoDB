# Current Plans / 現在の計画

English companion:
This page lists only unfinished or deferred plans. Completed work is kept in dated reports.

This page is the active plan index for DegoDB. / このページは、DegoDB の現在有効な計画索引です。

Use this page before searching historical reports. / 履歴 report を探す前に、まずこのページを見ます。

## Quick Plan List / 計画リスト

When someone asks for "the plan list", answer from this section first. / 「計画リスト」と聞かれたら、まずこの section から答えます。

| Order | Work unit / 作業の塊 | Commit unit / コミット単位 | Status |
| --- | --- | --- | --- |
| 1 | Next no-code product goal replan after runtime polish / runtime polish 後の次 no-code product goal 再計画 | Docs-only scope decision for the next product-facing no-code slice after generated runtime UX/state polish completion | `ACTIVE_NEXT` |
| 2 | Mtool implementation namespace cleanup / Mtool 実装 namespace cleanup | Boundary inventory is recorded; no implementation is recommended until a specific helper cluster or maintenance goal is chosen | `PARKED_REPLAN` |

The generated runtime UX/state polish lane is complete for the current small slice. The active item is a docs-only replan for the next no-code product goal. / generated runtime UX/state polish lane は現在の小さな slice では完了です。active item は次の no-code product goal を決める docs-only replan です。

## Priority Rationale / 優先理由

The OSS / consulting readiness docs package has been completed as the documentation-first step. / OSS・導入支援 readiness 資料 package は、ドキュメント先行の作業として完了しました。

AI context source output is implemented, verified across all tutorial samples, and available as an implicit default companion documentation output for current/new projects. Mtool self-output verification publishes AI context documentation for Mtool itself. / AI 文脈 Source Output は全 tutorial sample に展開済みで、現在・新規 project へ暗黙 default companion documentation output として提供します。Mtool 自身についても AI 文脈ドキュメントを self-output して検証しています。

The physical/logical sample naming migration, PostgreSQL Input / Output support, and generated PHP output namespace support are complete for their intended support boundaries. Generated PHP output namespace support adds an optional project-level namespace setting for DataClass / DBAccess output, keeps the default namespace-free, and verifies mixed sample coverage. Broader Mtool implementation namespace cleanup remains deferred. / physical / logical sample 命名移行、PostgreSQL Input / Output 対応、generated PHP output namespace 対応は、意図した support boundary では完了です。generated PHP output namespace 対応では、DataClass / DBAccess output 向けの任意 project-level namespace 設定を追加し、default は namespace なしのまま、sample coverage は混在で検証しました。Mtool 実装全体の namespace cleanup は後回しです。

App local DB / sync / no-code app roadmap and feasibility study catalog are drafted as dated reports. During planning, auth foundation was identified as useful beyond that roadmap, so it was promoted out of the FS group into a normal first-slice feature/foundation plan. Mtool auth foundation first slice is complete. Gate 0 core feasibility studies are also complete: Shared Contract Manifest Spike, App Local SQLite Schema Spike, and DTO Save/Read Spike. The FS result supports the original design assumption that generated DataClass and shared contract should be separate artifacts: DataClass remains implementation-facing, while shared contract metadata carries persistence / sync / no-code semantics. Shared Contract Core Vocabulary and Shared DataClass contract foundation are complete: manifest v0 vocabulary, validator, explicit contract metadata tables, DataClass + table metadata manifest generation, DataClass shape compare, `shared-contract-json`, and first `shared-contract-typescript` DTO output are implemented. App-local persistence first demo and Source Output artifact slices are complete through sample27: server row -> DTO -> App-local SQLite save -> App-local SQLite read is verified, and `app-local-persistence-php` now emits schema / manifest / summary / PHP wrapper artifacts. Managed data operation layer first-slice spine is complete through sample07 coverage: canonical operation / operation-field tables, PDO repository, fail-closed policy evaluator, `managed-operation-docs-md` Source Output artifact, plan-only execution adapter, sync intent skeleton, sync outbox lifecycle, App-local executor / handler, server DBAccess executor / handler, project catalog binding, real generated DBAccess coverage, and sample07 managed operation coverage are in place. No-code screen definition and runtime MVP is complete for the minimal steps 1-8 path: `no-code-screen-definition-v0`, the first `no-code-runtime-v0` render/dispatch adapter, `no-code-runtime-json` artifact publishing, sample07 artifact generation/publish verification, persisted operation flow via generated sample07 DBAccess, minimal HTML preview rendering, basic UI smoke, and browser/headless update dispatch smoke are implemented. The first user-facing no-code app MVP sample, `sample28-no-code-data-app-mvp`, is also complete through generated list/detail/form smoke and pack verification. The post-sample28 product-goal replan chose generated runtime UX polish as the next small product-facing slice before broader domain, sync, or operator workflow expansion. That lane is now complete through readable generated titles/subtitles, empty-state copy, browser action feedback, runtime/screen/action state badges, working/success/error feedback states, and refreshed sample07/sample28 smokes. / App 内 DB・同期・no-code app roadmap と feasibility study catalog は日付付き report として作成済みです。検討の中で auth 基盤はその roadmap に限定されず通常機能としても有用だと分かったため、FS 群から外して正式な first-slice 計画へ格上げしました。Mtool auth 基盤 first slice は完了済みです。Gate 0 core FS も Shared Contract Manifest Spike、App Local SQLite Schema Spike、DTO Save/Read Spike まで完了し、generated DataClass と shared contract は別 artifact として扱うべき、という元の見立てを支持しました。DataClass は implementation-facing、shared contract metadata は persistence / sync / no-code semantics の正本として扱います。Shared Contract Core Vocabulary と Shared DataClass contract 基盤は完了し、manifest v0 語彙、validator、明示 contract metadata table、DataClass + table metadata からの manifest 生成、DataClass shape compare、`shared-contract-json`、最初の `shared-contract-typescript` DTO output を実装済みです。App-local persistence first demo と Source Output artifact slice は sample27 まで完了し、server row -> DTO -> App-local SQLite save -> App-local SQLite read を検証済み、`app-local-persistence-php` は schema / manifest / summary / PHP wrapper artifact を出力します。Managed data operation layer first-slice spine は sample07 coverage まで完了し、canonical operation / operation-field table、PDO repository、fail-closed policy evaluator、`managed-operation-docs-md` Source Output artifact、plan-only execution adapter、sync intent skeleton、sync outbox lifecycle、App-local executor / handler、server DBAccess executor / handler、project catalog binding、real generated DBAccess coverage、sample07 managed operation coverage を追加済みです。No-code screen definition・runtime MVP は minimal steps 1-8 path として完了し、`no-code-screen-definition-v0`、最初の `no-code-runtime-v0` render/dispatch adapter、`no-code-runtime-json` artifact publishing、sample07 artifact 生成 / publish 検証、generated sample07 DBAccess 経由の persisted operation flow、最小 HTML preview rendering、basic UI smoke、browser/headless update dispatch smoke を実装済みです。最初の user-facing no-code app MVP sample である `sample28-no-code-data-app-mvp` も、generated list/detail/form smoke と pack verification まで完了しました。sample28 後の product-goal replan では、より広い domain / sync / operator workflow 拡張の前に、小さな product-facing slice として generated runtime UX polish を選びました。その lane は readable な generated title/subtitle、empty-state copy、browser action feedback、runtime / screen / action state badge、working / success / error feedback state、sample07/sample28 smoke 更新まで完了しました。

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
| 22 | Next no-code product goal replan after runtime polish / runtime polish 後の次 no-code product goal 再計画 | 30 - 60 min / 30 - 60 分 | Active planning step. Choose whether the next implementation should be richer domain sample, app-local sync demonstration, operator/admin workflow, or another small runtime polish slice. |

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
