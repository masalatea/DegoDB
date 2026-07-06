# Runtime Data Browser Search Controls First Slice

Date: 2026-07-06

Status: `DONE`

## Summary

#238 replans after the runtime-data search query endpoint and chooses browser search controls now that the read-only endpoint contract is fixed. #239 implements the first slice.

This keeps search an explicit user action. Normal Refresh remains the no-query full-list reload, while Search asks the current/alias `runtime-data.json` endpoint for `q`.

## Implemented

- Added Search input/buttons to current/alias runtime-data controls.
- Search requests the bounded `q` endpoint parameter.
- Search with page-size available starts at page 1 and preserves that page-size.
- Normal Refresh still fetches no-query live runtime data.
- Browser smoke coverage verifies Search controls and searched row rendering across sample28, sample29, and sample31.

## Boundary

- In scope: current/alias generated runtime search controls for live read-only runtime data.
- In scope: preserving no-query Refresh, pagination controls, selected-key row selection, and immutable artifact-key previews.
- Out of scope: field-specific filters, sort controls, advanced operators, persisted search state, and submit/outbox mutation behavior.

## Verification

Passed before commit:

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11105 assertions`, `1 skipped`)

Note: Docker-backed checks were run with normal Docker permissions because buildx writes activity metadata under `~/.docker`.
