# Sample18 Post Real DBAccess Invocation Adapter Lane Closure

Date: 2026-07-10
Plan: #650
Status: DONE

## Summary

#650 closes the route-unwired real-compatible DBAccess invocation adapter lane.

#649 is accepted as the current invocation boundary before generated-submit route execution is enabled.

## Accepted Capability From #649

- The adapter can construct a `TaskCardData`-compatible object from dispatcher `TaskCardObj` metadata.
- The adapter invokes only allowlisted generated-submit methods on a supplied DBAccess-compatible object.
- The adapter requires explicit `in_transaction=true` before invocation.
- Real DBAccess-like object, array, boolean, and error-like results normalize into the existing adapter shape.
- Missing transaction, missing method, DB error, and exception paths fail closed without route execution.

## Decision

Promote real transaction binding preflight next.

Reason:

- The DBAccess method call boundary now exists, but the transaction adapter still uses fake begin / commit / rollback callables.
- Route feature-flag integration would be premature until the app DB transaction API and DBAccess instance creation are pinned down.
- The all-success-or-failure policy requires clear begin / DBAccess / rollback / commit behavior before any route can expose execution.

## Next

#651 should define the real transaction binding boundary.

Required boundaries:

- no generated-submit route wiring yet;
- no default-on executor behavior;
- transaction begin / commit / rollback must target only the sample18 application DB handle;
- DBAccess instance creation must happen inside or be explicitly bound to the active app DB transaction context;
- DBAccess failure or exception must trigger rollback;
- rollback failure and commit failure must return user-facing failure with stable recovery metadata;
- focused tests should use fake transaction objects first, with DB-backed coverage promoted only after the binding shape is stable.

## Verification

- `git diff --check`
