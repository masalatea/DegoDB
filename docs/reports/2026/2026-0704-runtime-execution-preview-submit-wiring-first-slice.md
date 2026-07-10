# Runtime Execution Preview Submit Wiring First Slice

Date: 2026-07-04
Status: FIRST_SLICE_DONE

## Summary

Generated no-code runtime previews now include a `Submit to server` control in the `Action Intent Draft` toolbar. Static artifact previews keep the control unavailable. Current and custom-alias preview responses inject a server-generated execution binding before the runtime script runs, allowing the browser to discover the authenticated execution URL and CSRF token without making immutable artifact HTML session-specific.

This slice wires the browser-side POST path but still fails closed when the local draft is blocked by disabled action policy, missing key/input fields, or policy checks.

## Implementation Notes

- Added `data-runtime-execute` and `data-runtime-execute-status` controls to generated preview HTML.
- Added browser-side execution binding parsing, submit availability state, and `fetch()` POST wiring.
- Added response-time execution binding injection for current and alias preview responses.
- Preserved artifact-key preview cache behavior by not injecting session-specific binding into immutable artifact preview responses.
- Kept blocked drafts disabled with `Resolve draft blockers before server submission.`

## Verification

- `php -l mtool/app/no_code_runtime.php`: passed.
- `php -l mtool/app/no_code_public_runtime_page.php`: passed.
- Focused `NoCodeRuntimeTest` and `OpenApiSourceOutputContractTest`: `12 tests, 205 assertions`.
- `make sample28-no-code-runtime-ui-smoke`: passed.
- Direct current preview browser smoke: passed with `executionBindingUrl: /runs/no-code/SAMPLE28/current/execute.json` and blocked submit state.
- Direct alias preview browser smoke: passed with `executionBindingUrl: /runs/no-code/SAMPLE28/alias/stable/execute.json` and blocked submit state.

The focused PHPUnit run emitted a `.phpunit.result.cache` write warning because the container test directory is read-only; the test result itself passed.

Push was not performed for this slice.
