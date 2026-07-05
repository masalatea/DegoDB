# Post-Runtime Execution Current/Alias Routes No-Code Product Goal Replan

Date: 2026-07-04
Status: DONE

## Summary

Authenticated artifact-key, current, and custom-alias no-code runtime execution routes are now addressable. The remaining gap before a tryout user can see the real execution path from the generated preview is browser-side submission wiring.

The next slice should start from the generated preview while preserving the existing delivery split:

- Public preview HTML remains readable without authentication.
- Mutation JSON endpoints remain authenticated.
- CSRF and artifact binding stay server-generated.
- Immutable artifact preview caching must not receive session-specific data.

## Decision

Choose `Runtime execution preview submit wiring first slice` as the next work unit.

## Scope

- Add a generated `Submit to server` control beside the local draft copy control.
- Keep the control disabled when no execution binding is available.
- Inject execution binding only for no-store current and alias preview responses.
- Keep artifact-key preview HTML static and immutable.
- Block submission while the local draft has action, key, input, or policy blockers.

Push remains out of scope.
