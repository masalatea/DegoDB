# Sample19 Generated Handoff Preview Route Lane Closure

Date: 2026-07-12

## Closed lane

The default-off authenticated generated handoff preview route is complete.

Accepted capability:

- `GET /projects/SAMPLE19/material-insight/no-code-handoff` exists as an inspection-only route.
- It is guarded by `MTOOL_SAMPLE19_MATERIAL_INSIGHT_NO_CODE_HANDOFF_PREVIEW_ENABLED`.
- It requires authentication.
- It renders stable markers for `no-code-screen-definition-v0`, `no-code-runtime-v0`, generated handoff screens, zero generated actions, zero custom operations, no AI call, and no mutation.
- It reuses the validated material insight loader and the test-proven no-code handoff adapter.

Verification from the implementation slice:

- targeted route test: 4 tests / 31 assertions
- docs contract recheck: 12 tests / 590 assertions
- full suite: 482 tests / 14,364 assertions / 1 skipped

## Decision

Promote headless browser evidence next.

Reason:

- The route is new.
- Fast PHP tests prove route registration and rendered markers.
- Real-stack browser evidence should confirm default-off, auth redirect, flag-on render markers, zero POST/action controls, and rollback-by-flag.
- Browser checks must run headless by default to avoid intrusive macOS Chrome popups.

## Scope for #826

Verify:

- flag off: route is not exposed and returns 404 after auth
- unauthenticated flag-on request redirects to login
- authenticated flag-on route renders generated handoff markers
- no form/button/script/generated execution controls are present
- no POST requests are made by the page
- flag rollback returns to off behavior

Do not add:

- AI/Ollama calls
- DB/config writes
- import/apply/build/publish
- mutation
- generated submit controls
- generated execution
