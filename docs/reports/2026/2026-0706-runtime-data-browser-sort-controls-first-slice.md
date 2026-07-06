# Runtime Data Browser Sort Controls First Slice

Date: 2026-07-06

Status: `DONE`

## Summary

#250 replans after the sort query endpoint landed and chooses browser sort controls. #251 implements the first browser slice.

This keeps the product-facing current/alias runtime preview aligned with the read-only `runtime-data.json` sort contract while preserving the existing data boundary.

## Planned / Implemented

- Add field and direction controls to the generated runtime-data control row.
- Request `sort[field]=asc|desc` through the current/alias read-only `runtime-data.json` endpoint.
- Keep normal Refresh as a no-query full-list reload.
- Preserve search, field filter, pagination, and selected-key behavior as separate request paths.
- Extend browser smoke coverage for sample28, sample29, and sample31.

## Boundary

- In scope: current/alias browser controls for the existing one-field sort endpoint.
- In scope: DOM/probe coverage that the request URL and rendered first row match expected sorted data.
- Out of scope: multi-column sort, persisted sort state, combining sort with search/filter/pagination in one browser request, artifact-key preview behavior, and submit/outbox mutation behavior.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (337 tests, 11113 assertions, skipped 1)

The first `make test` run failed because `NoCodeRuntimeTest` intentionally asserts the generated runtime JS hook names/signatures. The implementation was already working in browser smokes; the test expectation was updated to include the new sort hook and controls, then the full suite passed.
