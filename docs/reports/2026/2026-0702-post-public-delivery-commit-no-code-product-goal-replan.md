# 2026-0702 Post-Public-Delivery-Commit No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Local app packaging boundary inventory first slice** as the next no-code product-facing lane.

Public Web delivery is now locally committed and verified. The next useful product-facing direction is the app-local side, but packaging should not start with code: the boundary needs to separate generated artifacts, runtime shell assumptions, install/package shape, sync/offline behavior, and target platforms.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Local app packaging boundary inventory first slice | 0.5 - 1 day | Selected. Promote the local app packaging part of the parked broader item into a narrow docs-first boundary. |
| Local app packaging implementation | 2 - 5 days for a first spike after boundary | Deferred until artifact/runtime/package boundaries are explicit. |
| Remote sync transport | 2 - 5 days for a first narrow transport smoke | Deferred. Packaging boundary should clarify whether transport is required for the first app-local milestone. |
| Visual builder / full generated app shell | 1 - 3 weeks or more | Deferred. Current product path still works from canonical metadata and generated artifacts. |
| Public deployment hardening | 1 - 3 days | Parked until custom domain/CDN/static hosting requirements are concrete. |

## Boundary

In scope:

- define local app packaging boundary candidates;
- separate minimum package proof from remote transport, conflict resolution, and native targets;
- update current plan and reports index;
- no code changes.

Out of scope:

- package generator implementation;
- native/iOS/Android/Flutter target implementation;
- remote sync transport;
- push.

## Verification

Docs-only planning step. Current worktree was clean after:

- `e2c5d7e Add no-code public runtime delivery workflow`;
- `c86d70b Record public delivery commit cleanup`.
