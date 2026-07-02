# Local App Packaging Boundary Inventory First Slice / local app packaging boundary inventory first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

This first slice defines the boundary for a future local app packaging lane after no-code public Web delivery was committed locally. The next implementation should prove a packageable app-local runtime artifact without taking on native targets, remote transport, conflict resolution, or a full visual-builder/app-shell project.

この first slice では、no-code public Web delivery を local commit にまとめた後の local app packaging lane の境界を定義する。次の実装は、native target、remote transport、conflict resolution、full visual-builder / app-shell project を背負わず、package 可能な app-local runtime artifact を証明するべき。

## Current Assets / 現在ある資産

- Generated no-code Web/runtime artifacts exist as `runtime-preview.html` and `runtime-preview.json`.
- React bridge and schema-form comparison artifacts exist for sample28.
- App-local persistence foundations exist through generated DTO/SQLite save/read and sample27.
- Managed operation sync intent/outbox and App-local/server handlers exist through sample30.
- Public delivery workflow now covers approved runtime publication for the Web preview path.

## Packaging Boundary / packaging 境界

Minimum local app packaging should prove:

- a deterministic package artifact from existing generated no-code/app-local outputs;
- package metadata that records project key, source output key, artifact key, runtime/contract versions, and included files;
- an operator/admin surface that shows package readiness and blockers;
- a focused smoke that unpacks or inspects the package and verifies required runtime files;
- no remote transport requirement for the first package proof.

Out of scope for the first implementation:

- native iOS/Android packaging;
- Flutter output;
- installer signing/notarization;
- remote sync transport;
- conflict resolution;
- scheduler/background sync;
- custom visual builder;
- public app-store style distribution.

## Candidate First Implementation / 次の実装候補

| Candidate | Estimate | Decision |
| --- | --- | --- |
| App-local package manifest first slice | 0.5 - 1.5 days | Recommended next. Add a manifest/summary artifact around existing generated app-local/no-code outputs and verify it with focused tests. |
| Operator package readiness display | 0.5 - 1 day | Useful after manifest shape exists; can show missing files/blockers. |
| Package archive smoke | 1 - 2 days | Useful after manifest/readiness exists; should avoid native installer scope. |
| Remote sync transport smoke | 2 - 5 days | Deferred until packaging proves what should be transported or synchronized. |

## Boundary / 境界

- In scope: inventory, minimum package boundary, next first-slice recommendation.
- Out of scope: code changes, native target implementation, remote transport, push.

## Verification / 検証

Docs-only first slice:

- no code changes;
- `git diff --check` required before commit.

## Next / 次

Run a post-local-app-packaging-boundary-inventory replan. The recommended next implementation is **App-local package manifest first slice**.
