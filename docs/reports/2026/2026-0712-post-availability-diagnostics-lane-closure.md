# Post Availability Diagnostics Lane Closure

## Decision

The read-only availability diagnostics lane is complete. Generated UI enablement remains parked. The next safe lane is an opt-in real guarded execution smoke against the existing authenticated execution boundary, with Transaction Full success and rollback evidence.

## Evidence now complete

- server readiness-to-policy builder is default-off and fail-closed;
- authenticated artifact/current/alias availability responses are selector-bound and zero-dispatch;
- preview consumption validates contract, project, and immutable artifact identity;
- browser coverage proves enabled, denied, stale, and unavailable diagnostics;
- availability loading issues GET only, leaves controls unchanged, and issues no POST;
- generated DBAccess Transaction Full behavior already has success and rollback coverage.

## Why UI enablement remains parked

The availability response is a point-in-time presentation result. Execution must still re-evaluate authorization, CSRF, artifact binding, action input, runtime configuration, idempotency, and recovery behavior at click time. Treating the GET response as execution authority would bypass that distinction.

## Next promoted boundary

Plan a narrow real guarded execution browser smoke with all of the following explicit inputs:

- authenticated current or alias preview;
- explicit execution and mutation flags;
- `MTOOL_NO_CODE_TRANSACTION_FULL_GATE=transaction_full_v1`;
- one qualifying Sample18 operation;
- before/after database assertions;
- forced second-step failure proving rollback;
- success proving all required writes commit;
- audit/outbox/recovery assertions appropriate to the existing route;
- cleanup that restores sample state.

This smoke may exercise an already-authorized explicit route. It does not change generated defaults or let the diagnostics GET authorize execution.

