# Post-Execution Binding Smoke Hardening No-Code Product Goal Replan

Date: 2026-07-04
Status: DONE

## Summary

Public runtime execution binding smoke hardening now proves the delivery split:

- Immutable artifact preview has no execution binding.
- Current preview exposes `/runs/no-code/{project}/current/execute.json`.
- Alias preview exposes `/runs/no-code/{project}/alias/{alias}/execute.json`.

The next useful step is to verify the browser-side submit payload path before enabling a real server mutation scenario in the generated preview.

## Decision

Choose `Runtime preview enabled submit payload smoke` as the next work unit.

## Scope

- Keep artifact-key preview static and non-executable.
- In current/alias preview smoke only, force one generated action into an enabled browser state.
- Stub `window.fetch` so no server mutation occurs.
- Click `Submit to server` and verify endpoint URL, credentials, CSRF, action binding, key field, and input payload shape.

Push remains out of scope.
