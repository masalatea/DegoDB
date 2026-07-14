# Firebird / Mobile C1 Current Plan History / Firebird・Mobile C1 current plan history

Date: 2026-07-13

Status: `ARCHIVED_FROM_CURRENT_PLANS`

This report preserves the completed current-plan rows that were removed from `docs/current-plans.md` after both lanes reached checkpoints.

この report は、Firebird lane と Mobile C1 lane が checkpoint に到達した後、`docs/current-plans.md` から移動した完了済み current-plan 行を保存します。

## Main Plan rows #885-#900 / Main Plan #885-#900

| Order | Work unit / 作業の塊 | Commit unit / コミット単位 | Status | Result / 結果 |
| --- | --- | --- | --- | --- |
| 885 | Firebird local durable DB profile feasibility / Firebird local durable DB profile feasibility | Confirm the product position, current Firebird embedded/local-file reality, PHP driver path, setup burden, backup/restore expectations, and non-goals before code changes | `DONE_F1` | inventory, risk matrix, and F2 proof target recorded / 棚卸し・risk matrix・F2 proof target 記録済み |
| 886 | Firebird local connection proof / Firebird local connection proof | Prove the narrowest local Firebird connection path usable by Mtool, preferably file-oriented and serverless/low-server-burden; keep Docker as CI aid, not the product requirement | `DONE_DOCKER_PROOF` | Firebird 5.0.4 server + PHP 8.4 `PDO_FIREBIRD` client smoke passed / Firebird 5.0.4 server + PHP 8.4 `PDO_FIREBIRD` client smoke 成功 |
| 887 | Firebird config-store fit and schema preflight / Firebird config-store fit・schema事前設計 | Decide whether the Mtool config schema can run on Firebird as a local durable profile and identify required dialect, identifier, sequence, BLOB, and migration differences | `DONE_FIRST_SLICE_DDL_PROOF` | read-only preflight plus 5-table disposable Firebird DDL apply/metadata proof completed / read-only preflight と 5 table disposable Firebird DDL apply・metadata proof 完了 |
| 888 | SQLite-to-Firebird promotion contract / SQLiteからFirebird promotion contract | Define a one-way upgrade path from SQLite lightweight profile to Firebird local durable profile, including type/value mapping, file ownership, backup, validation, and rollback evidence | `DONE_CONTRACT_SHAPE` | side-effect-free Firebird local durable contract, type mapping, source retention, and validation gates added / 副作用なし Firebird local durable contract・type mapping・source retention・validation gate 追加済み |
| 889 | Firebird-to-MySQL/MariaDB promotion boundary / FirebirdからMySQL・MariaDB promotion境界 | Reuse the existing offline promotion model where possible and define only the deltas needed for Firebird source to MySQL/MariaDB target | `DONE_BOUNDARY` | reuse/delta boundary and first source-inspection candidate recorded / reuse・差分境界と first source-inspection candidate 記録済み |
| 890 | Firebird lane checkpoint / Firebird lane checkpoint | Record supported boundary, non-goals, verification gates, and whether to proceed to implementation, park, or stop | `DONE_NARROW_GO` | feasibility complete; proceed only with source-inspection first slice, not broad Firebird support / feasibility完了。全面Firebird対応ではなく source-inspection first slice のみ継続 |
| 891 | Firebird source inspection adapter first slice / Firebird source inspection adapter first slice | Add a read-only Firebird metadata/value-profile normalizer compatible with the promotion contract, starting with array-level normalization and optional Docker/live proof later | `DONE_ARRAY_NORMALIZER` | pure metadata/value-profile normalizer and focused tests added / pure metadata・value-profile normalizer と focused tests 追加済み |
| 892 | Firebird source inspection Docker/live metadata smoke / Firebird source inspection Docker/live metadata smoke | Feed real Firebird metadata rows from the Docker proof database into the source inspection normalizer without mutation | `DONE_LIVE_METADATA_SMOKE` | Docker proof DB metadata normalized with no blockers / Docker proof DB metadata を blocker なしで normalize 済み |
| 893 | No-code app/mobile handoff spec output / No Code app・mobile handoff spec output | Define app-creator-facing output for mobile wrapper tooling, starting with React/Web + Capacitor-style iOS/Android wrapper targets | `DONE_SPEC_SHAPE` | `mobile-app-handoff.json` / `.md` v1 shape and platform target matrix defined / `mobile-app-handoff.json`・`.md` v1形状とplatform target matrix定義済み |
| 894 | Mobile app handoff spec validation / mobile app handoff spec validation | Validate that the handoff spec is complete and unambiguous enough for an app creator, Codex/Claude, or mobile builder to proceed without guessing core app behavior | `DONE_VALIDATOR_FIRST_SLICE` | `app_mobile_app_handoff_validate()` and representative fixture test added / `app_mobile_app_handoff_validate()` と representative fixture test 追加済み |
| 895 | React/web wrapper target contract / React・Web wrapper target contract | Derive the exact React/web wrapper inputs and constraints from the validated mobile handoff packet; no native build yet | `DONE_CONTRACT_SHAPE` | `docs/mobile-react-wrapper-target-contract.md` added / `docs/mobile-react-wrapper-target-contract.md` 追加済み |
| 896 | Capacitor wrapper proof boundary / Capacitor wrapper proof boundary | Plan the narrow first proof around generated web/no-code runtime + React wrapper input, without owning production native build/signing | `DONE_PROOF_BOUNDARY` | C1/C2/C3 split and sample28 candidate recorded / C1/C2/C3分割とsample28候補を記録済み |
| 897 | Mobile wrapper target C1 package first slice / mobile wrapper target C1 package first slice | Generate or validate a `mobile-wrapper-target/` package from the validated handoff and wrapper target contract, starting with sample28 | `DONE_IN_MEMORY_PACKAGE_BUILDER` | side-effect-free `app_mobile_wrapper_target_build_c1_package()` and focused tests / 副作用なし `app_mobile_wrapper_target_build_c1_package()` と focused tests |
| 898 | Mobile wrapper target artifact emission / mobile wrapper target artifact emission | Emit the in-memory C1 package to a controlled artifact directory with source refs and no native project mutation | `DONE_CONTROLLED_EMITTER` | `app_mobile_wrapper_target_emit_c1_package()` writes only C1 files and refuses overwrite / `app_mobile_wrapper_target_emit_c1_package()` がC1 fileのみを書き出し上書き拒否 |
| 899 | Sample28 mobile wrapper target artifact integration / sample28 mobile wrapper target artifact integration | Wire the C1 package emitter into a sample28-oriented artifact proof without touching native projects | `DONE_SAMPLE28_C1_HELPER` | sample28 handoff and emit helper covered by focused tests / sample28 handoff・emit helperをfocused testで確認済み |
| 900 | Mobile wrapper target lane checkpoint / mobile wrapper target lane checkpoint | Decide whether the next step is CLI/source-output route integration or parking after the C1 proof | `DONE_C1_CHECKPOINT_PARK_CLI_ROUTE` | C1 complete; CLI/source-output route parked until adoption need / C1完了。CLI/source-output routeは採用需要までpark |

