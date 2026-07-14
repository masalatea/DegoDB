# Firebird Mtool Support Scope Correction / Firebird Mtool対応scope訂正

Date: 2026-07-13

Status: `SCOPE_CORRECTION_REPLAN_REQUIRED`

## Why this report exists / このreportの目的

The Firebird lane was checkpointed as "complete for the current narrow source-inspection scope", but that wording was ambiguous against the earlier product intent: evaluate and, if viable, support Firebird as a local durable database profile for Mtool itself.

This report corrects the plan boundary and the implementation order:

- Completed narrow Firebird evidence is still valid.
- It does not mean Firebird is 100% complete.
- Firebird 100% means both MySQL-equivalent Mtool/profile support and migration-path support.
- The implementation order is sample-first, then Mtool itself, then migration paths.

## Completed and still valid / 完了済みで有効なもの

| Area | Status | Meaning |
| --- | --- | --- |
| Firebird feasibility | `DONE` | Firebird remains viable enough as a local durable RDB profile candidate. |
| Docker/PDO connection proof | `DONE_DOCKER_PROOF` | Mtool-side PHP can talk to Firebird in an opt-in Docker proof runtime. |
| Config schema first-slice DDL proof | `DONE_FIRST_SLICE_DDL_PROOF` | A subset of Mtool config schema can be converted and applied to Firebird. |
| SQLite-to-Firebird contract shape | `DONE_CONTRACT_SHAPE` | The side-effect-free promotion artifact shape is defined. |
| Firebird-to-MySQL/MariaDB boundary | `DONE_BOUNDARY` | Reuse/delta boundaries are recorded. |
| Firebird source inspection adapter | `DONE_ARRAY_NORMALIZER_AND_LIVE_METADATA_SMOKE` | Read-only source metadata normalization is proven. |

## Not yet complete / 未完了

These are required before saying "Firebird is 100% complete" without qualification.

| Area | Needed before support claim |
| --- | --- |
| Representative sample support | Selected samples must prove generated DBAccess/runtime dialect, schema, CRUD, transaction, blob/text/json/time, and profile packaging boundaries before Mtool itself is changed. |
| Sample coverage checkpoint | The project must confirm the representative samples cover the agreed Firebird support scope or explicitly record gaps. |
| Full config schema converter | Indexes, unique keys, foreign keys, ALTER, seed DML, identifier policy, text/blob policy, timestamp defaults, and migration ordering must be handled or explicitly excluded. |
| Config-store runtime backend | Mtool config-store connection/init/migration/read/write paths must support a Firebird profile or an explicit adapter boundary. |
| End-to-end Mtool smoke | Mtool must initialize and operate against Firebird in an opt-in profile without normal tests requiring Firebird. |
| SQLite-to-Firebird migration path | The existing contract shape must become a tested one-way migration path. |
| Firebird-to-MySQL/MariaDB migration path | The existing boundary plan must become a tested one-way migration path. |
| Product packaging | Local Docker/server profile vs embedded/local-file profile must be documented so app creators are not promised a serverless path prematurely. |

## Corrected priority / 訂正後の優先度

Until this scope is resolved, the next main plan should be Firebird 100% support replan, not Mobile C1 productization.

Mobile C1 remains a valid later lane, but it should not hide the unresolved Firebird support expectation.

Corrected order:

1. Sample support.
2. Confirm the agreed Firebird scope is covered by samples.
3. Adapt Mtool itself.
4. Implement the two migration paths: SQLite -> Firebird and Firebird -> MySQL/MariaDB.

## Corrected remaining sequence / 訂正後の残sequence

| Order | Work unit | Exit condition |
| --- | --- | --- |
| F100-0 | Correct Firebird 100% definition | `docs/current-plans.md` distinguishes narrow source-inspection completion from Firebird 100% completion. |
| F100-1 | Select representative Firebird samples | Sample list and coverage matrix exist. |
| F100-2 | Implement sample Firebird support | Selected samples run against Firebird via focused tests/smokes. |
| F100-3 | Confirm sample scope coverage | Coverage gaps are closed or explicitly deferred. |
| F100-4 | Adapt Mtool itself to Firebird | Mtool initializes and operates against Firebird in an opt-in profile. |
| F100-5 | Implement SQLite -> Firebird migration path | SQLite local/lightweight profile can promote to Firebird local durable profile with validation evidence. |
| F100-6 | Implement Firebird -> MySQL/MariaDB migration path | Firebird profile can promote to MySQL/MariaDB with validation evidence. |
| F100-7 | Firebird 100% checkpoint | Firebird support is qualified as 100% for the agreed scope, or remaining gaps are explicitly parked. |
