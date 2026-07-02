# 2026-0702 Post-Cache-Version-Policy No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Current public revision visibility first slice** as the next product-facing code slice.

The cache/version policy clarified that artifact-key URLs are immutable and the `current` alias is mutable. Before adding explicit revision selection, rollback, or custom alias storage, operators should be able to see which approved candidate currently backs the public `current` alias.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Current public revision visibility first slice | 0.5 - 1 day | Selected. Makes current alias resolution visible without adding rollback state or selection storage. |
| Explicit revision selection / rollback implementation | 1 - 3 days | Deferred. Needs visible current revision boundary first. |
| Custom public alias key storage | 1 - 3 days | Deferred until current/revision semantics are clearer. |

## Boundary

In scope:

- show the approved candidate currently backing the `current` public runtime preview;
- show approved non-current candidates as non-current;
- state that explicit rollback selection is not implemented yet;
- static contract coverage and plan/report updates.

Out of scope:

- new published revision table;
- explicit current selection storage;
- rollback action;
- custom alias storage;
- package copy/static hosting;
- push.

## Verification

Implementation selected immediately after this planning step.
