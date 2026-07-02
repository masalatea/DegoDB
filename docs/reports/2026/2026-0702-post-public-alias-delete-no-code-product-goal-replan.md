# 2026-0702 Post-Public-Alias-Delete No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Public delivery closure first slice** as the next no-code product-facing slice.

The public runtime delivery lane now has approved candidate exposure, artifact-key serving, `current` serving, explicit current selection, stable alias serving, and alias deletion. The next smallest step is to close this lane with a status/boundary report before opening broader rollback polish or another product goal.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Public delivery closure first slice | 0.5 day | Selected. Records what the public runtime delivery lane now supports and what remains outside minimum scope. |
| Broader rollback workflow polish | 1 - 3 days | Deferred. Rollback is possible via selecting an older approved candidate, but richer history/copy can follow after closure. |

## Boundary

In scope:

- summarize the completed public runtime delivery capabilities;
- record the minimum acceptance state for artifact-key, current, alias, and alias deletion flows;
- identify remaining follow-up candidates.

Out of scope:

- new runtime routes;
- soft-delete alias history;
- rollback event stream;
- custom domain/CDN configuration;
- package copy/static hosting;
- push.

## Verification

Docs-only closure selected immediately after this planning step.
