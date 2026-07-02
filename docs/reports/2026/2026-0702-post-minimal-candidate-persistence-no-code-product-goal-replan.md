# 2026-0702 Post-Minimal-Candidate-Persistence No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Approval transition persistence first slice** as the next product-facing code slice.

Minimal candidate persistence now stores durable draft candidate revisions and supports scoped list/find reads. The next smallest useful continuation is to let those stored candidates move through the first approval states while keeping public runtime delivery, packaging, rollback, and route/UI actions separate.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Approval transition persistence first slice | 1 - 2 days | Selected. It directly follows durable candidate records and turns prior approval state-model planning into a repository-tested storage behavior. |
| Candidate create/list/detail route surface | 1 - 3 days | Deferred. Route/UI controls should use a proven transition helper instead of defining mutation rules inline. |
| Public runtime URL/package exposure | 2 - 5 days | Deferred. Approval state should exist before public delivery semantics are added. |
| Rollback/revision history public selection | 2 - 5 days | Deferred. Requires published revision semantics, not only draft/approved candidate state. |

## Boundary

In scope:

- append-only transition event storage;
- repository helper for `request_review`, `approve`, and `reject`;
- expected current status guard;
- reject reason requirement;
- operator/admin actor guard;
- focused Docker-backed SQLite repository tests.

Out of scope:

- candidate create/list/detail routes;
- approval action buttons;
- public runtime URL;
- artifact copying or packaging;
- rollback;
- push.

## Verification

Planning/report update plus implementation through the selected first slice.
