# Post-Enabled Submit Payload Smoke No-Code Product Goal Replan

Date: 2026-07-04
Status: DONE

## Summary

The browser-side enabled submit payload smoke now proves that generated current and alias previews can build the correct POST shape without mutating the server. The remaining gap before real generated preview mutation is not payload shape; it is the product/security boundary where generated actions must be enabled only under an authenticated principal and policy decision.

Before enabling real mutation from the preview, the safer next step is an authenticated direct endpoint smoke that proves the HTTP route accepts valid request binding and still fails closed when the generated artifact action is disabled.

## Decision

Choose `Runtime execution endpoint disabled-policy smoke` as the next work unit.

## Scope

- Login through the existing stub admin auth path.
- Read current/alias execution binding from rendered previews.
- POST valid CSRF, project key, artifact key, action key, and input payload to each `execute.json`.
- Verify the request contract passes but the generated disabled action returns 422 JSON without execution.

Push remains out of scope.
