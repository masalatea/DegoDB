# Next Priority Selection Inventory / 次優先度選定棚卸し

Date: 2026-07-13

Status: `PRIORITY_DECISION_REQUIRED`

## Context / 背景

Firebird local durable DB profile and the first mobile app handoff lane reached their current checkpoints.

- Firebird is complete for the current narrow source-inspection scope.
- Mobile handoff is complete for C1 wrapper-readiness.
- No active main implementation lane remains in `docs/current-plans.md`.

Therefore the next safe step is not to silently reopen a large parked lane, but to choose the next priority explicitly.

## Completed checkpoints / 完了checkpoint

| Area | Current result | Notes |
| --- | --- | --- |
| Firebird local durable DB profile | `CHECKPOINTED` | Current scope is narrow source-inspection support, not full Mtool internal config-store replacement. |
| PostgreSQL generated output support | `DONE_FOR_OUTPUT_SCOPE` | Mtool internal PostgreSQL config-store support is explicitly unnecessary. |
| No-code sample / Mtool supported-contract coverage | `DONE_SUPPORTED_CONTRACT_COVERAGE` | This means tool-covered scope is covered; it does not mean every screen is fully generated. |
| Mobile app handoff C1 | `CHECKPOINTED` | Spec shape, validator, React/web wrapper target contract, Capacitor proof boundary, package builder, controlled emitter, and sample28 helper are done. |

## Candidate next priorities / 次候補

| Candidate | Why it might be next | Reopen condition | Risk / caution |
| --- | --- | --- | --- |
| Mobile C1 productization route | Make the current handoff package easier for app creators to obtain from Mtool output. | User-facing adoption need exists for a CLI/source-output route such as `mobile-wrapper-target/`. | Avoid generating native projects too early; keep the boundary as input/output contract first. |
| Flutter / React Native packet planning | Extend the current mobile handoff idea to additional app-builder targets. | A target platform is selected. | Should remain an input packet/contract first, not a full native app generator. |
| Custom operation execution routes | Continue the no-code execution story beyond current guarded/dry-run boundaries. | A concrete route cluster is selected. | Needs auth, CSRF, audit, stale-artifact, and irreversible-action policy before implementation. |
| Mtool admin/lab authorization hardening | Reduce risk around admin/lab surfaces before broader operation exposure. | Deployment/admin exposure requirements become concrete. | Should not become a vague whole-app security rewrite. |
| SQL Server / Oracle output support | Enterprise DB output support. | Real user demand or sample target exists. | Parked until demand; large matrix cost. |
| Japanese invoice / billing / compliance sample | Domain sample that may be commercially useful. | Domain review is available. | Legal/accounting details should not be guessed. |
| Mtool self no-code replacement | Dogfooding of contained Mtool workflows. | A small, contained workflow is selected despite supported-contract coverage already being complete. | Do not reopen as “make all Mtool no-code”; that contradicts the agreed 80–90% automation philosophy. |

## Recommendation / 推奨

Superseded correction on 2026-07-13: before choosing among unrelated next priorities, resolve the Firebird Mtool-support scope ambiguity recorded in `docs/reports/2026/2026-0713-firebird-mtool-support-scope-correction.md`.

Keep unrelated implementation paused until the Firebird Mtool-support boundary is made explicit in `docs/current-plans.md`.

## Promotion decision guide / 昇格判断ガイド

Use this guide when choosing the next active lane.

| Situation / 状況 | Prefer / 優先候補 | Why / 理由 |
| --- | --- | --- |
| The next goal is to make the current mobile handoff usable by app creators. | Mobile C1 productization route | It builds directly on completed C1 evidence and keeps output as a safe handoff package. |
| The next goal is to support a specific app-builder ecosystem. | Flutter or React Native packet planning | It should define input/output expectations before any native project generation. |
| The next goal is real no-code mutation/execution beyond guarded examples. | Custom operation execution routes | This is the natural continuation of action metadata, but it must begin with explicit safety policy. |
| The next goal is safer internal/admin exposure. | Mtool admin/lab authorization hardening | This should precede broader operation exposure when deployment risk is the concern. |
| The next goal is enterprise database reach. | SQL Server / Oracle output support | Reopen only with a support-scope decision because matrix cost can grow quickly. |
| The next goal is a commercially recognizable Japanese business sample. | Japanese invoice / billing / compliance sample | Reopen only with domain review so the sample does not encode guessed compliance behavior. |
| The next goal is dogfooding Mtool itself. | Mtool self no-code replacement | Reopen only as one contained partial/hybrid workflow; never as full Mtool conversion. |

Recommended default if the user only says "continue": continue the active Firebird Mtool-support scope correction/replan until its remaining slices are explicit. Do not promote Mobile C1 productization ahead of that correction.
