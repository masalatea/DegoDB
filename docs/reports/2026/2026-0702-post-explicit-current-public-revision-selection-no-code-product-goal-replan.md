# 2026-0702 Post-Explicit-Current-Public-Revision-Selection No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Custom public alias key storage first slice** as the next product-facing code slice.

Explicit current public revision selection made rollback possible by selecting an approved candidate for `current`. The next smallest public-delivery continuation is to add a stable custom alias path that can point to an approved candidate without changing artifact-key URLs or `current` semantics.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Custom public alias key storage first slice | 1 - 2 days | Selected. Adds alias storage, alias route resolution, and operator/admin alias assignment for approved candidates. |
| Broader rollback workflow polish | 1 - 3 days | Deferred until alias semantics are durable. |
| Public delivery closure report | 0.5 - 1 day | Deferred until alias route first slice lands. |

## Boundary

In scope:

- custom public alias key storage for approved `NO-CODE-RUNTIME` candidates;
- operator/admin action to assign an alias to an approved candidate;
- alias public runtime preview route using `/runs/no-code/{project_key}/alias/{alias_key}/runtime-preview.html`;
- focused repository/static UI contract coverage.

Out of scope:

- separate rollback event stream;
- alias deletion/disable workflow;
- package copy/static hosting;
- custom domain or CDN configuration;
- push.

## Verification

Implementation selected immediately after this planning step.
