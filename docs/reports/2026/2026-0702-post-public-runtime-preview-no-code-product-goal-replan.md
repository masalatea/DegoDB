# 2026-0702 Post-Public-Runtime-Preview No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Public runtime current alias route first slice** as the next product-facing code slice.

The artifact-key public runtime route proved that approved generated `NO-CODE-RUNTIME` output can be served through a public URL. The smallest useful continuation is a stable project-level `current` alias that resolves to the latest approved candidate, while keeping custom slugs, rollback selection, package copying, and broad cache/version policy out of scope.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Public runtime current alias route first slice | 0.5 - 1 day | Selected. Gives users a shareable project-level URL without inventing custom alias storage. |
| Cache/version policy | 0.5 - 1 day | Deferred. Keep `no-store` for this first alias slice and replan immutable/current cache behavior later. |
| Revision selection / rollback boundary | 1 - 3 days | Deferred. Current alias can use latest approved candidate until explicit published revision semantics are needed. |
| Candidate event display polish | 0.5 - 1 day | Deferred. Useful auditability polish, but less public-delivery-facing than the alias route. |

## Boundary

In scope:

- public GET route for `/runs/no-code/{project_key}/current/runtime-preview.html`;
- latest approved `NO-CODE-RUNTIME` candidate lookup;
- existing artifact bundle/manifest storage only;
- static route/auth contract coverage and repository coverage.

Out of scope:

- custom public alias key storage;
- explicit rollback / published revision selection;
- package copying or new storage table;
- broad static file serving;
- custom cache/version policy beyond the existing `no-store`;
- push.

## Verification

Implementation selected immediately after this planning step.
