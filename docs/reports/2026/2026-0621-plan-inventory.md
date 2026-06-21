# 2026-06-21 Plan Inventory

## Status

- status: `CURRENT INVENTORY`
- created: `2026-06-21 JST`
- purpose: 2026-06-19 の棚卸し以後に進んだ計画と、現在の実装優先順を再整理する

## Summary

2026-06-19 の棚卸しで active mainline は整理済みだったが、2026-06-20 以後に security 後の実装候補がかなり進んだ。現在は、security / auth / generated runtime baseline だけでなく、custom proxy bundle coverage、generated OIDC JWT runtime verification、generated name migration first slice、PostgreSQL user DB output representative set まで current scope で進んでいる。

現時点の次の主線は、Mtool admin/lab authorization broad enforcement ではなく、database-first / existing-database-first positioning と営業資産を整える documentation / examples / AI context lane として読むのが自然である。

Current plan index is promoted to the date-less document [Current Plans / 現在の計画](../../current-plans.md). Use that page for active / TODO / parked status before reading dated reports.

2026-06-21 late update: completed items were moved out of `docs/current-plans.md` so that "plan list" answers show only unfinished or deferred plans. The completed documentation foundation, curated legacy references, PostgreSQL input/backing, and SQLite input/backing remain recorded in this dated inventory.

2026-06-21 follow-up inventory: `docs/current-plans.md` now also carries the remaining generated name migration follow-up, conditional PostgreSQL follow-up, namespace migration, and explicit replan/parked items. This keeps active answers short while preserving known deferred work.

## Rough Work Estimates

2026-06-21 時点の計画見積もり。期限ではなく、作業量の目安として残す。

| Order | Work unit | Status | Rough effort |
| --- | --- | --- | --- |
| 1 | Documentation foundation / docs 第一波の固定 | `DONE` | Large docs commit として完了。README positioning、current plan index、use cases、compatibility、examples index、Laravel baseline を 1 つの意味単位にまとめた。実生成でない generated 風 placeholder は含めない。 |
| 2 | Curated legacy mtool reference snapshots / 整理済み旧 mtool 参照 snapshot | `DONE` | 30-60 分級。主に safety scan、README guardrails、whitespace policy、snapshot commit。 |
| 3 | PostgreSQL existing schema input draft / PostgreSQL 既存スキーマ入力ドラフト | `DONE` | 30-60 分級で完了。schema / seed / scenario、representative query、import notes、generation plan。5 と同じ commit で裏付けメモを追加。 |
| 4 | SQLite API generation input draft / SQLite API 生成入力ドラフト | `DONE` | 45-90 分級で完了。SQLite schema、seed data、CRUD smoke、import notes、generation plan。6 と同じ commit で裏付けメモを追加。 |
| 5 | PostgreSQL actual-output backing / PostgreSQL 実出力の裏付け | `DONE_WITH_3` | current actual-output sample12 と PostgreSQL import gate に接続。scenario 固有 output はまだ `reference/` に置かない。 |
| 6 | SQLite actual-output backing / SQLite 実出力の裏付け | `DONE_WITH_4` | current sample18 actual output に接続。scenario 固有 output はまだ `reference/` に置かない。 |
| 7 | AI context source output / AI 文脈 Source Output 化 | `TODO` | 半日以上。`AI-CONTEXT-MD` source output を実装してから example を追加する。 |
| 8 | Modernization audit MVP generator / 現代化診断 MVP generator | `TODO` | 半日以上。診断 generator を実装してから audit example を追加する。 |
| 9 | Goal-based help and wrapper CLI roadmap / 目的別 help と wrapper CLI roadmap | `TODO` | 30-60 分。goal-based help group と `mtool init / inspect / generate / audit / doctor` などの wrapper CLI roadmap を設計する。 |
| 10 | Namespace migration / namespace migration | `LATER` | 最初の機械的 cleanup は 30-90 分。runtime class / autoload / tests まで広げる場合は半日以上。 |

## Current Reading

| Area | Current status | Reading |
| --- | --- | --- |
| Security / auth / SSO | `DONE_FOR_CURRENT_SCOPE` | security foundation、API auth v2、static bearer、SSO first slice、generated runtime security baseline、OIDC JWT generated runtime verification は current baseline 完了。 |
| Custom proxy bundle | `DONE` | `project-core` bundle に custom proxy metadata / steps / target bindings / auth policy refs を含められる。 |
| Generated name migration | `FIRST_SLICE_APPLIED` | physical / logical / generated naming helper、snapshot / transform / compare / keyword scan lane、Mtool runtime reference first sync が完了。 |
| PostgreSQL user DB output | `REPRESENTATIVE_AND_EBOOK_SET_READY` | user DB / generated output 側の opt-in PostgreSQL lane は sample06/08/09/10/12/18/19/21-26 と代表 output contracts まで進行。Mtool config store PostgreSQL support ではない。 |
| Sample tutorial lane | `CURRENT / MAINTAIN` | `sample01-26` は current。次に増やすなら roadmap、README、Make target、checker、reference output を同時更新する。 |
| Sales / positioning assets | `ACTIVE_NEXT` | README / docs / examples を database-first, existing-database-first, legacy modernization, AI-readable context の入口として整える段階。 |
| Mtool namespace migration | `LATER CLEANUP` | 価値はあるが、database-first positioning と examples より優先しない。 |
| Admin/lab route authorization broad enforcement | `PARKED / REPLAN_GATE` | 実装へ自動遷移しない。deployment need や cluster 単位の明確な要件が出たら再計画する。 |

