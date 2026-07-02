# 2026-0702 Post-Approval-Transition-Persistence No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Guarded publish candidate detail UI first slice** as the next product-facing code slice.

Approval transition persistence proved the repository-level state machine and append-only transition event storage. The next smallest useful continuation is to expose those helpers from the existing `NO-CODE-RUNTIME` Source Output detail page, without adding public runtime delivery, package exposure, rollback, or a dedicated candidate route set.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Guarded publish candidate detail UI first slice | 1 - 2 days | Selected. It gives operators/admins a visible candidate creation/history/transition surface while reusing the proven repository guards. |
| Dedicated candidate list/detail routes | 1 - 3 days | Deferred. A separate route set can come later if the Source Output detail page becomes too dense. |
| Public runtime URL/package exposure | 2 - 5 days | Deferred. Approval controls should be visible before public delivery semantics are added. |
| Rollback/revision history public selection | 2 - 5 days | Deferred. Requires published revision semantics, not only approved candidate state. |

## Boundary

In scope:

- `NO-CODE-RUNTIME` Source Output detail page only;
- create draft candidate from publishable readiness;
- list candidate history;
- request-review, approve, and reject transition actions;
- CSRF-protected POST handling;
- repository guard reuse and static contract coverage.

Out of scope:

- public runtime URL;
- artifact package exposure;
- rollback;
- dedicated candidate routes;
- push.

## Verification

Planning/report update plus implementation through the selected first slice.
