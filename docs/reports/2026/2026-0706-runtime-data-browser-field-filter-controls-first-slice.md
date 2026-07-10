# Runtime Data Browser Field Filter Controls First Slice

Date: 2026-07-06

Status: `DONE`

## Summary

#244 replans after the endpoint field-filter slice and chooses browser field filter controls because the read-only current/alias `runtime-data.json` contract now accepts bounded `filter[field]=value` queries. #245 implements the first browser control slice.

This keeps field-specific filtering user-facing without changing the no-query Refresh behavior or the separate submit/outbox mutation path.

## Planned / Implemented

- Add generated runtime filter controls to current/alias live runtime-data list screens.
- Build the filter field selector from generated screen fields.
- Request `runtime-data.json?filter[field]=value` through the existing read-only runtime-data endpoint.
- Start filtered requests at page 1 when a page-size control is available.
- Keep normal Refresh as a no-query full-list runtime-data reload.
- Preserve existing Search, pagination, direct page input, selected-key row selection, immutable artifact-key previews, and submit/outbox mutation boundaries.
- Extend browser smoke coverage for sample28, sample29, and sample31.

## Boundary

- In scope: current/alias browser controls for first-slice field-specific filtering.
- In scope: proving the browser request shape matches the endpoint contract.
- Out of scope: advanced filter operators, multiple visible filter rows, persisted filter state, sort controls, artifact-key preview behavior, and mutation semantics.

## Verification

Passed before commit:

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11109 assertions`, `1 skipped`)

Note: The first full `make test` run exposed one stale contract assertion for the expanded `refreshRuntimeDataForScreen(...)` signature. The assertion was updated and the full suite passed on rerun.
