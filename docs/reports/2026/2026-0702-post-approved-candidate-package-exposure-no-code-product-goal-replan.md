# 2026-0702 Post-Approved-Candidate-Package-Exposure No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Public runtime preview artifact-key route first slice** as the next product-facing code slice.

Approved candidate package exposure made the existing admin artifact detail/download handoff visible only after approval. The smallest public delivery continuation is to expose the approved generated `runtime-preview.html` through an artifact-key route, while keeping stable public aliases, cache policy, rollback selection, package copying, and dedicated candidate route sets out of scope.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Public runtime preview artifact-key route first slice | 0.5 - 1 day | Selected. It opens the first public runtime surface but gates it on an approved candidate and an existing artifact key. |
| Public alias route planning | 0.5 - 1 day | Deferred. Alias stability and revision selection should be planned after the artifact-key URL proves the serving boundary. |
| Candidate event display polish | 0.5 - 1 day | Deferred. Useful, but less product-facing than giving approved runtime output a URL. |
| Rollback/revision public selection | 2 - 5 days | Deferred. Requires stable published revision semantics beyond first public serving. |

## Boundary

In scope:

- public GET route for approved `NO-CODE-RUNTIME` `runtime-preview.html`;
- approved candidate lookup by project/artifact key;
- existing artifact bundle/manifest storage only;
- static route/auth contract coverage and repository coverage.

Out of scope:

- public alias key route;
- custom cache/version policy;
- package copying or new storage table;
- rollback/revision selection;
- public serving of arbitrary bundle files;
- push.

## Verification

Implementation selected immediately after this planning step.