## Immediate sequence F1-F6 / I1-I2 / M1-M8

| Order | Work unit / 作業単位 | Scope / 範囲 | Status |
| --- | --- | --- | --- |
| F1 | Firebird feasibility inventory / Firebird feasibility棚卸し | Verify current Firebird local/embedded story, PHP access, platform support, backup tools, and Mtool fit | `DONE` |
| F2 | Local connection proof / local connection proof | Establish the smallest local Firebird database create/connect/transaction path | `DONE_DOCKER_PROOF` |
| F3 | Config-store schema fit / config-store schema fit | Compare Mtool config schema requirements against Firebird dialect and metadata behavior | `DONE_FIRST_SLICE_DDL_PROOF` |
| F4 | SQLite to Firebird upgrade path / SQLiteからFirebird upgrade path | Plan side-effect-free promotion from SQLite local profile to Firebird durable profile | `DONE_CONTRACT_SHAPE` |
| F5 | Firebird to MySQL/MariaDB one-way promotion / FirebirdからMySQL・MariaDB一方向promotion | Define reuse/delta against existing SQLite-to-MySQL promotion chain | `DONE_BOUNDARY` |
| F6 | Lane checkpoint / lane checkpoint | Decide whether to implement, park, or stop based on evidence | `DONE_NARROW_GO` |
| I1 | Firebird source inspection adapter first slice / Firebird source inspection adapter first slice | Start narrow implementation at source inspection only; no target import/cutover | `DONE_ARRAY_NORMALIZER` |
| I2 | Firebird source inspection live metadata smoke / Firebird source inspection live metadata smoke | Use Docker Firebird proof DB to collect real metadata rows and pass them through the pure normalizer | `DONE_LIVE_METADATA_SMOKE` |
| M1 | Mobile handoff spec output / mobile handoff spec output | Define app-creator-facing output before wrapper implementation | `DONE_SPEC_SHAPE` |
| M2 | Mobile handoff spec validation / mobile handoff spec validation | Validate packet completeness before wrapper implementation | `DONE_VALIDATOR_FIRST_SLICE` |
| M3 | React/web wrapper target contract / React・Web wrapper target contract | Define wrapper preparation inputs and constraints before any native build | `DONE_CONTRACT_SHAPE` |
| M4 | Capacitor wrapper proof boundary / Capacitor wrapper proof boundary | Define the smallest iOS/Android wrapper proof before any production native build/signing | `DONE_PROOF_BOUNDARY` |
| M5 | Mobile wrapper target C1 package first slice / mobile wrapper target C1 package first slice | Generate or validate a wrapper-readiness package before any Capacitor/native project work | `DONE_IN_MEMORY_PACKAGE_BUILDER` |
| M6 | Mobile wrapper target artifact emission / mobile wrapper target artifact emission | Write the C1 package into a controlled artifact location without touching a native project | `DONE_CONTROLLED_EMITTER` |
| M7 | Sample28 mobile wrapper target artifact integration / sample28 mobile wrapper target artifact integration | Connect the C1 package emitter to the first candidate sample proof | `DONE_SAMPLE28_C1_HELPER` |
| M8 | Mobile wrapper target lane checkpoint / mobile wrapper target lane checkpoint | Choose CLI/source-output route integration or checkpoint/park after the C1 proof | `DONE_C1_CHECKPOINT_PARK_CLI_ROUTE` |

## Checkpoint result / checkpoint結果

- Firebird lane is complete for the current narrow source-inspection scope. Broad Firebird support remains outside the current active plan.
- Mobile app/mobile wrapper lane is complete for C1 wrapper-readiness. CLI/source-output route integration, Flutter input packets, React Native input packets, and real Capacitor/native project work are parked until a concrete adoption need appears.
