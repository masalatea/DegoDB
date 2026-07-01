# Sample28 No-Code Runtime UI Smoke

Status: `FIRST_SLICE_DONE`

Date: 2026-06-30

## Scope

Added browser/headless coverage for the generated `sample28-no-code-data-app-mvp` runtime preview.

This verifies that sample28 emits a data-first no-code list/detail/form preview and that the generated browser dispatch helper maps the `update_no_code_ticket` operation boundary into a runtime action intent.

## Implementation

- Generalized `mtool/scripts/check_no_code_runtime_preview_ui_smoke.js` with a `--profile` option.
- Kept the default `sample07` profile for existing `sample07-no-code-runtime-ui-smoke` behavior.
- Added a `sample28` profile for `no_code_ticket` screens and `update_no_code_ticket`.
- Added `make sample28-no-code-runtime-ui-smoke`.
- Documented the smoke target in the sample28 README.

## Verification

- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `make sample28-no-code-runtime-ui-smoke`
- `make sample07-no-code-runtime-ui-smoke`

## Next

Continue with sample28 MVP polish, docs, and pack verification.
