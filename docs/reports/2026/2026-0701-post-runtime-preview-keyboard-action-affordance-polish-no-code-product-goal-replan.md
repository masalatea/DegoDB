# Post-runtime preview keyboard/action affordance polish no-code product goal replan

Status: `DONE`

Date: 2026-07-01

## Decision

Choose Retry audit trail as the next small no-code product-facing implementation.

## Context

Runtime preview action controls now expose keyboard/action affordance markers and disabled-action reasons. Operator retry visibility also already exists across the sync outbox detail page, retry action, retry feedback, processor smoke, and generated runtime retry hint. The remaining small product gap is accountability: when an operator requeues a failed sync outbox item, the mutation should leave a focused audit event that records who did it and what changed.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Retry audit trail | 0.5 - 2 days | Selected. Runtime/operator action visibility is now strong enough that accountability is the next concrete gap. |
| Runtime preview action affordance follow-up | 0.5 - 2 days | Deferred. The first affordance marker and disabled reason are enough until a concrete payload-guidance gap appears. |
| Operator/admin no-code workflow polish | 1 - 3 days | Deferred. Useful later, but the retry mutation already has the narrower accountability gap. |

## Scope

In scope:

- audit event shape for operator retry requeue;
- sync outbox detail page audit append after successful requeue;
- operator notice showing audit trail result;
- focused contract tests.

Out of scope:

- new audit tables;
- retry scheduler;
- inline processing;
- transport;
- conflict resolution;
- broad operator workflow redesign.

## Notes

This keeps retry accountability on the existing `audit_events` foundation and does not change the sync outbox processing semantics.
