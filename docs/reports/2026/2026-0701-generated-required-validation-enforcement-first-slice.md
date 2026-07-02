# 2026-0701 Generated Required Validation Enforcement First Slice

Status: `FIRST_SLICE_DONE`.

## Summary

Added deterministic required-value enforcement to the generated no-code runtime action-intent path.

Before this slice, the PHP and browser runtime intent builders failed closed when a required field was missing, but a required field present as a blank string could still be accepted. This slice treats `null` and blank strings as missing for required fields before an action intent is considered valid.

## Scope

In scope:

- PHP runtime action-intent builder required-value check.
- Generated browser preview dispatch helper required-value check.
- Focused PHPUnit coverage for blank required input.
- sample28 browser smoke coverage for blank required dispatch.

Out of scope:

- Full validation DSL.
- Cross-field validation.
- Server-side validation behavior.
- Visual builder.
- Publishing workflow.
- Remote sync transport.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `make sample28-no-code-runtime-ui-smoke`
- `make test`
  - `311 tests, 10354 assertions, skipped 1`

## Notes

This is intentionally small. It does not add a new validation schema or server behavior; it only makes the existing generated required metadata enforceable in the local action-intent preparation path.
