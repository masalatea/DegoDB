# 2026-0702 Post-Candidate-Event-Display No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Public runtime cache/version policy first slice** as the next product-facing code slice.

Candidate transition event visibility closed the immediate operator auditability gap. The next smallest public-delivery continuation is to make the existing public runtime response semantics explicit: artifact-key URLs represent immutable generated artifacts, while the `current` alias must be revalidated because it can resolve to a newer approved candidate.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Public runtime cache/version policy first slice | 0.5 - 1 day | Selected. Clarifies existing public URL behavior without adding rollback or alias storage. |
| Revision selection / rollback boundary | 1 - 3 days | Deferred. Needs a dedicated published-revision model decision after cache semantics are explicit. |
| Custom public alias key storage | 1 - 3 days | Deferred until current/cache/revision semantics are clear. |
| Broader public delivery polish | Replan | Deferred. Keep this slice narrowly on response semantics. |

## Boundary

In scope:

- artifact-key runtime preview cache policy;
- current alias runtime preview cache policy;
- helper names that document the URL semantics;
- static/contract coverage and plan/report updates.

Out of scope:

- explicit published revision selection;
- rollback;
- custom alias storage;
- package copy/static hosting;
- new public URL shapes;
- push.

## Verification

Implementation selected immediately after this planning step.
