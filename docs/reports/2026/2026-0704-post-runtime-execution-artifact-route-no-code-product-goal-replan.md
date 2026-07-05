# Post-Runtime Execution Artifact Route No-Code Product Goal Replan

Date: 2026-07-04
Status: DONE

## Summary

The artifact-key scoped no-code runtime execution route is in place. It gives approved runtime artifacts an authenticated JSON mutation endpoint while leaving generated preview HTML public and keeping browser submission wiring out of scope.

Before wiring generated forms to submit, the next useful slice is to align execution route variants with the already-public preview URL shapes: `current` and custom `alias` URLs. This keeps execution addressability consistent with the public runtime delivery model without changing the generated HTML yet.

## Decision

Choose `Runtime execution current/alias routes first slice` as the next work unit.

## Scope

- Add authenticated `/runs/no-code/{project}/current/execute.json`.
- Add authenticated `/runs/no-code/{project}/alias/{alias}/execute.json`.
- Resolve the same approved candidates as the existing current and alias preview routes.
- Reuse the existing runtime execution request/dispatch/endpoint response helper.
- Keep generated preview submission wiring deferred.

Push remains out of scope.
