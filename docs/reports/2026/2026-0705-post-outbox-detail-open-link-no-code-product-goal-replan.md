# Post Outbox Detail Open Link No-Code Product Goal Replan

Status: `DONE`

Push: not performed.

## Decision

After adding the submit-success `Open outbox detail` link, the next implementation slice is a manual runtime result refresh affordance.

Chosen next work:

- Runtime result refresh button first slice.

## Why This Next

The runtime now tells the user to process the sync outbox item and refresh the runtime preview, and it also exposes copy/open affordances for the operator outbox detail path.

A disabled-by-default `Refresh preview` button that becomes available only after successful submit is the smallest next user-facing step. It keeps the flow manual and outbox-based while making the instructed next action visible in the UI.

Live polling, synchronous endpoint processing, retry mutation, and another sample remain out of scope for this slice.

## Deferred Candidates

- Live result refresh / polling after submit.
- Synchronous endpoint processing for local/demo workflows.
- Runtime retry mutation for failed outbox items.
- Another no-code sample using the same submit/outbox/operator handoff.
- Commit cleanup / push, only with explicit user direction.

## Estimate

0.25 day for replan, then 0.25 - 0.5 day for the refresh control, form-state preservation, smoke assertion, focused test update, and verification.
