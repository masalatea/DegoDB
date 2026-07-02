# 2026-0702 Post-Custom-Public-Alias No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Public alias delete workflow first slice** as the next product-facing code slice.

Custom public alias storage made stable alias URLs possible. The next smallest management continuation is to let an operator/admin remove an alias so a stale or accidental public alias can be withdrawn without changing candidate approval history.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Public alias delete workflow first slice | 0.5 - 1 day | Selected. Adds alias listing and guarded deletion for existing public runtime aliases. |
| Broader rollback workflow polish | 1 - 3 days | Deferred until alias lifecycle has a minimal removal path. |
| Public delivery closure report | 0.5 - 1 day | Deferred until alias deletion lands. |

## Boundary

In scope:

- list configured public runtime aliases on the `NO-CODE-RUNTIME` detail page;
- operator/admin deletion action for an alias;
- repository helper that removes the alias row so the alias route resolves to not found;
- focused repository/static UI contract coverage.

Out of scope:

- soft-delete history or alias deletion event stream;
- custom domain or CDN configuration;
- package copy/static hosting;
- push.

## Verification

Implementation selected immediately after this planning step.
