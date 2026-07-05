# Public Runtime Execution Binding Smoke Hardening

Date: 2026-07-04
Status: FIRST_SLICE_DONE

## Summary

The no-code runtime preview browser smoke now checks execution binding expectations explicitly. This protects the split introduced by preview submit wiring:

- Immutable artifact-key preview stays static and has no execution binding.
- No-store current preview exposes a current execution binding.
- No-store alias preview exposes a custom-alias execution binding.

## Implementation Notes

- Added `--execution-binding=ignore|none|required` to `check_no_code_runtime_preview_ui_smoke.js`.
- Added `--execution-url-contains=TEXT` for current/alias endpoint shape checks.
- Added expected project keys to sample07, sample28, and sample29 smoke profiles.
- Updated `check_sample28_no_code_public_runtime_browser_smoke.sh` to assert the artifact/current/alias binding split.

## Verification

- `make sample28-no-code-public-runtime-browser-smoke`: passed.
- The run confirmed artifact preview binding is empty, current preview binding points to `/runs/no-code/SAMPLE28/current/execute.json`, and alias preview binding points to `/runs/no-code/SAMPLE28/alias/stable/execute.json`.

Push was not performed for this slice.
