# No-Code Runtime UI Smoke

Status: `FIRST_SLICE_DONE`

Date: 2026-06-30

## Scope

Added the first basic UI smoke for generated no-code runtime HTML preview output.

This verifies the current Web / HTML runtime preview surface. It does not add native app targets, a visual builder, or create/update browser interaction yet.

## Implementation

- Added `mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`.
- Added `make sample07-no-code-runtime-ui-smoke`.
- The target runs sample07 pack generation, then opens generated `runtime-preview.html` with headless Chromium.
- The smoke verifies:
  - `no-code-runtime-v0` preview root
  - generated list screen
  - generated detail screen
  - generated form screen
  - table / detail / form DOM structures
  - screenshot capture under `output/playwright/no-code-runtime-preview/`

## Verification

- `node mtool/scripts/check_no_code_runtime_preview_ui_smoke.js --html=work/source-outputs/SAMPLE07/NO-CODE-RUNTIME/runtime-preview.html`
- `make sample07-no-code-runtime-ui-smoke`

## Next

The next no-code slice is create/update browser or headless smoke, proving an action dispatch goes through the generated operation boundary rather than hand-coded screen logic.
