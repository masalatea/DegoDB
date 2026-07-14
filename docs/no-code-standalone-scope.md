# Standalone No-Code Scope / no-code単体scope

English companion: This document defines the bounded standalone no-code scope that Mtool owns before broader external app or shared-state work.

This document defines the Mtool-owned standalone no-code scope.

この文書は、Mtool が所有する no-code 単体 scope を定義する。

## Purpose / 目的

Standalone no-code is the Mtool-owned app surface generated from database-first canonical metadata and Source Output artifacts.

no-code 単体とは、database-first canonical metadata と Source Output artifact から生成される、Mtool 所有の app surface を指す。

This scope should be completed before moving to broader future lanes such as WebSocket/shared-state sync or deeper external app framework integration.

この scope は、WebSocket / shared-state sync や外部 app framework 連携の深掘りより先に完了させる。

Planning report:

- [2026-0714 No-Code Standalone Completion Scope](reports/2026/2026-0714-no-code-standalone-completion-scope.md)

## In scope / scope内

Mtool owns and may complete these standalone no-code surfaces:

| Area | Scope |
| --- | --- |
| Metadata | screen definition, runtime preview metadata, action intent metadata, readiness metadata |
| Generated output | `NO-CODE-RUNTIME` Source Output, runtime preview JSON, runtime preview HTML |
| Screens | list, detail, form, selected row/key markers, generated field display/input metadata |
| Actions | action intent draft, required field status, readiness state, guarded submit/outbox handoff where supported |
| Preview | publish candidate, approval, current preview, alias preview |
| Execution boundary | managed operation / guarded route / outbox boundary where explicitly supported |
| Validation | fast contract tests, representative browser smoke, sample qualification checklist |
| Documentation | tryout guide, testing guide, support boundary, completion inventory |

## Out of scope / scope外

Standalone no-code completion does not include:

| Area | Reason |
| --- | --- |
| Complete conversion of every sample | Supported capability matrix matters more than sample count. |
| Replacement of all custom code | Generated and custom code may coexist permanently. |
| Full arbitrary UI builder | Standalone scope is metadata-driven generated runtime, not a general design tool. |
| Production React/Flutter/React Native generation | External app framework handoff is a separate lane. |
| Native app wrapping | Mobile wrapper/app framework work is separate. |
| WebSocket/shared-state sync | Roadmap candidate, separate future lane. |
| Offline sync by default | Requires explicit sync contract. |
| Hidden browser-only mutation model | Execution must go through explicit server-side authority. |

## Completion definition / 完了定義

Standalone no-code is complete for the current scope when:

1. the supported capability matrix is explicit;
2. representative samples map to the supported capabilities;
3. current docs explain the standalone no-code path without relying on dated reports;
4. unsupported or future capabilities are explicitly parked;
5. remaining in-scope gaps are implemented or documented as not required;
6. validation gates are named and pass for the representative samples;
7. a dated completion report links to evidence.

This is a supported-contract completion definition. It is not a promise that every application can be fully generated without custom code.

## Supported capability matrix / supported capability matrix

Initial matrix for completion review:

| Capability | Required for completion | Evidence target |
| --- | --- | --- |
| List screen | Yes | representative generated runtime sample |
| Detail screen | Yes | representative generated runtime sample |
| Form screen | Yes | representative generated runtime sample |
| Read-only preview | Yes | current/alias public preview |
| Action intent draft | Yes | generated runtime metadata and UI marker |
| Required field readiness | Yes | fast contract or browser smoke |
| Managed submit/outbox handoff | Yes where action execution is supported | sample18/sample28/sample29/sample31 style evidence |
| Server authority boundary | Yes | guarded route / outbox / execution policy docs and tests |
| Current/alias approval flow | Yes | publish candidate approval evidence |
| Representative browser smoke | Yes | public preview/current/alias integration gate |
| Full CRUD for all actions | No | parked unless explicitly scoped |
| Offline sync | No | requires separate sync contract |
| Realtime shared state | No | separate roadmap candidate |
| External app handoff | No for standalone completion | separate mobile/external app scope |

## Validation rule / validation rule

Validation should prefer fast contract tests for generated metadata and representative browser smoke for public preview integration.

Use browser smoke when checking:

- current/alias routing;
- auth/public preview integration;
- submit/outbox handoff;
- rendered feedback that cannot be proven clearly with fast metadata tests.

Use fast tests when checking:

- schema shape;
- screen/action metadata;
- readiness markers;
- required field status;
- no hidden mutation binding.

## Relationship to output modes / output modeとの関係

This document defines the standalone `mtool_no_code` surface.

Related:

- [Mobile Output Modes / mobile output modes](mobile-output-modes.md)
- [Mobile Ownership Boundaries / mobile ownership boundary](mobile-ownership-boundaries.md)

The `external_no_code` and `hybrid` modes are separate from standalone completion.

## Next review steps / 次review

1. Inventory current evidence.
2. Identify gap-only items.
3. Implement only bounded gaps.
4. Publish a standalone no-code completion report.
