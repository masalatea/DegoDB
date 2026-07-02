# 2026-0702 Post-Rollback-Workflow-Polish No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Public delivery browser smoke first slice** as the next no-code product-facing slice.

Rollback workflow polish clarified the operator/admin semantics for current rollback and alias non-follow behavior. The next practical gap is verification: the public artifact-key, current, and alias preview URLs should be exercised as browser-visible runtime pages, not only as route/static contract coverage.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Public delivery browser smoke first slice | 0.5 - 1 day | Selected. It verifies artifact/current/alias public preview routes with the existing generated runtime browser smoke. |
| Alias lifecycle audit trail | 1 - 2 days | Deferred. Useful for operations, but less urgent than proving the public route experience end to end. |
| New product-facing continuation outside public delivery | Replan first | Deferred until the public delivery route verification gap is closed. |

## Boundary

In scope:

- sample28 public runtime smoke fixture;
- approved/current/alias seed for a generated `NO-CODE-RUNTIME` artifact;
- browser-level checks for artifact-key/current/alias preview URLs;
- cache header checks for immutable artifact URL and no-store moving URLs;
- docs and plan index update.

Out of scope:

- new public route behavior;
- visual redesign of generated runtime;
- alias audit history;
- push.

## Verification

Implementation selected immediately after this planning step.
