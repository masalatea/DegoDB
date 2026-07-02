# 2026-0701 Validation Feedback Polish First Slice

Status: `FIRST_SLICE_DONE`.

## Summary

Generated runtime and React bridge validation failures now keep raw machine-readable error codes while also exposing a display-ready message.

For required input failures, `input.missing:body` remains available as `error`, and the user-facing `message` becomes `Required input is missing: body`.

## Scope

In scope:

- Runtime preview browser dispatch result `message`.
- PHP runtime dispatch result `message`.
- React bridge `MtoolNoCodeActionIntentResult.message`.
- React bridge App feedback uses `result.message`.
- sample28 runtime and React bridge smoke assertions.

Out of scope:

- Full validation DSL.
- Cross-field validation.
- Persistence/server validation semantics.
- Schema-form/rjsf runtime behavior.
- Localization.

## Implementation Notes

- Added validation message conversion for `input.missing:*` and `input.readonly:*`.
- Kept `error` unchanged for machine-readable checks and backward-compatible tests.
- Added `message` to successful dispatch/action results as an empty string.
- Updated generated React bridge source contract coverage to assert `validationMessage` and `setActionError(result.message)`.
- Updated browser smokes to assert the display-ready message for blank required input.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `php -l tests/Integration/SharedDataClassContractFoundationTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `node --check mtool/scripts/check_no_code_react_bridge_browser_smoke.js`
- `make sample28-no-code-runtime-ui-smoke`
- `make sample28-no-code-react-bridge-browser-smoke`
- `make test` (`311 tests, 10359 assertions, skipped 1`)