## Done Since 2026-06-19 Inventory

- Custom proxy metadata bundle coverage first slice is done.
  - `custom-proxies.json` is part of project-core bundle output.
  - custom proxy auth policy refs are validated with the same generated runtime auth policy validator.
  - populated secret-like auth policy fields fail bundle preview.
- Generated API `oidc-jwt-bearer` runtime verification is done.
  - generated single proxy runtime verifies JWT bearer tokens.
  - issuer, audience, required claims, JWKS env / URI / discovery are covered by contract tests.
- Generated name migration first slice is applied.
  - physical / logical / generated naming helper exists.
  - import / metadata / bundle paths preserve `physical_name`.
  - opt-in `MTOOL_GENERATED_NAME_POLICY=physical-logical-v1` output contracts exist.
  - Mtool runtime reference has been synchronized through the boundary-aware rename pipeline.
- PostgreSQL user DB output lane is no longer just a first CRUD probe.
  - representative live contracts cover sample06, sample08, sample09, sample10, sample12, sample18, sample19, and ebook sample21 through sample26.
  - sample13, sample14, and sample16 are covered by focused PostgreSQL naming policy output contracts.
  - full regression after generated naming follow-up passed with the opt-in live PostgreSQL sample12 test skipped when DSN is absent.
- Database-first positioning work has started.
  - README entry is being shifted toward database-first / existing-database-first toolkit positioning.
  - `docs/use-cases.md` exists as the use-case-facing companion to `docs/mtool-positioning.md`.
  - `examples/README.md` defines scenario-oriented examples separately from `sample/tutorials/`.
  - `docs/reports/2026/2026-0621-database-first-sales-assets-plan.md` tracks the future `AI-CONTEXT-MD` source output and audit generator ideas, but no generated examples are stored yet.
  - `examples/postgresql-existing-schema/` is backed by current sample12 actual output and PostgreSQL import gates without copying unrelated generated output into `reference/`.
  - `examples/sqlite-api-generation/` is backed by current sample18 actual output without copying unrelated generated output into `reference/`.
  - `docs/reports/2026/2026-0621-database-first-sales-assets-plan.md` captures the next sales-asset lane.

## Active Next Work

### 1. Database-first sales asset lane

This is the current recommended next lane.

First useful slice:

- stabilize README top positioning and bilingual wording;
- keep `docs/use-cases.md` focused on use cases, not implementation internals;
- keep `examples/README.md` as an index and scope contract;
- keep generated-looking example directories reserved for actual Mtool output.

Recommended first examples:

1. `examples/laravel-mysql-legacy/`
   - strongest modernization sales story;
   - should be synthetic but executable;
   - should start with schema, seed, baseline check, import notes, and generation plan only until actual Mtool output is available.
2. `examples/postgresql-existing-schema/`
   - connects to the new PostgreSQL user DB output lane;
   - input draft exists;
   - current backing points to actual sample12 output and PostgreSQL import gates without claiming Mtool config store PostgreSQL support.

### 2. PostgreSQL follow-up

Do not expand PostgreSQL coverage just for count. Add new live PostgreSQL contract coverage only when a new sample introduces a genuinely new DBAccess behavior surface.

Practical next decisions:

- whether to add a reusable PostgreSQL compose profile for contract gates;
- whether sample12 PostgreSQL live import should get a documented local runbook;
- whether PostgreSQL example work should reuse the existing opt-in contract lane or stay as a fixed sales example first.

### 3. Generated name migration follow-up

The first slice is enough to support PostgreSQL continuation and self-host safety. Next work should stay pipeline-driven:

- produce after snapshots with the new naming policy for representative samples;
- compare with explicit keyword maps;
- expand only when conflicts are zero or classified;
- avoid manual edits to generated artifacts as a substitute for generator / transform rule fixes.

## Parked

- Mtool config store PostgreSQL support.
- SQL Server user DB support.
- Oracle support.
- repo-wide Mtool namespace migration.
- broad Mtool admin/lab authorization enforcement.
- approval workflow.
- rollback / revision history.
- local app packaging.
- production CMS features for the ebook sample lane.
- Japanese invoice / billing / tax / compliance SaaS example until domain review is available.

## Guardrails

- `original-codes/` remains host-side reference only.
- Do not reopen 5 月 broad rewrite plans as active parent plans unless a new concrete regression points there.
- Do not treat Mtool config store portability and generated user DB dialect support as the same lane.
- Do not turn tutorial sample follow-up into production product work by default.
- Do not claim non-PHP generated language support as current refactored support yet, even though the legacy implementation had references for C#, Java, Objective-C, and Swift.
- Do not claim AI context generation as a finished source output until `AI-CONTEXT-MD` or an equivalent generator exists.

## Source Of Truth Links

- Current prior inventory: `docs/reports/2026/2026-0619-plan-inventory.md`
- Post-security priority plan: `docs/reports/2026/2026-0620-post-security-feature-priority-plan.md`
- Generated name migration: `docs/reports/2026/2026-0620-generated-name-migration-plan.md`
- PostgreSQL user DB output: `docs/reports/2026/2026-0620-postgresql-user-db-output-first-slice.md`
- Database-first sales assets: `docs/reports/2026/2026-0621-database-first-sales-assets-plan.md`
- Use cases: `docs/use-cases.md`
- Examples index: `examples/README.md`
