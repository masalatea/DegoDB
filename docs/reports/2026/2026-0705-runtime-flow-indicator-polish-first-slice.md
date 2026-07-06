# Runtime Flow Indicator Polish First Slice / runtime flow indicator polish first slice

Status: `FIRST_SLICE_DONE`

Date: 2026-07-05

Push: not performed.

## Scope

This slice implements the first practical runtime flow polish for the post-submit no-code experience. It keeps the existing async outbox foundation and adds a visible flow indicator to make the current process easier to understand from the runtime surface.

## Accepted Capability

Generated no-code runtime panels now show a `Runtime flow` group with three steps:

1. Submit
2. Outbox tracking
3. Refresh

The flow starts in waiting / blocked / ready states depending on execution binding and draft readiness. During submit it moves to working. After a successful real submit it moves to accepted, with submit done, outbox tracking ready, and refresh ready.

## Boundary

- Async outbox remains the foundation.
- Runtime submit still enqueues work and exposes outbox handoff affordances.
- No synchronous processing is added in this slice.
- No conflict resolution, transport, or worker orchestration is added.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`

## Next Step

The next active mainline item is #180 synchronous demo processing first slice. It should add a safe demo/tryout path for immediate processing while preserving the async outbox model as the production foundation.
