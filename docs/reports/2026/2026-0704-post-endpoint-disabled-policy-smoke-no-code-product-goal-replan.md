# Post-Endpoint Disabled-Policy Smoke No-Code Product Goal Replan

Date: 2026-07-04
Status: DONE

## Summary

The authenticated direct endpoint smoke now proves that current and alias `execute.json` routes receive valid CSRF/project/artifact/action/input requests and still fail closed when the stored generated runtime artifact marks the action disabled.

The next gap is not routing or request shape. It is the principal-aware policy boundary: execution needs a way to keep the approved stored artifact stable while re-evaluating action availability for the authenticated principal before dispatch.

## Decision

Choose `Runtime principal action policy overlay contract` as the next work unit.

## Scope

- Add a pure runtime helper before wiring route behavior.
- Preserve stored runtime definition structure, fields, data, and artifact identity.
- Overlay only action availability and policy from a separately evaluated principal-aware definition.
- Prove rendered actions and dispatch use the overlaid policy.

Push remains out of scope.
