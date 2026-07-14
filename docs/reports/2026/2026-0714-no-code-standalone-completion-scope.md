# No-code standalone completion scope / no-code単体完了scope

Date: 2026-07-14

## Summary / summary

This report records the next active direction after the mobile/external-app feasibility work.

Decision:

- Keep shared-state/WebSocket/Node.js sync as a roadmap candidate, not the immediate lane.
- Keep external mobile/app framework handoff as completed for the current scope.
- Focus next on completing the standalone Mtool no-code lane.

ここでは、mobile / external app feasibility 後の次 active direction を記録する。

判断:

- shared-state / WebSocket / Node.js sync は roadmap candidate に置き、直近 lane にはしない。
- external mobile / app framework handoff は current scope 完了として扱う。
- 次は Mtool no-code 単体 lane の完了に集中する。

## Meaning of "standalone no-code" / no-code単体の意味

Standalone no-code means Mtool's own generated web/no-code/runtime output, not external app framework handoff.

It corresponds to `mtool_no_code` in `docs/mobile-output-modes.md`, but the scope is broader than mobile:

- generated web/no-code/runtime artifact;
- list/detail/form screens from canonical metadata;
- action intent draft;
- guarded submit/outbox handoff where supported;
- current/alias preview;
- publish candidate approval;
- validation and browser/contract smoke gates;
- operator-facing documentation.

It does not mean:

- complete conversion of every sample;
- replacing all custom code;
- production React/Flutter/React Native app generation;
- native app wrapping;
- realtime shared-state sync;
- offline sync by default;
- arbitrary UI builder coverage.

## Boundary / 境界

Mtool owns for standalone no-code:

| Area | Mtool responsibility |
| --- | --- |
| Metadata contract | screen definition, runtime preview, action intent metadata, readiness metadata |
| Generated artifact | `NO-CODE-RUNTIME` source output, runtime preview JSON/HTML, publish candidate |
| Preview routing | current/alias public preview, approval path |
| Execution handoff | managed operation / guarded submit / outbox boundary where supported |
| Validation | fast contract tests, representative browser smoke, sample qualification checklist |
| Documentation | tryout guide, testing guide, support boundary, completion inventory |

Mtool does not own in this lane:

| Area | Reason |
| --- | --- |
| External FE/app framework handoff | Already completed as separate mobile/external scope. |
| Native app generation | Out of standalone no-code scope. |
| WebSocket/shared-state sync | Roadmap candidate, separate future lane. |
| Full custom-code replacement | Explicitly not required; generated and custom code may coexist. |
| Full CRUD for every domain | Only declared supported capability matrix is required. |
| Offline sync | Requires explicit sync contract. |

## Completion target / 完了target

The completion target is not "everything no-code can ever do".

The target is:

> Mtool's standalone no-code path is clearly bounded, documented, validated by representative samples, and ready to be used as the default Mtool-owned app surface for supported cases.

## Suggested active sequence / active sequence案

| Step | Work unit | Exit condition |
| --- | --- | --- |
| NC-S1 | Standalone no-code boundary spec | A date-less doc defines Mtool-owned standalone no-code scope, non-goals, and completion definition. |
| NC-S2 | Current evidence inventory | Existing samples/tests/docs are mapped to the supported no-code capability matrix. |
| NC-S3 | Gap-only implementation plan | Any remaining gaps are listed as concrete, bounded implementation items; non-required items are explicitly parked. |
| NC-S4 | Implement remaining bounded gaps | Only gaps inside the standalone scope are implemented, with tests. |
| NC-S5 | Completion report | A dated report states standalone no-code is complete for the declared scope and links to evidence. |

## Likely evidence sources / 想定evidence source

Stable docs:

- `docs/no-code-tryout.md`
- `docs/no-code-ui-testing.md`
- `docs/no-code-l1-sample-qualification-checklist.md`
- `docs/overview.md`
- `docs/execution-success-policy.md`
- `docs/mobile-output-modes.md`
- `docs/mobile-ownership-boundaries.md`

Representative samples:

- sample18 mini task board;
- sample28 no-code data app MVP;
- sample29 support case demo;
- sample31 inventory request demo;
- sample32 no-code UI test lab;
- sample19 material-to-no-code validation pipeline, if needed for AI/material handoff evidence.

## Relationship to other roadmap items / 他roadmapとの関係

Shared-state/WebSocket:

- parked as `ROADMAP_CANDIDATE_NOT_ACTIVE`;
- not part of standalone no-code completion.

External app/mobile framework:

- completed for current handoff/FS/policy scope;
- future work is new scoped implementation.

Mtool self no-code replacement:

- still not full replacement;
- reopen only for contained partial/hybrid workflow after standalone no-code scope is complete.

## Status / status

Status: `ACTIVE_REPLAN_CANDIDATE`.

Promote this into `docs/current-plans.md` as the next active lane if accepted.
