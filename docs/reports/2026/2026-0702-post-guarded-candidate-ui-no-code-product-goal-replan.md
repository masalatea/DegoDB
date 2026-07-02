# 2026-0702 Post-Guarded-Candidate-UI No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Approved candidate package exposure first slice** as the next product-facing code slice.

Guarded candidate UI made candidate creation, history, and approval transitions visible on the existing `NO-CODE-RUNTIME` Source Output detail page. The next smallest continuation is to expose the already-created artifact package links only for approved candidates, while keeping public runtime URL, public alias routes, rollback, and dedicated candidate routes out of scope.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Approved candidate package exposure first slice | 0.5 - 1 day | Selected. It turns approved candidate state into an operator/admin handoff affordance without creating public delivery semantics. |
| Public runtime URL route | 2 - 5 days | Deferred. Needs stable public alias/security semantics and should not be coupled to the first package exposure. |
| Dedicated candidate list/detail routes | 1 - 3 days | Deferred. The existing Source Output detail page is still enough for the first candidate workflow. |
| Rollback/revision history public selection | 2 - 5 days | Deferred. Requires published revision semantics beyond approved candidate state. |

## Boundary

In scope:

- existing `NO-CODE-RUNTIME` Source Output detail page only;
- approved candidate package affordance;
- links to existing artifact detail/download routes;
- clear non-approved guard text;
- static contract coverage.

Out of scope:

- public runtime URL;
- public alias key route;
- package copying or new storage table;
- rollback;
- dedicated candidate route set;
- push.

## Verification

Planning/report update plus a narrow UI/contract implementation.
