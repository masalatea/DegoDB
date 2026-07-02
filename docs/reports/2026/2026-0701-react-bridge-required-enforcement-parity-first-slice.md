# 2026-0701 React Bridge Required Enforcement Parity First Slice

Status: `FIRST_SLICE_DONE`.

## Summary

The generated React bridge now applies the same blank required-value fail-close behavior as the generated runtime action-intent path.

The bridge exports `createActionIntentResult()` for non-throwing validation, keeps the existing `createActionIntent()` wrapper for callers that want exceptions, and only emits `mtool-no-code-react-bridge-intent` when the result is valid.

## Scope

In scope:

- Generated React bridge local action-intent helper.
- Required blank string handling for form input fields.
- App/global helper feedback state when action intent creation is blocked.
- sample28 React bridge browser smoke coverage.

Out of scope:

- Full validation DSL.
- Cross-field validation.
- Server-side validation behavior.
- Generated runtime validation feedback wording.
- Publishing workflow.

## Implementation Notes

- Added `MtoolNoCodeActionIntentResult` and `createActionIntentResult()` to the generated React bridge helper.
- Required checks apply to action fields with `role === 'input'`; key fields remain outside this local form-state guard because the bridge local intent helper only owns editable form input state.
- Blank strings are normalized through `runtimeInputValue()` before checking for required emptiness.
- The React app feedback block now exposes an `error` state when validation blocks intent emission.
- The browser smoke fills all required sample28 form inputs for the success path, then probes a blank required `body` value and expects `input.missing:body`.

## Verification

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `php -l tests/Integration/SharedDataClassContractFoundationTest.php`
- `node --check mtool/scripts/check_no_code_react_bridge_browser_smoke.js`
- `make sample28-no-code-react-bridge-browser-smoke`
- `make test` (`311 tests, 10355 assertions, skipped 1`)
