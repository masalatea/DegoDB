# 2026-0702 Post-Current-Public-Revision-Visibility No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Explicit current public revision selection first slice** as the next product-facing code slice.

Current public revision visibility made the implicit `current` alias target visible. The next smallest public-delivery continuation is to let an operator/admin explicitly select which approved candidate backs `current`, providing the first rollback path by selecting an older approved candidate.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Explicit current public revision selection first slice | 1 - 2 days | Selected. Adds a narrow current selection table and operator action without custom alias storage. |
| Custom public alias key storage | 1 - 3 days | Deferred until current revision selection is durable. |
| Broader rollback workflow | 2 - 5 days | Deferred. The first slice treats rollback as reselecting an older approved candidate. |

## Boundary

In scope:

- current public runtime revision selection storage;
- operator/admin action to set an approved candidate as current;
- current route lookup honoring explicit selection before latest-approved fallback;
- focused repository/static UI contract coverage.

Out of scope:

- custom alias key storage;
- separate rollback event stream;
- package copy/static hosting;
- new public URL shapes;
- push.

## Verification

Implementation selected immediately after this planning step.
