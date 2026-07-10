# Post-Runtime Execution Boundary No-Code Product Goal Replan

Date: 2026-07-03
Status: DONE

## Summary

The server-backed runtime execution boundary inventory confirmed that backend dispatch and generated DBAccess execution capability already exist. The generated runtime preview still needs a user-facing mutation boundary before it can submit real changes.

The next safe step is not a full endpoint yet. The chosen first implementation slice is a request contract helper that normalizes a runtime execution POST into action key, input payload, and runtime artifact binding only after fail-closed request checks pass.

## Decision

Choose `Runtime execution request contract first slice` as the next main-plan work unit.

This keeps the mutation path staged:

- Browser-local action intent preview remains unchanged.
- Server dispatch remains behind existing `app_no_code_runtime_dispatch_action()` checks.
- The new work fixes the request boundary that an endpoint can reuse later.
- Public endpoint routing, result refresh, audit trail, and durable execution UI remain separate follow-up slices.

## Deferred Candidates

- Public runtime execution endpoint.
- Browser form submission wiring from generated runtime preview.
- Conservative success/error result surface in the generated preview.
- Runtime execution audit trail beside publish candidate / alias lifecycle events.
- Result refresh after successful mutation.
