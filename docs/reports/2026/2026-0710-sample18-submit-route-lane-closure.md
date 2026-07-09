# Sample18 Submit Route Lane Closure

Plan item: #568 sample18 submit route lane closure

Status: DONE

## Summary

Closed the sample18 blocked submit-route preflight lane and promoted HTTP smoke coverage as the next safest increment before route binding or mutation dispatch.

## Accepted capability

- #565 defines generated submit request normalization, ignored client fields, validation errors, and unknown-operation failure for create/update/complete.
- #566 adds `/samples/sample18-task-board/no-code/generated-submit` as an authenticated blocked JSON wrapper that validates payloads but still returns `generated_submit_disabled` before mutation.
- #567 exposes the blocked submit route marker from generated/runtime UI while keeping generated action buttons and runtime execute disabled.

## Decision

#569 should prove the generated submit endpoint through the authenticated HTTP stack. The smoke should cover the blocked valid request, validation failure, unknown operation, and method guard JSON behavior before any runtime click binding is added.

Route binding and mutation dispatch remain parked. The generated buttons stay disabled, no DBAccess mutation is called, no outbox work is enqueued, and the curated sample18 route remains the user-facing mutation owner.

## Verification

- `git diff --check`

## Next

#569 should add the smallest authenticated HTTP smoke for the blocked sample18 generated submit route before promoting runtime binding or mutation dispatch.
